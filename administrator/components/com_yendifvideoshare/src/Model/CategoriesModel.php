<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Model;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class CategoriesModel.
 *
 * @since  2.0.0
 */
class CategoriesModel extends ListModel {

	var $tree = array();

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
		// List state information
		parent::populateState( 'a.id', 'DESC' );

		$context = $this->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $context );

		// Split context into component and optional section
		$parts = FieldsHelper::extract( $context );

		if ( $parts ) {
			$this->setState( 'filter.component', $parts[0] );
			$this->setState( 'filter.section', $parts[1] );
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.0.0
	 */
	protected function getStoreId( $id = '' ) {
		// Compile the store id
		$id .= ':' . $this->getState( 'filter.search' );
		$id .= ':' . $this->getState( 'filter.state' );
		$id .= ':' . $this->getState( 'filter.access' );
		
		return parent::getStoreId( $id );		
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
		$query->from( '`#__yendifvideoshare_categories` AS a' );
		
		// Join over the users for the checked out user
		$query->select( "uc.name AS uEditor" );
		$query->join( "LEFT", "#__users AS uc ON uc.id=a.checked_out" );

		// Join over the user field 'created_by'
		$query->select( '`created_by`.name AS `created_by`' );
		$query->join( 'LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`' );

		// Join over the user field 'modified_by'
		$query->select( '`modified_by`.name AS `modified_by`' );
		$query->join( 'LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`' );

		// Join over the access level field 'access'
		$query->select( '`access`.title AS `access`' );
		$query->join( 'LEFT', '#__viewlevels AS access ON `access`.id = a.`access`' );		

		// Filter by published state
		$published = $this->getState( 'filter.state' );

		if ( is_numeric( $published ) )	{
			$query->where( 'a.state = ' . (int) $published );
		} elseif ( empty( $published ) ) {
			$query->where( '(a.state IN (0, 1))' );
		}

		// Filter by access level
		$access = $this->getState( 'filter.access' );

		if ( is_numeric( $access ) ) {
			$query->where( 'a.access = ' . (int) $access );
		}

		// Filter by search in title
		$search = $this->getState( 'filter.search' );

		if ( ! empty( $search ) ) {
			if ( stripos( $search, 'id:' ) === 0 ) {
				$query->where( 'a.id = ' . (int) substr( $search, 3 ) );
			} else {
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
				$query->where( '( a.title LIKE ' . $search . ' )' );
			}
		}
		
		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

		if ( $orderCol && $orderDirn ) {
			$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return  mixed  Array of data items on success, false on failure.
	 * 
	 * @since   2.0.0
	 */
	public function getItems() {
		$parent = ! empty( $this->getState( 'filter.search' ) ) ? -1 : 0;
		$this->buildTree( $parent );
		
		$items = array();

		foreach ( $this->tree as $item ) {
			$item->parents = $this->getAncestors( $item->id );
			$items[] = $item;
		}

		return $items;
	}

	private function buildTree( $parent = 0, $spcr = '' ) {
		$db = $this->getDatabase();

		$query = $this->getListQuery();
		if ( $parent >= 0 ) {
			$query->where( 'a.parent = ' . (int) $parent );
		}
		$query->setLimit( 0, 0 );
		 	    
        $db->setQuery( $query );
   		$items = $db->loadObjectList();

		$count = count( $items );

        if ( $count > 0 ) {		
            foreach ( $items as $index => $item ) {
				$item->up   = ( $index == 0 ) ? 0 : 1;
                $item->down = ( $index + 1 == $count ) ? 0 : 1;
				$item->spcr = ( $item->parent > 0 ) ? $spcr . "—" : $spcr;
		
				$this->tree[ $item->id ] = $item;
				$this->buildTree( $item->id, $spcr . "—" );
            }
        }	
	}

	private function getAncestors( $id ) {	
		$ids = array();
		$parent = $id;

		while ( $parent != 0 ) {
			$parent = isset( $this->tree[ $parent ] ) ? $this->tree[ $parent ]->parent : 0;
			$ids[] = $parent;
		}		
			
		return $ids;		
	}
	
}
