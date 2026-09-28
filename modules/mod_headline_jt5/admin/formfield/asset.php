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

jimport('joomla.form.formfield');

class JFormFieldAsset extends JFormField {

    protected $type = 'Asset';

    protected function getInput() {
		//JHTML::_('behavior.framework');	
		$document	= JFactory::getDocument();		
		if (!version_compare(JVERSION, '3.0', 'ge')) {
			$checkJqueryLoaded = false;
			$header = $document->getHeadData();
			foreach($header['scripts'] as $scriptName => $scriptData)
			{
				if(substr_count($scriptName,'/jquery')){
					$checkJqueryLoaded = true;
				}
			}
				
			//Add js
			if(!$checkJqueryLoaded) 
			$document->addScript(JURI::root().$this->element['path'].'js/jquery.min.js');
			$document->addScript(JURI::root().$this->element['path'].'js/chosen.jquery.min.js');		
			$document->addScript(JURI::root().$this->element['path'].'js/colorpicker/colorpicker.js');
			$document->addScript(JURI::root().$this->element['path'].'js/jt.js');

			//Add css         
			$document->addStyleSheet(JURI::root().$this->element['path'].'css/jt.css');
			$document->addStyleSheet(JURI::root().$this->element['path'].'js/colorpicker/colorpicker.css');        
			$document->addStyleSheet(JURI::root().$this->element['path'].'css/chosen.css');        
			$document->addStyleDeclaration('.switcher-yes,.switcher-no{background-image:url('.JURI::root().JText::_('YESNO_IMAGE').')}');
		
		}else{
			$document->addScript(JURI::root().$this->element['path'].'js/colorpicker/colorpicker.js');
			$document->addScript(JURI::root().$this->element['path'].'js/jt.js');
			//Add css         
			$document->addStyleSheet(JURI::root().$this->element['path'].'css/jt.css');
			$document->addStyleSheet(JURI::root().$this->element['path'].'js/colorpicker/colorpicker.css'); 
		
		}
        
        return null;
    }
}
?>