<?php 
/**
* @title		JT Image Gallery  module
* @website		http://www.joomlatema.net
* @copyright	Copyright (C) 2008 -2023 JoomlaTema.net. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/
defined('_JEXEC') or die;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('jquery.framework');	
////////
if($params->get('showtitle')==0)
{ $showtitle='this.title = false; ';
}
else{
$showtitle="this.title = $(this.element).attr('data-caption');";
}

//////////
$document = Factory::getDocument();
$document->addStyleSheet(URI::root() . 'modules/mod_jt_image_gallery/css/jquery.fancybox.css');
$document->addStyleSheet(URI::base() . 'modules/mod_jt_image_gallery/css/gallery.css');
$document->addStyleSheet(URI::base() . 'modules/mod_jt_image_gallery/css/slickcarousel.css');
$document->addStyleSheet(URI::base() . 'modules/mod_jt_image_gallery/css/slick-theme.css');
//$document->addScript('modules/mod_jt_image_gallery/js/jquery.js');
if ($params->get('loadFancybox')==1)
{
$document->addScript('modules/mod_jt_image_gallery/js/jquery.fancybox.pack.js');
$document->addScript('modules/mod_jt_image_gallery/js/slick.min.js');
}
else if ($params->get('loadFancybox')==0){
$document->addScript('modules/mod_jt_image_gallery/js/slick.min.js');
}
require_once dirname(__FILE__) . '/helper.php';

// Get module parameters
//$params = ModuleHelper::getParams($module);

// Include the layout file
require ModuleHelper::getLayoutPath('mod_jt_image_gallery', $params->get('layout', 'default'));


?>
