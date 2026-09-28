<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Player
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Module\YendifVideoSharePlayer\Site\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;

/**
 * Helper for mod_yendifvideoshare_player
 *
 * @since  2.0.0
 */
Class YendifVideoSharePlayerHelper {

	/**
	 * Retrieve video data
	 *
	 * @param   Joomla\Registry\Registry  &$params  Module parameters
	 *
	 * @return  object  The video item
	 * 
	 * @since   2.0.0
	 */
	public static function getItem( &$params ) {	
		$db = Factory::getDbo();		
		
		$query = 'SELECT * FROM #__yendifvideoshare_videos WHERE state=1';
			
		$id = $params->get( 'videoid' );
		$orderby = $params->get( 'orderby' );	

		if ( $id == 'latest' || $id == 'random' ) {
			$orderby = $id;
			$id = 0;
		} 

		if ( ! empty( $id ) ) {
			$query .= ' AND id = ' . (int) $id;
		} else {
			if ( $catid = $params->get( 'catid' ) ) {
				if ( is_array( $catid ) ) {
					$catids = array_map( 'intval', $catid );
					$catids = array_filter( $catids );
	
					if ( ! empty( $catids ) ) {
						$query .= ' AND catid IN (' . implode( ',', $catids ) . ')';
					}
				} else {
					$query .= ' AND catid = ' . (int) $catid;
				}
			}

			if ( $params->get( 'filterby' ) == 'featured' ) {
				$query .= ' AND featured = 1';
			}
	
			if ( $params->get( 'schedule_video_publishing' ) ) {
				$nullDate = $db->getNullDate();
				$nowDate  = Factory::getDate()->toSql();

				$query .= ' AND (published_up IS NULL OR published_up = ' . $db->quote( $nullDate ) . ' OR published_up <= ' . $db->quote( $nowDate ) . ')';
				$query .= ' AND (published_down IS NULL OR published_down = ' . $db->quote( $nullDate ) . ' OR published_down >= ' . $db->quote( $nowDate ) . ')';
			}	

			switch ( $orderby ) {
				case 'latest':
					$query .= ' ORDER BY id DESC';
					break;
				case 'date_added':
					$query .= ' ORDER BY created_date DESC';
					break;
				case 'most_viewed':
					$query .= ' ORDER BY views DESC';
					break;
				case 'most_rated':
					$query .= ' ORDER BY rating DESC';
					break;
				case 'a_z':
					$query .= ' ORDER BY title ASC';
					break;
				case 'z_a':
					$query .= ' ORDER BY title DESC';
					break;
				case 'random':
					$query .= ' ORDER BY RAND()';
					break;
				default:
					$query .= ' ORDER BY ordering ASC';
			}

			$query .= ' LIMIT 1';
		}

		$db->setQuery( $query );
		$item = $db->loadObject();		
		
		return $item;		
	}
	
}
