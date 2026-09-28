<?php

/*
 * @version		$Id: helper.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class YendifVideoShareCategoriesHelper {
	
	public static function getItems( $params, $limit ) {
	
		$db = JFactory::getDBO();
		
		$query = "SELECT * FROM #__yendifvideoshare_categories WHERE published=1 AND parent=".$params->get('catid');
		
		switch( $params->get('orderby') ) {		
			case 'latest' :
				$query .= ' ORDER BY created_date DESC';
				break; 	
			case 'date_added' :
				$query .= ' ORDER BY created_date ASC';
				break;
			case 'a_z' :
				$query .= ' ORDER BY name ASC';
				break;
			case 'z_a' :
				$query .= ' ORDER BY name DESC';
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
		
		$query .= ' LIMIT ' . $limit;
		$db->setQuery( $query );
       	$items = $db->loadObjectList();
			
        return $items;
		
    }
			
}