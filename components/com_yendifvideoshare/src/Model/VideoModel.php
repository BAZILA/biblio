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
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\MVC\Model\ListModel;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class VideoModel.
 *
 * @since  2.0.0
 */
class VideoModel extends ListModel {

	public $__item;	

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
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function populateState( $ordering = null, $direction = null ) {
		$app = Factory::getApplication();

		// Load the parameters
		$params = $app->getParams();
		$this->setState( 'params', $params );		

		// List state information
		$orderby = $params->get( 'related_orderby', '' );
		if ( empty( $orderby ) ) {
			$orderby = $params->get( 'orderby', 'ordering' );
		}

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

		$no_of_rows = (int) $params->get( 'related_no_of_rows', 0 );
		if ( empty( $no_of_rows ) ) {
			$no_of_rows = (int) $params->get( 'no_of_rows', 3 );
		}

		$no_of_cols = (int) $params->get( 'related_no_of_cols', 0 );
		if ( empty( $no_of_cols ) ) {
			$no_of_cols = (int) $params->get( 'no_of_cols', 3 );
		}

		$limit = $no_of_rows * $no_of_cols;

		$this->setState( 'list.limit', $limit );

		$limitstart = $app->input->get( 'limitstart', 0, 'uint' );
		$this->setState( 'list.start', $limitstart );

		// Filter state information
		$id = $app->input->getInt( 'id' );
		$this->setState( 'filter.videoid', $id );

		$catid = 0;
		$related = array();

		$this->__item = $this->getVideo( $id );
		if ( $this->__item ) {
			$catid = $this->__item->catid;

			$related = array();
			if ( ! empty( $this->__item->related ) ) {
				$related = explode( ',', $this->__item->related );
				$related = array_map( 'intval', $related );
				$related = array_filter( $related );

				if ( ( $key = array_search( $this->__item->id, $related ) ) !== false ) {
					unset( $related[ $key ] );
				}
			}
		}

		$this->setState( 'filter.catid', $catid );
		$this->setState( 'filter.related', $related );		
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
			
		// Filter by start and end dates
		$params = $this->state->get( 'params' );

		if ( $params && $params->get( 'schedule_video_publishing' ) ) {
			$nullDate = $db->getNullDate();
			$nowDate  = Factory::getDate()->toSql();

			$query->where( '(a.published_up IS NULL OR a.published_up = ' . $db->quote( $nullDate ) . ' OR a.published_up <= ' . $db->quote( $nowDate ) . ')' );
			$query->where( '(a.published_down IS NULL OR a.published_down = ' . $db->quote( $nullDate ) . ' OR a.published_down >= ' . $db->quote( $nowDate ) . ')' );
		}
		
		// Filter by published state
		$query->where( 'a.state = 1' );

		// Filtering catid, related
		$filter_related = $this->state->get( 'filter.related' );

		if ( ! empty( $filter_related ) ) {
			$query->where( 'id IN (' . implode( ',', $filter_related ) . ')' );			
		} else {
			$filter_catid = $this->state->get( 'filter.catid' );
			$filter_videoid = $this->state->get( 'filter.videoid' );

			if ( $filter_catid > 0 ) {
				$query->where( 'a.catid = ' . (int) $filter_catid );
			}
			
			if ( $filter_videoid > 0 ) {
				$query->where( 'a.id != ' . (int) $filter_videoid );
			}
		}
		
		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

		if ( $orderCol ) {
			if ( $orderCol == 'ordering' && ! empty( $filter_related ) ) {
				$query->order( 'FIELD(id, ' . implode( ',', $filter_related ) . ')' );
			} elseif ( $orderCol == 'RAND' ) {
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

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function getVideo( $id = null ) {
		if ( $this->__item === null ) {
			$db = Factory::getDbo();
			$user = Factory::getUser();							

			$query = $db->getQuery( true );

			$query->select( '*' );
			$query->from( $db->quoteName( '#__yendifvideoshare_videos' ) );

			// Filter by id
			if ( empty( $id ) ) {
				$id = $this->state->get( 'filter.videoid' );
			}

			$query->where( 'id = ' . (int) $id );

			// Filter by start and end dates
			$params = $this->state->get( 'params' );

			if ( $params && $params->get( 'schedule_video_publishing' ) ) {
				$nullDate = $db->getNullDate();
				$nowDate  = Factory::getDate()->toSql();

				$query->where( '(userid = ' . $user->id . ' OR published_up IS NULL OR published_up = ' . $db->quote( $nullDate ) . ' OR published_up <= ' . $db->quote( $nowDate ) . ')' );
				$query->where( '(userid = ' . $user->id . ' OR published_down IS NULL OR published_down = ' . $db->quote( $nullDate ) . ' OR published_down >= ' . $db->quote( $nowDate ) . ')' );
			}

			// Filter by published state
			$query->where( '(userid = ' . $user->id . ' OR state = 1)' );

        	$db->setQuery( $query );
			$this->__item = $db->loadObject();
		}	
		
		if ( ! empty( $this->__item ) ) {
			$this->__item->category = YendifVideoShareHelper::getCategory( $this->__item->catid );
		}

		return $this->__item;
	}
	
}
