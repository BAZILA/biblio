<?php
/*------------------------------------------------------------------------
# JT Carousel Extension
# ------------------------------------------------------------------------
 * 
 * @package 	JT Carousel
 * @subpackage 	mod_jt_carousel
 * @version   	1.0
 * @author    	JoomlaTema
 * @copyright 	Copyright (C) 2008 - 2022 http://www.joomlatema.net. All rights reserved.
 * @license   	GNU General Public License version 2 or later; see LICENSE.txt
 *
 * # Website: http://www.joomlatema.net
 **/


defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Module\JTCarousel\Site\helper\jtcarouselhelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$folder		= jtcarouselhelper::getFolder($params);
$images		= jtcarouselhelper::getImages($params, $folder);


$modbase 	= URI::base(true).'/modules/mod_jt_carousel/';
$document 	= Factory::getDocument();
$document->addStyleSheet(URI::base(true).'/modules/mod_jt_carousel/assets/css/style.css');

HTMLHelper::_('jquery.framework');
$document->addScript ($modbase.'assets/js/jquery.jtcarousel.js');
$load_lightbox = $params->get("load_lightbox","1");	
if($load_lightbox == 1)	
{
$document->addScript ($modbase.'assets/js/lightbox.js');
}
require ModuleHelper::getLayoutPath('mod_jt_carousel', $params->get('layout', 'default'));