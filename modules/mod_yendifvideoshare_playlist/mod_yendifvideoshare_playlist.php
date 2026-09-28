<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Playlist
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;

$global = ComponentHelper::getParams( 'com_yendifvideoshare' );

$options = array(
	'autoplaylist',
	'loop',
	'ratio',
	'image_ratio',	
	'title_length',
	'show_excerpt',
	'excerpt_length',
	'show_category',
	'show_user',
	'show_date',
	'show_views',
	'show_rating',
	'schedule_video_publishing',
	'playlist_position',
	'playlist_width',
	'playlist_height',
	'custom_css'
);

foreach ( $options as $option ) {
	$value = $params->get( $option, 'global' );
	if ( $value == 'global' ) {
		$params->set( $option, $global->get( $option ) );
	}
}

require ModuleHelper::getLayoutPath( 'mod_yendifvideoshare_playlist' );
