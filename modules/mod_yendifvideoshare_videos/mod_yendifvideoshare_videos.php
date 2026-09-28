<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Videos
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Helper\ModuleHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

$params = YendifVideoShareHelper::resolveParams( $params );

require ModuleHelper::getLayoutPath( 'mod_yendifvideoshare_videos' );
