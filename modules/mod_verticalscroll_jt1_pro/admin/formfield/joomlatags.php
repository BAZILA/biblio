<?php
/**
 * @package    Vertical scroller JT1 Pro Module for Joomla! 4.x/5.x/6.x
 * @version    3.0
 * @author     muratyil
 * @copyright  (C) 2013- muratyil
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TagField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

if (class_exists('Joomla\CMS\Form\Field\TagField')) {
    class JFormFieldJoomlaTags extends TagField
    {
        public $type = 'joomlatags';
    }
} else {
    class JFormFieldJoomlaTags extends FormField
    {
        public $type = 'joomlatags';
        
        protected function getInput()
        {
            return '<span class="' . $this->element['class'] . '">' . Text::_('ERROR_CURRENT_VERSION_DOES_NOT_SUPPORT_TAGS_COMPONENT') . '</span>';
        }
    }
}