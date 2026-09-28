<?php

/*
 * @version		$Id: helper.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class YendifVideoShareVideosHelper {
	
	public static function getItems( $params, $check_publishing_options, $limit ) {	
	
		$db = JFactory::getDBO();

		$query  = "SELECT v.*, c.name as category, c.alias as catalias FROM #__yendifvideoshare_videos AS v";	
		$query .= " LEFT JOIN #__yendifvideoshare_categories AS c ON v.catid = c.id";		
			
		$where = array();
		
		$where[] = "v.published=1";
		
		if( $check_publishing_options ) {
			$date = JFactory::getDate();
			
			$nullDate = $db->quote( $db->getNullDate() );
			$nowDate  = $db->quote( $date->toSql() );
				 
		 	$where[] = "(v.published_up = " . $nullDate . " OR v.published_up <= " . $nowDate.')';
			$where[] = "(v.published_down = " . $nullDate . " OR v.published_down >= " . $nowDate.')';											 			
		 }				
		
		if( $params->get('catid', 0) > 0 ) {
			$where[] = "v.catid=".$params->get('catid');
		}
			
		if( $params->get('filterby') == 'featured' ) {
			$where[] = "v.featured=1";
		}
		
		$where = count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '';		 
		$query .= $where;			
		
		switch ( $params->get('orderby') ) {	
			case 'latest' :
				$query .= ' ORDER BY v.created_date DESC';
				break;	 	
			case 'most_viewed' :
				$query .= ' ORDER BY v.views DESC';
				break;
			case 'most_rated' :
				$query .= ' ORDER BY v.rating DESC';
				break;
			case 'date_added' :
				$query .= ' ORDER BY v.created_date ASC';
				break;
			case 'a_z' :
				$query .= ' ORDER BY v.title ASC';
				break;
			case 'z_a' :
				$query .= ' ORDER BY v.title DESC';
				break;
			case 'random' :
				$query .= ' ORDER BY RAND()';
				break;
			case 'ordering' :
				$query .= ' ORDER BY v.catid, v.ordering';
				break;
			default :
				$query .= ' ORDER BY v.id DESC';
		}
		
		$query .= ' LIMIT ' . $limit;
		$db->setQuery( $query );
       	$items = $db->loadObjectList();
			
        return $items;
		
    }
	
	public static function getCategory( $catid ) {	
	
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__yendifvideoshare_categories WHERE id=".$catid;	
	 	$db->setQuery( $query );
       	$item = $db->loadObject();
			
        return $item;
	
	}
		
}