<?php
/**
 * @package     mod_jttabs
 * @copyright   Copyright (C) 2007 - 2023 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 **/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Log\Log;
use Joomla\CMS\HTML\HTMLHelper; // Add this line

class JFormFieldArticles extends FormField
{
    protected $type = 'Articles'; //the form field type
    
    protected function getInput()
    {
        // Initialize variables.
        $html = array();
        $attr = '';
        
        $options = array();
        $db	= Factory::getDbo();
        
        $db->setQuery(
          'SELECT `id` as value, `title` as text FROM #__content;'
        );
        
        try
        {
          $articles = $db->loadObjectList();
        }
        catch (RuntimeException $e)
        {
          Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        
        if (empty($articles)) $articles = array();
        
        $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        
        $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
        $attr .= $this->multiple ? ' multiple="multiple"' : '';
        
        // Create a regular list - use HTMLHelper instead of JHtml
        $html[] = HTMLHelper::_('select.genericlist', $articles, $this->name, $attr, 'value', 'text', $this->value, $this->id);
        
        return implode("\n", $html);
    }
}