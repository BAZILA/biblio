<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Router\Route;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class YendifVideoShareRoute.
 *
 * @since  2.0.0
 */
abstract class YendifVideoShareRoute {

	/**
	 * Get the URL route for categories
	 *	 
	 * @param   int     $itemid    The id of the menu item.
	 * @param   int     $catid     The category ID.
	 * @param   mixed   $language  The id of the language being used.
	 * @param   string  $format    The view format.
	 *
	 * @return  string  The link to the categories
	 *
	 * @since   2.1.1
	 */
	public static function getCategoriesRoute( $itemid = 0, $catid = 0, $language = 0, $format = '' ) {
		$link = '';

		if ( $itemid > 0 ) {
			$link = "index.php?option=com_yendifvideoshare&view=categories&Itemid=$itemid";
		} else {
			if ( $catid > 0 ) {
				if ( $category = YendifVideoShareHelper::getCategory( $catid, array( 'id', 'alias' ) ) ) {
					$link = self::getCategoryRoute( $category, $itemid, $language, $format );
				}
			} else {
				if ( $itemid = self::findMenuItem( 'category' ) ) {
					$link = "index.php?option=com_yendifvideoshare&view=categories&Itemid=$itemid";
				}
			}
		}

		return $link;
	}

	/**
	 * Get the URL route for a category
	 *
	 * @param   object  $item      The category object.
	 * @param   int     $itemid    The id of the menu item.
	 * @param   mixed   $language  The id of the language being used.
	 * @param   string  $format    The view format.
	 *
	 * @return  string  The link to the category
	 *
	 * @since   2.0.0
	 */
	public static function getCategoryRoute( $item, $itemid = 0, $language = 0, $format = '' ) {
		if ( empty( $itemid ) ) {
			$params = ComponentHelper::getParams( 'com_yendifvideoshare' );
			$itemid = (int) $params->get( 'itemid_category' );
		}

		if ( $itemid == -1 ) {
			$link = self::getRoute( 'category', $item );
		} else {
			if ( empty( $itemid ) ) {
				$itemid = Factory::getApplication()->input->getInt( 'Itemid', 0 );
			}

			$link = 'index.php?option=com_yendifvideoshare&view=category&id=' . $item->id . ':' . $item->alias;
			if ( $itemid > 0 ) {
				$link .= '&Itemid=' . $itemid;
			}
		}

		if ( $language && $language !== '*' && Multilanguage::isEnabled() ) {
			$link .= '&lang=' . $language;
		}

		if ( $format ) {
			$link .= '&format=' . $format;
		}

		return $link;
	}

	/**
	 * Get the URL route for videos
	 *
	 * @param   int     $itemid    The id of the menu item.
	 * @param   int     $catid     The category ID.
	 * @param   mixed   $language  The id of the language being used.
	 * @param   string  $format    The view format.
	 *
	 * @return  string  The link to the videos
	 *
	 * @since   2.1.1
	 */
	public static function getVideosRoute( $itemid = 0, $catid = 0, $language = 0, $format = '' ) {
		$link = '';

		if ( $itemid > 0 ) {
			$link = "index.php?option=com_yendifvideoshare&view=videos&Itemid=$itemid";
		} else {
			if ( $catid > 0 ) {
				if ( $category = YendifVideoShareHelper::getCategory( $catid, array( 'id', 'alias' ) ) ) {
					$link = self::getCategoryRoute( $category, $itemid, $language, $format );
				}
			} else {
				if ( $itemid = self::findMenuItem( 'video' ) ) {
					$link = "index.php?option=com_yendifvideoshare&view=videos&Itemid=$itemid";
				}
			}
		}

		return $link;
	}

	/**
	 * Get the URL route for a video
	 *
	 * @param   object  $item      The video object.
	 * @param   int     $itemid    The id of the menu item.
	 * @param   mixed   $language  The id of the language being used.
	 *
	 * @return  string  The link to the video
	 *
	 * @since   2.0.0
	 */
	public static function getVideoRoute( $item, $itemid = 0, $language = 0 ) {
		if ( empty( $itemid ) ) {
			$params = ComponentHelper::getParams( 'com_yendifvideoshare' );
			$itemid = (int) $params->get( 'itemid_video' );
		}

		if ( $itemid == -1 ) {
			$link = self::getRoute( 'video', $item );
		} else {
			if ( empty( $itemid ) ) {
				$itemid = Factory::getApplication()->input->getInt( 'Itemid', 0 );
			}

			$link = 'index.php?option=com_yendifvideoshare&view=video&id=' . $item->id . ':' . $item->alias;
			if ( $itemid > 0 ) {
				$link .= '&Itemid=' . $itemid;
			}
		}

		if ( $language && $language !== '*' && Multilanguage::isEnabled() ) {
			$link .= '&lang=' . $language;
		}
		
		return $link;
	}

	/**
	 * Get the URL route for a search
	 *
	 * @param   int     $itemid    The id of the menu item.
	 * @param   mixed   $language  The id of the language being used.
	 *
	 * @return  string  The link to the search
	 *
	 * @since   2.0.0
	 */
	public static function getSearchRoute( $itemid = 0, $language = 0 ) {
		if ( empty( $itemid ) ) {
			$itemid = Factory::getApplication()->input->getInt( 'Itemid', 0 );
		}

		$link = 'index.php?option=com_yendifvideoshare&view=search';
		if ( $itemid > 0 ) {
			$link .= '&Itemid=' . (int) $itemid;
		}

		if ( $language && $language !== '*' && Multilanguage::isEnabled() ) {
			$link .= '&lang=' . $language;
		}

		return $link;
	}

	private static function getRoute( $view, $item ) {
		$is_exact_match_found = 0;
		
		// Check if there is a menu item with the given ID value for the view
		$itemid = self::findMenuItem( $view, $item->id );
		if ( $itemid > 0 ) {
			$is_exact_match_found = 1;
		}
		
		// Check if there is a menu item atleast for the view
		if ( empty( $itemid ) ) {
			$itemid = self::findMenuItem( $view );
		}
		
		// Fallback to the current itemid
		if ( empty( $itemid ) ) {
			$itemid = Factory::getApplication()->input->getInt( 'Itemid', 0 );
		}

		// Build route
		if ( $is_exact_match_found ) {
			$route = "index.php?Itemid=$itemid";
		} else {
			$route = "index.php?option=com_yendifvideoshare&view=$view&id={$item->id}:{$item->alias}";

			if ( ! empty( $itemid ) ) {
				$route .= "&Itemid=$itemid";
			}
		}
		
		return $route;	
	}

	private static function findMenuItem( $view, $id = 0, $itemid = 0 ) {	
		$db = Factory::getDbo();
		
		$query = sprintf(
			'SELECT id FROM #__menu WHERE link=%s AND published=1 LIMIT 1',
			$db->quote( "index.php?option=com_yendifvideoshare&view=$view&id=$id" )
		);

		$db->setQuery( $query );

		if ( $id = $db->loadResult() ) {
			$itemid = $id;
		}
		
		return $itemid;
	}

}
