<?php
/**
 * @package		Joomla
 * @license    GNU/GPL
 */
defined ('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldAddscript extends FormField {
  protected $type = 'Addscript';

  protected function getInput () {
    $doc = Factory::getDocument ();
    $doc->addScript ("/modules/mod_qf3/qf_mod.js");
    return '';
  }
}
