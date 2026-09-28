<?php

/*
 * @version		$Id: helper.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access'); 

// include the dependencies
require_once( JPATH_ROOT.'/administrator/components/com_yendifvideoshare/libraries/utils.php' );

class YendifVideoSharePlaylistHelper {
	private $players = 0;
	
	
	public static function getItems( $params , $check_publishing_options ) {	
	
		foreach( $params as $key => $value ) {
			if( $value == '' || $value == 'global' ) unset( $params[ $key ] );
		}
		
		$db = JFactory::getDBO();
        $query = "SELECT * FROM #__yendifvideoshare_videos";
		
		$where = array();
		$where[] = "published=1";
		
		if( $check_publishing_options ) {
			$date = JFactory::getDate();
			
			$nullDate = $db->quote( $db->getNullDate() );
	    	$nowDate  = $db->quote( $date->toSql() );
		
			$where[] = " ( published_up = " . $nullDate . " OR published_up <= " . $nowDate .' )' ;
			$where[] = " ( published_down = " . $nullDate . " OR published_down >= " . $nowDate.' )';	
		}	
			
		$where[] = "type!=".$db->Quote('thirdparty');
		
		if( isset( $params['catid'] ) && $params['catid'] > 0 ) {
			$where[] = "catid=".$params['catid'];
		}
		
		if( isset( $params['featured']) && $params['featured'] == 'featured' ) {
		 	$where[] = "featured=1";
		}
	
		$user = JFactory::getUser();
		$viewLevels = $user->getAuthorisedViewLevels();
		$where[] = "access IN (''," . implode(',', $viewLevels) . ")";
		
		$where = ( count($where) ? ' WHERE '. implode(' AND ', $where) : '' );		 
		$query .= $where;	
		
		$orderby = isset( $params['orderby'] ) ? $params['orderby'] : '';
		switch( $orderby ) {	
			case 'latest' :
				$query .= ' ORDER BY created_date DESC';
				break;		
			case 'most_viewed' :
				$query .= ' ORDER BY views DESC';
				break;
			case 'most_rated' :
				$query .= ' ORDER BY rating DESC';
				break;
			case 'date_added' :
				$query .= ' ORDER BY created_date ASC';
				break;
			case 'a_z' :
				$query .= ' ORDER BY title ASC';
				break;
			case 'z_a' :
				$query .= ' ORDER BY title DESC';
				break;
			case 'random' :
				$query .= ' ORDER BY RAND()';
				break;
			case 'ordering' :
				$query .= ' ORDER BY ordering';
				break;
			default :
				$query .= ' ORDER BY id DESC';				
		}

		if( isset($params['limit']) && !empty($params['limit']) ) {
			$query .= " LIMIT ". (int) $params['limit'];
		}
		
        $db->setQuery( $query );
        $items = $db->loadObjectList();
		if( count( $items ) == 0 ) return null;
		
		return $items;
	
	}



 }