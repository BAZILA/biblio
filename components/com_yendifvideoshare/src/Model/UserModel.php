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
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class UserModel.
 *
 * @since  2.0.0
 */
class UserModel extends ListModel {

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
				'updated_date', 'a.updated_date'
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
		parent::populateState( 'a.id', 'DESC' );

		$layout = $app->input->getWord( 'layout' );		

		if ( $layout == 'videos' ) {
			$orderby = $params->get( 'orderby', 'latest' );

			switch ( $orderby ) {
				case 'latest':
					$orderCol  = 'id';
					$orderDirn = 'DESC';
					break;
				case 'date_added':
					$orderCol  = 'created_date';
					$orderDirn = 'DESC';
					break;
				case 'most_viewed':
					$orderCol  = 'views';
					$orderDirn = 'DESC';
					break;
				case 'most_rated':
					$orderCol  = 'rating';
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
		} else {
			$list = $app->getUserState( $this->context . '.list' );

			$ordering  = $this->getUserStateFromRequest( $this->context . '.filter_order', 'filter_order', 'a.id' );
			$direction = strtoupper( $this->getUserStateFromRequest( $this->context . '.filter_order_Dir', 'filter_order_Dir', 'DESC' ) );
			
			if ( ! empty( $ordering ) || ! empty( $direction ) ) {
				$list['fullordering'] = $ordering . ' ' . $direction;
			}

			$app->setUserState( $this->context . '.list', $list );	
		}

		// Filter state information
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

		$query->from( $db->quoteName( '#__yendifvideoshare_videos', 'a' ) );

		// Join over the categories for the category title
		$query->select( "c.title AS category" );
		$query->join( "LEFT", "#__yendifvideoshare_categories AS c ON c.id=a.catid" );

		// Join over the users for the checked out user
		$query->select( 'uc.name AS uEditor' );
		$query->join( 'LEFT', '#__users AS uc ON uc.id = a.checked_out' );

		// Join over the created by field 'created_by'
		$query->join( 'LEFT', '#__users AS created_by ON created_by.id = a.created_by' );

		// Join over the created by field 'modified_by'
		$query->join( 'LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by' );		
		
		// Filtering state
		$query->where( '(a.state IN (0, 1))' );

		// Filtering user
		$query->where( 'a.userid = ' . (int) Factory::getUser()->id );

		// Filter by search in title
		$search = $this->getState( 'filter.search' );

		if ( ! empty( $search ) ) {
			if ( stripos( $search, 'id:' ) === 0 ) {
				$query->where( 'a.id = ' . (int) substr( $search, 3 ) );
			} else {
				$search = $db->quote( '%' . $db->escape( $search, true ) . '%' );
				$query->where( '( a.title LIKE ' . $search . '  OR  a.description LIKE ' . $search . '  OR  a.meta_keywords LIKE ' . $search . '  OR  a.meta_description LIKE ' . $search . ' )' );
			}
		}			
			
		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

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
