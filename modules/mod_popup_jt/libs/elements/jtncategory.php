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
class JFormFieldJtnCategory extends JFormField {
	
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'JtnCategory';
	
	/**
	 * fetch Element 
	 */
	function getInput()
	{
		$db 	= &JFactory::getDBO();
		$data 	= JHtml::_('category.options', 'com_content');
		$categories 	= array();
		$categories[0] 	= new stdClass();
		$categories[0]->value 	= '';
		$categories[0]->text 	= JText::_("---------- Select All ----------");
		$data = array_merge($categories,$data);

		return JHTML::_('select.genericlist', $data, ''.$this->name.'[]', 'class="inputbox"   multiple="multiple" size="10"', 'value', 'text', $this->value,$this->id);
	}
}