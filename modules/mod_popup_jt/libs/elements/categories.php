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
class JFormFieldCategories extends JFormField
{
    var $_name = 'Categories';

    function getInput()
    {
        $db 		= &JFactory::getDBO();
		$attributes = $this->element;
		$parent_id  = $attributes['parent'];
        $class    	= $attributes['class'];
		
        if(!$class)
		{
            $class = "inputbox";
        }
        $query = 'SELECT c.id AS value, c.title AS text' .
				' FROM #__categories AS c' .
				' WHERE c.published = 1' .
				' AND c.parent_id = '.$db->Quote($parent_id).
				' ORDER BY c.title';
				
        $db->setQuery($query);
        $options 	= $db->loadObjectList();
		$categories = array();
		
		$categories[0] = new stdClass();
		$categories[0]->value = '';
		$categories[0]->text = JText::_("---------- Select All ----------");
		$options = array_merge($categories,$options);	
        return JHTML::_('select.genericlist',  $options, ''.$this->name.'[]', 'class="inputbox" size="5" style="width:95%;" multiple="multiple"', 'value', 'text', $this->value, $this->fieldname);
    }
}