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

use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class ImportsModel.
 *
 * @since  2.0.0
 */
class ImportsModel extends ListModel {

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
				'video_catid', 'a.video_catid',		
				'type', 'a.type',
				'import_state', 'a.import_state',
				'state', 'a.state',
				'next_import_date', 'a.next_import_date'
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
	 * @return  string A store id.
	 *
	 * @since   2.0.0
	 */
	protected function getStoreId( $id = '' ) {
		// Compile the store id
		$id .= ':' . $this->getState( 'filter.search' );		
		$id .= ':' . $this->getState( 'filter.type' );
		$id .= ':' . $this->getState( 'filter.import_state' );
		$id .= ':' . $this->getState( 'filter.state' );
		
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
		$query->from( '`#__yendifvideoshare_imports` AS a' );
		
		// Join over the categories for the category title
		$query->select( "c.title AS video_category" );
		$query->join( "LEFT", "#__yendifvideoshare_categories AS c ON c.id=a.video_catid" );

		// Join over the users for the name
		$query->select( "u.name AS user" );
		$query->join( "LEFT", "#__users AS u ON u.id=a.video_userid" );

		// Join over the users for the checked out user
		$query->select( "uc.name AS uEditor" );
		$query->join( "LEFT", "#__users AS uc ON uc.id=a.checked_out" );

		// Join over the user field 'created_by'
		$query->select( '`created_by`.name AS `created_by`' );
		$query->join( 'LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`' );

		// Join over the user field 'modified_by'
		$query->select( '`modified_by`.name AS `modified_by`' );
		$query->join( 'LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`' );		

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
		
		// Filtering video_catid
		$filter_video_catid = $this->state->get( 'filter.video_catid' );

		if ( is_numeric( $filter_video_catid ) ) {
			$query->where( 'a.video_catid = ' . (int) $filter_video_catid );
		}

		// Filtering type
		$filter_type = $this->state->get( 'filter.type' );

		if ( ! empty( $filter_type ) ) {
			$query->where( 'a.type = ' . $db->Quote( $db->escape( $filter_type ) ) );
		}

		// Filtering import_state
		$filter_import_state = $this->state->get( 'filter.import_state' );

		if ( ! empty( $filter_import_state ) ) {
			$query->where( 'a.import_state = ' . $db->Quote( $db->escape( $filter_import_state ) ) );
		}

		// Filter by published state
		$published = $this->getState( 'filter.state' );

		if ( is_numeric( $published ) )	{
			$query->where( 'a.state = ' . (int) $published );
		} elseif ( empty( $published ) ) {
			$query->where( '(a.state IN (0, 1))' );
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

		if ( $orderCol && $orderDirn ) {
			$ordering = $db->escape( $orderCol ) . ' ' . $db->escape( $orderDirn );
			$query->order( $ordering );
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
		$items = parent::getItems();
		return $items;
	}

}
