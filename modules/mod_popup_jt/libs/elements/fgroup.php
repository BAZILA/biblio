<?php 
 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Get a collection of categories
 */ 	
class JFormFieldFgroup extends JFormField
{
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'fgroup';
	/**
	 * fetch Element 
	 */
	function getInput()
	{
		$mediaPath = JURI::root(). str_replace(DS, '/', str_replace(JPATH_ROOT, '',dirname(dirname(dirname(__FILE__))))).'/assets/';
		//JHTML::stylesheet($mediaPath .'form.css');
		$attributes = $this->element;
		$class 		= isset($attributes['group']) && trim($attributes['group']) == 'end' ? 'jtn-end-group' : 'jtn-group'; 
		$title		=  isset($attributes['title']) ?  JText::_($attributes['title']):'';
		$for 		= isset($attributes['for'])?$attributes['for']:'';
		$string 	= '<div '.($title?"":'style="display:none"').'  class="'.$class.'" title="'.$for.'">'.$title.'</div>';
		
		if(!defined('JTN_ADDED_TIME'))
		{
			$string .= '<input type="hidden" class="text_area" value="'.time().'" id="jform_params_jtn_added_time" name="jform[params][jtn_added_time]">';
			define('JTN_ADDED_TIME',1);
		}

		if(!defined('ADD_MEDIA_CONTROL'))
		{
			define('ADD_MEDIA_CONTROL',1);
			$uri = str_replace(DS,"/",str_replace(JPATH_SITE, JURI::base(), dirname(__FILE__)));
			$uri = str_replace("/administrator/", "", $uri);
			JHTML::stylesheet($uri."/media/form.css");
			JHTML::script($uri."/media/form.js");
		}
		return $string;
	}
}