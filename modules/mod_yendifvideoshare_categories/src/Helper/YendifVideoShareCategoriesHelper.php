<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Categories
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Module\YendifVideoShareCategories\Site\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;

/**
 * Helper for mod_yendifvideoshare_categories
 *
 * @since  2.0.0
 */
Class YendifVideoShareCategoriesHelper {

	/**
	 * Retrieve categories
	 *
	 * @param   Joomla\Registry\Registry  &$params  Module parameters
	 *
	 * @return  array  The categories list.
	 * 
	 * @since   2.0.0
	 */
	public static function getItems( &$params ) {	
		$db = Factory::getDbo();

		$query = 'SELECT * FROM #__yendifvideoshare_categories WHERE state=1 AND parent=' . (int) $params->get( 'catid', 0 );
		
		switch ( $params->get( 'orderby' ) ) {
			case 'latest':
			case 'date_added':
				$query .= ' ORDER BY id DESC';
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
		
		$limit = (int) $params->get( 'no_of_rows', 3 ) * (int) $params->get( 'no_of_cols', 3 );
		
		$db->setQuery( $query, 0, $limit );
       	$items = $db->loadObjectList();

        return $items;		
    }
	
}
