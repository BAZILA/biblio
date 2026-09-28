<?php
 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.filesystem.folder');

class JFormFieldJtnEffect extends JFormField {

    /**
     * @access private
     */
	 
	var	$_name = 'Jtneffect';
	var $_params = null;
	
	function __getValue($name1,$name2, $default=''){
		$val = $this->form->getValue($name1, 'params', $default);
		
		return is_array($val)&& isset($val[$name2])? $val[$name2]:$default;
	}
	
	function getInput()
	{   
		if(!defined('ADD_MEDIA_CONTROL'))
		{
			define('ADD_MEDIA_CONTROL',1);
			$uri = str_replace(DS,"/",str_replace(JPATH_SITE, JURI::base(), dirname(__FILE__)));
			$uri = str_replace("/administrator/", "", $uri);
			JHTML::stylesheet($uri."/media/form.css");
			JHTML::script($uri."/media/form.js");
		}
		// function fetchElement($name, $value, &$node, $control_name) {
		$value = $this->value; 

		$name = $this->element['name'];
		$cur_index = $this->element['index'];
		$control_name ='jform[params]';
		

	
		$elms = array(
					  ); 
		
	 		$ynoptions = array( '0'  => JText::_('No'),
						        '1' =>  JText::_('Yes')
							 );
		$onshow = false;
		$string = '<div style="clear:both" >';
		$enableValue = $this->__getValue( $name,'enable','');
		if(1 == $enableValue || $name == "file1"){
			$enableValue = 1;
		}
		$string .='<input class="jtn-showcontainer jtn-enable" id="jtn-enable'.$cur_index.'" name="'.$control_name.'['.$name.'][enable]" type="hidden" value="'.$this->__getValue( $name,'enable','').'"/>';

		$string .='</div>';
		$strMods = $this->_getModules( $name, $control_name, "image");
		$string .= '<div class="jtn-cols" ><label>'.JText::_("CHOOSE_MODULE").'</label>'.$strMods.'</div>';
		foreach( $elms as $key=>$elm ){
			$string .= '<div class="jtn-cols" ><label>'.$elm['text'].'</label><input type="text" size="30" maxlength="255" value="'. $this->__getValue( $name,$key,'')  .'" id="jform_params_'.$name.'_'.$key.'" name="'.
						$control_name.'['.$name.']['.$key.']'
					.'"/></div>';
		}
		if($name == "file1"){
			$string .= '<div style="clear:both;text-align:right;width:80%" class="jtn-cols add_control_'.$name.'"><a href="#add" class="addControl" onclick="addFile('.$cur_index.');" title="'.JText::_("Add_Module").'">+</a></div>';
		}
		elseif($name == "file6"){
			$string .= '<div style="clear:both;text-align:right;width:80%" class="jtn-cols add_control_'.$name.'"><a href="#remove" class="removeControl" onclick="removeFile(this);" title="'.JText::_("Remove").'">-</a></div>';
		}
		else{
			$string .= '<div style="clear:both;text-align:right;width:80%" class="jtn-cols add_control_'.$name.'"><a href="#add" class="addControl" onclick="addFile('.$cur_index.');" title="'.JText::_("Add_Module").'">+</a>&nbsp;&nbsp;<a href="#remove" class="removeControl" onclick="removeFile(this);" title="'.JText::_("Remove").'">-</a></div>';
		}
		return $string;
	}
	
	function _getModules($name, $control_name="", $key = "image")
	{
		$str = '<select id="jform_params_'.$name.'_'.$key.'" name="'.
							$control_name.'['.$name.']['.$key.']'.'">';
		$db = JFactory::getDBO();
		
		$query = "SELECT id, module, title FROM #__modules WHERE published=1 AND client_id=0 ORDER BY title";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		$value = $this->__getValue( $name, $key );
		
		if(!empty($rows)){
			foreach($rows as $row){
				if($row->id == $value ){
						$str.= '<option value="'.$row->id.'" selected="selected">'.$row->title.'</option>';
					}
					else{
						$str.= '<option value="'.$row->id.'">'.$row->title.'</option>';
					}
			}
		}
		
		$str .= '</select>';
		
		return $str;
	}
}
