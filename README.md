# This plugin allows you to include PHP in your content items.

**Keep in mind**: allowing to include PHP-Code can be a security problem when you not do it in restricted way. Use for the scripts a directory nobody can upload files and if you have a bunch of authors restrict execution to some articles. 

This plugin allows to add PHP-Code to articles or modules. The PHP-script-files should be located under the Joomla! root-directory and you should disable the "Print" and "Email to a friend" links.

Execution can be restricted to articles and/or modules. For articles you can also configure for which articles the script should be executed. Further more you can set the base directory and the relativ path to the file will then be calculated on this base.  

---

## Usage:

{babioonaddphp file=rel_path_to_the_file_with_filename}

## Example:
Joomla installation directory: /var/www/joomla

PHP-Files are in: /var/www/joomla/myphpfiles

Filename: my_file.php


**{babioonaddphp file=myphpfiles/my_file.php}**

## Plugin Configuration

![](http://i.rdbs.net/b/babioon-add-php-params-1.png)

You have to create a folder within the Joomla-Root-Directory, all normal Joomla-Directories are excluded from the list. 

![](http://i.rdbs.net/b/babioon-add-php-params-2.png)

### Event for running
**Do not run** disables executing for articles at all. Both events work in the same way the difference is that **OnContentPrepare** is executed a bit sooner then **onContentBeforeDisplay**.

### Access Rule
If you chose **deny** then the listed articles are excluded from the execution of the script and for **allow** the execution only runs on these articles.

### Article List
Comma separated list of article ids like 23,45,66 

![](http://i.rdbs.net/b/babioon-add-php-params-3.png)
