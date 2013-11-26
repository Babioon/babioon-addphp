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
* {rdaddphp file=realtive_path_to_file_in_your_htdocs_include_file_name}
*
* Example:
* Joomla installed in /var/www/joomla
* PHP-Files in /var/www/joomla/myphpfiles/
* Filename ist my_file.php
* {rdaddphp file=myphpfiles/my_file.php}
*
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );


class plgContentBabioonaddphp extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
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
	 */
	private function simpleCheck($text)
	{
		// simple performance check to determine whether bot should process further
		return !(strpos($text, 'babioonaddphp') === false);
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
		// check if we should proccess on this event
		if ($this->params->get('event', 0) == 1)
		{
			return;
		}
		// check if there is something to replace
		if (!$this->simpleCheck($article->text))
		{
			return;
		}
		$article->text=$this->parseAndReplace($article->text, $params);
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
		// check if we should proccess on this event
		if ($this->params->get('event', 0) == 0)
		{
			return;
		}

		// check if there is something to replace
		if (!$this->simpleCheck($article->introtext))
		{
			return;
		}

		// check if there is an article restriction
		// rarticle should be a comma seperated list of article id's like 1,34,87,6543
		$rarticle = $this->params->get('rarticle', '');
		$rlist = split(',', $rarticle);
		for ($i=0,$rc=count($rlist);$i<$rc;$i++)
		{
			$rlist[$i] = (int) $rlist[$i];
		}
		/*
		 * if rule == deny then processing is denied for the listed articles
		 * if rule == allow then processing is allowed for the listed articles
		 */
		$rule = $this->params->get('baserule', 'deny');
		if($rule == 'deny')
		{
			if (in_array($article->id, $rlist)) return;
		}
		else
		{
			if (!in_array($article->id, $rlist)) return;
		}

		$article->introtext=$this->parseAndReplace($article->introtext, $params);
	}

	/**
	 * Parse the text and replace it with the output from the php-file
	 *
	 * @param string 	The article text
	 * @param object	The article params
	 */
	private function parseAndReplace($text, $params)
	{
		// expression to search for
		$regex = '/{(babioonaddphp)\s*(.*?)}/i';

		// find all instances of plugin and put in $matches
		$matches = array();
		preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
		foreach ($matches as $elm)
		{
			parse_str( $elm[2], $args );
			$phpfile=@$args['file'];
			$output = "";
			if ( $phpfile ) {
				$phpfile = JPATH_ROOT . '/' . $phpfile;
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
			$text = preg_replace($regex, $output, $text, 1);
		}
		return $text;
	}

}
/* EOF */