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

/**
 * Class VideosModel.
 *
 * @since  2.0.0
 */
class VideosModel extends ListModel {

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
				'catid', 'a.catid',
				'type', 'a.type',
				'mp4', 'a.mp4',
				'mp4_hd', 'a.mp4_hd',
				'webm', 'a.webm',
				'ogv', 'a.ogv',
				'youtube', 'a.youtube',
				'vimeo', 'a.vimeo',
				'hls', 'a.hls',
				'dash', 'a.dash',
				'thirdparty', 'a.thirdparty',
				'image', 'a.image',
				'captions', 'a.captions',
				'duration', 'a.duration',
				'description', 'a.description',	
				'userid', 'a.userid',			
				'access', 'a.access',
				'views', 'a.views',
				'featured', 'a.featured',
				'rating', 'a.rating',
				'preroll', 'a.preroll',
				'postroll', 'a.postroll',				
				'meta_keywords', 'a.meta_keywords',
				'meta_description', 'a.meta_description',
				'state', 'a.state',
				'published_up', 'a.published_up',
				'published_down', 'a.published_down',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'updated_date', 'a.updated_date',
				'import_id', 'a.import_id',
				'import_key', 'a.import_key'
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

		// List state information
		parent::populateState( 'a.id', 'DESC' );

		// Adjust the context to support modal layouts
		if ( $layout = $app->input->get( 'layout' ) ) {
			$this->context .= '.' . $layout;
		}

		$context = $this->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $context );

		$import_id = $this->getUserStateFromRequest( $this->context . '.filter.import_id', 'filter_import_id'  );
		$this->setState( 'filter.import_id', $import_id );
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
		$id .= ':' . $this->getState( 'filter.state' );
		$id .= ':' . $this->getState( 'filter.catid' );
		$id .= ':' . $this->getState( 'filter.featured' );
		$id .= ':' . $this->getState( 'filter.user' );
		$id .= ':' . $this->getState( 'filter.import_id' );
		
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
		$query->from( '`#__yendifvideoshare_videos` AS a' );
		
		// Join over the categories for the category title
		$query->select( "c.title AS category" );
		$query->join( "LEFT", "#__yendifvideoshare_categories AS c ON c.id=a.catid" );

		// Join over the users for the name
		$query->select( "u.name AS user" );
		$query->join( "LEFT", "#__users AS u ON u.id=a.userid" );

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

		// Filter by search in title
		$search = $this->getState( 'filter.search' );

		if ( ! empty( $search ) ) {
			if ( stripos( $search, 'id:' ) === 0 ) {
				$query->where( 'a.id = ' . (int) substr( $search, 3 ) );
			} elseif ( stripos( $search, 'import:' ) === 0 ) {
				$query->where( 'a.import_key = ' . $db->quote( $db->escape( substr( $search, 7 ) ) ) );
			} else {
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
				$query->where( '( a.title LIKE ' . $search . '  OR  a.description LIKE ' . $search . '  OR  a.meta_keywords LIKE ' . $search . '  OR  a.meta_description LIKE ' . $search . ' )' );
			}
		}		

		// Filtering catid
		$filter_catid = $this->state->get( 'filter.catid' );

		if ( is_numeric( $filter_catid ) ) {
			$query->where( 'a.catid = ' . (int) $filter_catid );
		}

		// Filtering featured
		$filter_featured = $this->state->get( 'filter.featured' );

		if ( is_numeric( $filter_featured ) ) {
			$query->where( 'a.featured = ' . (int) $filter_featured );
		}

		// Filtering user
		$filter_user = $this->state->get( 'filter.userid' );

		if ( is_numeric( $filter_user ) ) {
			$query->where( 'a.userid = ' . (int) $filter_user );
		}

		// Filtering import_id
		$filter_import_id = $this->state->get( 'filter.import_id' );

		if ( is_numeric( $filter_import_id ) ) {
			$query->where( 'a.import_id = ' . (int) $filter_import_id );
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

		if ( $orderCol && $orderDirn ) {
			if ( $orderCol === 'a.ordering' || $orderCol === 'a.catid' ) {
				$ordering = [
					$db->quoteName( 'c.title' ) . ' ' . $db->escape( $orderDirn ),
					$db->quoteName( 'a.ordering' ) . ' ' . $db->escape( $orderDirn ),
				];
			} else {
				$ordering = $db->escape( $orderCol ) . ' ' . $db->escape( $orderDirn );
			}

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

	public function getUnApprovedCount() {
		$db = Factory::getDbo();
		$user = Factory::getUser();

		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_videos WHERE state = 0 AND userid != ' . (int) $user->id;
		$db->setQuery( $query );
		$result = $db->loadResult();

		return $result;
	}

}
