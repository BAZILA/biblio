<?php

/*
 * @version		$Id: helper.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class YendifVideoSharePlayerHelper {   
	
	public static function getVideo( $params, $check_publishing_options ) {	
	
        $db = JFactory::getDBO();

		$query = "SELECT * FROM #__yendifvideoshare_videos WHERE published=1";
		
		if( $params->get('catid', 0) > 0 ) {
			$query .= " AND catid = " . $params->get('catid');
		}	
		
		if( $check_publishing_options ) {
			$date = JFactory::getDate();
			
			$nullDate = $db->quote( $db->getNullDate() );		
			$nowDate  = $db->quote( $date->toSql() );
		
			$query .= " AND ( published_up = " . $nullDate . " OR published_up <= " . $nowDate .' )';
			$query .= " AND ( published_down = " . $nullDate . " OR published_down >= " . $nowDate .' )';
		}
		
		switch( $params->get('videoid') ) {		 		
			case 'latest' :
				$query .= ' ORDER BY created_date DESC';
				break;
			case 'random' :
				$query .= ' ORDER BY RAND()';
				break;
			default :
				$query .= ' AND id=' . (int) $params->get('videoid');
		}
					
		$query .= ' LIMIT 1';

        $db->setQuery( $query );
        $item = $db->loadObject();
		
		return $item;
		
	}
		
}