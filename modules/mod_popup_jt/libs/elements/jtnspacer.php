<?php 
 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die;
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
/**
 * Get a collection of categories
 */
class JFormFieldJtnspacer extends JFormField
{
	
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'Jtnspacer'; 	
	
	/**
	 * fetch Element 
	 */
	protected function getInput()
	{
		if(!defined('_JTNSPACER'))
		{
			define('_JTNSPACER', 1);
			$uri = str_replace(DS,"/",str_replace( JPATH_SITE, JURI::base(), dirname(__FILE__) ));
			$uri = str_replace("://", "_JTNHOLDER", $uri);
			$uri = str_replace("//","/", $uri);
			$uri = str_replace("_JTNHOLDER", "://", $uri);
			$uri = str_replace("/administrator", "", $uri);
			JHTML::stylesheet($uri.'/media/form.css');
		}
		$text = (string)$this->element['text']?(string)$this->element['text']:'';
		return '<div class="jtn-header"><div class="jtn-h3">'.JText::_($text).'</div></div>';
	}		
}