<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Uri\Uri;

/**
 * Class YendifVideoSharePlayer
 *
 * @since  2.0.0
 */
class YendifVideoSharePlayer {	

	/**
	 * Get the player HTML
	 *
	 * @param   array   $config  The player configuration parameters
	 * @param   object  $params  The component params
	 *
	 * @return  string
	 * 
	 * @since   2.0.0
	 */
	public static function load( $config = array(), $params = '' ) {
		$app  = Factory::getApplication();
		$lang = Factory::getLanguage();

		// Get params
		if ( empty( $params ) ) {
			$params = ComponentHelper::getParams( 'com_yendifvideoshare' );
		}

		// Load component language file		
		$lang->load( 'com_yendifvideoshare', JPATH_SITE );

		// Import CSS		
		$wa = $app->getDocument()->getWebAssetManager();

		if ( ! $wa->assetExists( 'style', 'com_yendifvideoshare.site' ) ) {
			$wr = $wa->getRegistry();
			$wr->addRegistryFile( 'media/com_yendifvideoshare/joomla.asset.json' );
		}

		$wa->useStyle( 'com_yendifvideoshare.site' );		

		// Get the player URL
		$src = self::getURL( $config );
			
		// Build player HTML
		$width = ! empty( $config['width'] ) ? $config['width'] : $params->get( 'width', 0 );
		$ratio = ! empty( $config['ratio'] ) ? $config['ratio'] : $params->get( 'ratio', 56.25 );

		$html = sprintf( 
			'<div class="yendif-player-wrapper" style="max-width: %s;"><div class="yendif-player" style="padding-bottom: %s;"><iframe width="560" height="315" src="%s" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>', 
			( ! empty( $width ) ? (int) $width . 'px' : '100%' ),
			(float) $ratio . '%', 
			$src
		);

		// Return
		return $html;
	}

	/**
	 * Get the player URL
	 *
	 * @param   array  $config  The player configuration parameters
	 *
	 * @return  string
	 * 
	 * @since   2.0.0
	 */
	public static function getURL( $config = array() ) {
		// Build player URL
		$query = array(
			'option' => 'com_yendifvideoshare',
			'view'   => 'player',
			'id'     => 0,
			'format' => 'raw'
		);
		
		if ( isset( $config['videoid'] ) ) {
			$query['id'] = (int) $config['videoid'];
		} else {
			$sources = array( 'mp4', 'mp4_hd', 'webm', 'ogv', 'youtube', 'vimeo', 'hls', 'dash', 'image', 'captions' );

			foreach ( $sources as $source ) {
				if ( isset( $config[ $source ] ) ) {
					$query[ $source ] = base64_encode( $config[ $source ] );
				}
			}
		}

		$properties = array( 'id', 'autoplay', 'loop', 'volume', 'playbtn', 'controlbar', 'playpause', 'currenttime', 'progress', 'duration', 'volumebtn', 'fullscreen', 'embed', 'share', 'autoplaylist', 'uid', 'Itemid' );

		foreach ( $properties as $property ) {
			if ( isset( $config[ $property ] ) ) {
				$query[ $property ] = (int) $config[ $property ];
			}
		}

		$src = Uri::root() . 'index.php?' . http_build_query( $query );

		// Return
		return $src;
	}

	/**
	 * Update views count
	 *
	 * @param   int  $id  The video id.
	 * 
	 * @since   2.0.0
	 */
	public static function updateViews( $id ) {		
		$session = Factory::getSession();
		
		$stored = array();		
		if ( $session->get( 'com_yendifvideoshare_views' ) ) {
			$stored = $session->get( 'com_yendifvideoshare_views' );
		}
		
		if ( ! in_array( $id, $stored ) ) {
		    $stored[] = $id;				
	 
			$db = Factory::getDbo();

		 	$query = 'UPDATE #__yendifvideoshare_videos SET views=views+1 WHERE id=' . (int) $id;
    	 	$db->setQuery( $query );
		 	$db->execute();
		 
		 	$session->set( 'com_yendifvideoshare_views', $stored );
		}		
	}
	
}
