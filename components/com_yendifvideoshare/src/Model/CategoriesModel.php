<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Model;

// No direct access
defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Uri\Uri;

/**
 * Class CategoriesModel.
 *
 * @since  2.0.0
 */
class CategoriesModel extends ListModel {

	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  2.0.0
	 */
	public function __construct( $config = array(), ?MVCFactoryInterface $factory = null ) {
		if ( empty( $config['filter_fields'] ) ) {
			$config['filter_fields'] = array(
				'id', 'a.id',				
				'title', 'a.title',
				'alias', 'a.alias',
				'parent', 'a.parent',
				'image', 'a.image',
				'description', 'a.description',
				'access', 'a.access',
				'meta_keywords', 'a.meta_keywords',
				'meta_description', 'a.meta_description',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
			);
		}

		parent::__construct( $config, $factory );
	}	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function populateState( $ordering = null, $direction = null )	{
		$app = Factory::getApplication();

		$params = $app->getParams();

		// List state information
		$orderby = $params->get( 'categories_orderby', '' );
		if ( empty( $orderby ) ) {
			$orderby = $params->get( 'orderby', 'a_z' );
		}

		switch ( $orderby ) {
			case 'latest':
			case 'date_added':
				$orderCol  = 'id';
				$orderDirn = 'DESC';
				break;
			case 'a_z':
				$orderCol  = 'title';
				$orderDirn = 'ASC';
				break;
			case 'z_a':
				$orderCol  = 'title';
				$orderDirn = 'DESC';
				break;
			case 'random':
				$orderCol  = 'RAND';
				$orderDirn = '';
				break;	
			default:
				$orderCol  = 'ordering';
				$orderDirn = 'ASC';		
		}

		$this->setState( 'list.ordering', $orderCol );
		$this->setState( 'list.direction', $orderDirn );

		$no_of_rows = (int) $params->get( 'no_of_rows', 3 );
		$no_of_cols = (int) $params->get( 'no_of_cols', 3 );
		$limit = $no_of_rows * $no_of_cols;

		$this->setState( 'list.limit', $limit );

		$limitstart = $app->input->get( 'limitstart', 0, 'uint' );
		$this->setState( 'list.start', $limitstart );

		// Load the parameters
		$this->setState( 'params', $params );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery() {
		// Create a new query object
		$db    = $this->getDatabase();
		$query = $db->getQuery( true );

		// Select the required fields from the table
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);

		$query->from( $db->quoteName( '#__yendifvideoshare_categories', 'a' ) );
		$query->where( 'a.parent = 0' );		
		$query->where( 'a.state = 1' );
			
		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.title' );
		$orderDirn = $this->state->get( 'list.direction', 'ASC' );

		if ( $orderCol ) {
			if ( $orderCol == 'RAND' ) {
				$query->order( 'RAND()' );
			} else {
				if ( $orderDirn ) {
					$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
				} else {
					$query->order( $db->escape( $orderCol ) );
				}
			}
		}

		return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed  An array of data on success, false on failure.
	 * 
	 * @since   2.0.0
	 */
	public function getItems() {
		$items = parent::getItems();
		return $items;
	}
	
}
