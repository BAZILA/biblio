<?php
/**
 * @package Vertical scroller JT1 Pro Module for Joomla! 2.5
 * @version $Id: 1.0 
 * @author muratyil
 * @Copyright (C) 2013- muratyil
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldK2Tags extends JFormFieldList{
	public $type = 'k2tags';
	
	public function getInput(){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT enabled FROM #__extensions WHERE name = ' . $db->quote('com_k2'));
		$isEnabled = $db->loadResult();
        if ($isEnabled) { 
			return parent::getInput();
		}else{
			return '<span class="' . $this->element['class'] . '">' . JText::_('K2_IS_NOT_ENABLED_OR_INSTALLED') . '</span>';
		}
	}
	
	public function getOptions(){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, name FROM #__k2_tags WHERE published = 1');
		$rs = $db->loadObjectList();
		$options = array();
		if($rs){
			foreach($rs as $tag){
				$options[] = JHtml::_('select.option', $tag->id, $tag->name);
			}
		}
		return $options;
	}
}