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

if(class_exists('JFormFieldTag')){
	class JFormFieldJoomlaTags extends JFormFieldTag {
		public $type = 'joomlatags';
		
	}
}else{
	class JFormFieldJoomlaTags extends JFormField {
		public $type = 'joomlatags';
		protected function getInput(){
			return '<span class="' . $this->element['class'] . '">' . JText::_('ERROR_CURRENT_VERSION_DOES_NOT_SUPPORT_TAGS_COMPONENT') . '</span>';
		}
	}
}