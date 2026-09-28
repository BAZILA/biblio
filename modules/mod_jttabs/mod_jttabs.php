<?php
/**
 * @package     mod_jttabs
 * @copyright   Copyright (C) 2007 - 2023 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 **/

// no direct access
defined('_JEXEC') or die ('Restricteted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Utilities\ArrayHelper;
Use Joomla\String\StringHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('jquery.framework');
HTMLHelper::_('bootstrap.framework'); 
require_once JPATH_SITE.'/modules/mod_jttabs/helper.php';
$doc = Factory::getDocument();
$modpath = URI::root(true).'/modules/' . $module->module . '/assets/';

$doc->addStyleSheet($modpath.'css/mod_jttabs_style.css');

require ModuleHelper::getLayoutPath('mod_jttabs', $params->get('layout', 'default'));
?>
