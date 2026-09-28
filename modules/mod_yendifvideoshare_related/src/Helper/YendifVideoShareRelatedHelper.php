<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Related
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Module\YendifVideoShareRelated\Site\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;

/**
 * Helper for mod_yendifvideoshare_related
 *
 * @since  2.1.1
 */
Class YendifVideoShareRelatedHelper {

	/**
	 * Retrieve videos
	 *
	 * @param   Joomla\Registry\Registry  &$params  Module parameters
	 *
	 * @return  array  The videos list.
	 * 
	 * @since   2.1.1
	 */
	public static function getItems( &$params ) {
		$app = Factory::getApplication();	
		$db  = Factory::getDbo();			
		
		$items = array();

		if ( $app->input->get( 'option' ) == 'com_yendifvideoshare' && $app->input->get( 'view' ) == 'video' && $id = $app->input->getInt( 'id' ) ) {
			$query = 'SELECT id,catid,related FROM #__yendifvideoshare_videos WHERE id = ' . $id;
			$db->setQuery( $query );
			$item = $db->loadObject();

			if ( empty( $item ) ) {
				return false;
			}			
			
			$limit = 10;

			$related_ids = array();
			if ( ! empty( $item->related ) ) {
				$related_ids = explode( ',', $item->related );
				$related_ids = array_map( 'intval', $related_ids );
				$related_ids = array_filter( $related_ids );

				if ( ( $key = array_search( $id, $related_ids ) ) !== false ) {
					unset( $related_ids[ $key ] );
				}
			}
			
			// Get related videos
			$query = $db->getQuery( true );

			$query->select( '*' );
			$query->from( $db->quoteName( '#__yendifvideoshare_videos' ) );

			if ( ! empty( $related_ids ) ) {
				$query->where( 'id IN (' . implode( ',', $related_ids ) . ')' );
				$query->order( 'FIELD(id, ' . implode( ',', $related_ids ) . ')' );
				
				$limit = 0;
			} else {
				$query->where( 'id != ' . (int) $item->id );
				$query->where( 'catid = ' . (int) $item->catid );
				$query->order( 'RAND()' );			
			}

			if ( $params->get( 'schedule_video_publishing' ) ) {
				$nullDate = $db->getNullDate();
				$nowDate  = Factory::getDate()->toSql();
	
				$query->where( '(published_up IS NULL OR published_up = ' . $db->quote( $nullDate ) . ' OR published_up <= ' . $db->quote( $nowDate ) . ')' );
				$query->where( '(published_down IS NULL OR published_down = ' . $db->quote( $nullDate ) . ' OR published_down >= ' . $db->quote( $nowDate ) . ')' );
			}

			$query->where( $db->quoteName( 'state' ) . ' = 1' );

			if ( $limit > 0 ) {
				$db->setQuery( $query, 0, $limit );
			} else {
				$db->setQuery( $query );
			}
			
			$items = $db->loadObjectList();
		}			

        return $items;		
    }	
	
}
