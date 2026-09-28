<?php
/*------------------------------------------------------------------------
# mod_jt_testimonial Extension
# ------------------------------------------------------------------------
# author    joomlatema
# copyright Copyright (C) 2022 joomlatema.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomlatema.net
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

HTMLHelper::_('jquery.framework');	

$app = Factory::getApplication();

require ModuleHelper::getLayoutPath('mod_jt_testimonial', $params->get('layout', 'default'));
