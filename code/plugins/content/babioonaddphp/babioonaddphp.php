<?php
/**
 * babioon addphp
 * @package    BABIOON_ADDPHP
 * @author     Robert Deutz <rdeutz@gmail.com>
 * @copyright  2013 Robert Deutz Business Solution
 * @license    GNU General Public License version 2 or later
 **/

/**
* Add PHP Plugin
*
* Usage:
* {babioonaddphp file=realtive_path_to_file_in_your_htdocs_include_file_name}
*
* Example:
* Joomla installed in /var/www/joomla
* PHP-Files in /var/www/joomla/myphpfiles/
* Filename ist my_file.php
* {babioonaddphp file=myphpfiles/my_file.php}
*
* you can configure a folder in the plugin setting, if you do so this folder
* goes between htdocs and your file
*
* Example:
* Joomla installed in /var/www/joomla
* PHP-Files in /var/www/joomla/myphpfiles/
* Filename ist my_file.php
* setting are folder == myphpfiles
* {babioonaddphp file=my_file.php}
*
*
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * plgContentBabioonaddphp class
 *
 * @package BABIOON_ADDPHP
 * @since   2.0.0
 */
class plgContentBabioonaddphp extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Check if there is something we can work on
	 *
	 * @param string $text
	 *
	 * @return  boolean  is there something to replace
	 */
	private function isTagInText($text)
	{
		// simple performance check to determine whether bot should process further
		$searchtag = $this->params->get('searchtag','babioonaddphp');
		return !(strpos($text, $searchtag) === false);
	}

	/**
	 * Plugin that loads a phpfile within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// check if there is something to replace
		if ($this->isTagInText($article->text))
		{
			$remove = false;
			// check if we should process on this event and context
			$currentContext = current(explode('.', $context));
			if ($currentContext == 'mod_custom' && $this->params->get('runonmodules', 1) == 0)
			{
				$remove = true;
			}

			if ($currentContext == 'com_content')
			{
				if ($this->params->get('runonarticles', 1) == 2)
				{
					return;
				}

				if (!$this->isArticleOnWhiteList($article) || $this->params->get('runonarticles', 1) == 0)
				{
					$remove = true;
				}
			}

			$article->text=$this->parseAndReplace($article->text, $remove);
		}
	}

	/**
	 * Plugin that loads a phpfile within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
	{
		// check if there is something to replace
		if (!$this->isTagInText($article->text))
		{
			$remove = false;
			// check if we should process on this event and context
			$currentContext = current(explode('.', $context));

			if ($currentContext == 'com_content')
			{
				if ($this->params->get('runonarticles', 1) == 1)
				{
					return;
				}

				if ($this->isArticleOnWhiteList($article) || $this->params->get('runonarticles', 1) == 0)
				{
					$remove = true;
				}
			}

			$article->introtext=$this->parseAndReplace($article->introtext, $remove);
		}
	}

	/**
	 * Parse the text and replace it with the output from the php-file
	 *
	 * @param  string   The article text
	 * @param  boolean  remove tag or replace tag
	 *
	 * @return string   The article text
	 */
	private function parseAndReplace($text, $remove=false)
	{
		// expression to search for
		$searchtag = $this->params->get('searchtag','babioonaddphp');
		$regex     = '/{('.$searchtag.')\s*(.*?)}/i';

		// find all instances of plugin and put in $matches
		$matches = array();
		preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
		foreach ($matches as $elm)
		{
			$output = "";

			if ($remove === false)
			{
				parse_str($elm[2], $args);
				$phpfile  = @$args['file'];
				$myfolder = $this->params->get('myfolder','');

				$basedir = JPATH_ROOT . '/';
				if ($myfolder != '')
				{
					$basedir = $basedir . $myfolder . '/';
				}

				if ($phpfile != '')
				{
					$phpfile = $basedir . $phpfile;
					if (file_exists($phpfile))
					{
						ob_start();
						include($phpfile);
						$output .= ob_get_contents();
						ob_end_clean();
					}
					else
					{
						$output = "File: $phpfile don't exists";
					}
				}
			}
			$text = preg_replace($regex, $output, $text, 1);
		}
		return $text;
	}

	/**
	 * check if there is an article restriction
	 *
	 * @param  object  $article   the article
	 *
	 * @return boolean
	 */
	private function isArticleOnWhiteList($article)
	{
		$rlist = array();
		// rarticle should be a comma seperated list of article id's like 1,34,87,6543
		$listarticle = trim($this->params->get('listarticle', ''));
		if ($listarticle != '')
		{
			$rlist = explode(',', $listarticle);
			JArrayHelper::toInteger($rlist);
		}
		/*
		 * if rule == deny then processing is denied for the listed articles
		 * if rule == allow then processing is allowed for the listed articles
		 */
		$run = true;
		$rule = $this->params->get('baserulea', 'deny');
		if ($rule == 'deny')
		{
			if (in_array($article->id, $rlist))
			{
				$run = false;
			}
		}
		else
		{
			if (!in_array($article->id, $rlist))
			{
				$run = false;
			}
		}

		return $run;
	}
}
/* EOF */