<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Helper;

defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Filter\OutputFilter;
use \PluginsWare\Component\YendifVideoShare\Administrator\Helper\YouTubeApi;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class YouTubeImport.
 *
 * @since  2.0.0
 */
class YouTubeImport {

	/**
	 * @var  YouTubeImport  The class instance
	 * 
	 * @since  2.0.0
	 */
	private static $instance;

	/**
	 * Get an instance of this class
	 *
	 * @return  YouTubeImport
	 * 
	 * @since   2.0.0
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new YouTubeImport();
		}

		return self::$instance;
	}

	/**
	 * Import videos
	 * 
   	 * @param  int  $id  Import Id.
	 *
	 * @since  2.0.0
     */
	public function process( $id ) {
		$db = Factory::getDbo();
		$date = Factory::getDate();

		set_time_limit( 1200 );

		$params   = $this->getParams( $id );
		$response = $this->requestApi( $params );

		if ( $response->success ) {
			// Insert videos
			if ( $response->data['imported'] > 0 ) {
				$videos_inserted = 0;
				
				foreach ( $response->videos as $video ) {
					$item = new \stdClass();

					$item->id = NULL;
					$item->title = $video->title;

					if ( Factory::getConfig()->get( 'unicodeslugs' ) == 1 ) {
						$item->alias = OutputFilter::stringURLUnicodeSlug( trim( $video->title ) );
					} else {
						$item->alias = OutputFilter::stringURLSafe( trim( $video->title ) );
					}

					$item->catid = (int) $params['video_catid'];
					$item->type = 'youtube';
					$item->mp4 = '';
					$item->mp4_hd = '';
					$item->webm = '';
					$item->ogv = '';
					$item->youtube = $video->url;
					$item->vimeo = '';
					$item->hls = '';
					$item->dash = '';
					$item->rtmp = '';
					$item->flash = '';
					$item->thirdparty = '';
					$item->image = $video->image;
					$item->captions = '';
					$item->duration = $video->duration;
					$item->description = ! empty( $params['video_description'] ) ? $video->description : '';
					$item->userid = (int) $params['video_userid'];
					$item->access = 1;
					$item->views = 0;
					$item->featured = 0;
					$item->rating = 0;
					$item->preroll = -1;
					$item->postroll = -1;
					$item->meta_keywords = '';
					$item->meta_description = '';
					$item->state = (int) $params['video_state'];
					$item->published_up = NULL;
					$item->published_down = NULL;
					$item->ordering = $this->getNextOrder( $params['video_catid'] );
					$item->created_by = Factory::getUser()->id;
					$item->modified_by = Factory::getUser()->id;

					if ( 'original' == $params['video_date'] ) {
						$item->created_date = $video->date;	
						$item->updated_date = $video->date;	
					} else {
						$item->created_date = $date->toSql();	
						$item->updated_date = $date->toSql();
					}

					$item->import_id = $id;
					$item->import_key = $response->data['key'];

					if ( $result = $db->insertObject( '#__yendifvideoshare_videos', $item ) ) {
						++$videos_inserted;
					}									
				}
				
				// If not all videos are inserted
				if ( $videos_inserted != $response->data['imported'] ) {
					$response->data['imported'] = $videos_inserted;
				}
			}

			// Update Import
			$item = new \stdClass();

			$item->id = $id;
			$item->import_state = $response->import_state;
			$item->params = json_encode( $response->params );

			// History
			$params['history']['last_error'] = $response->last_error;

			if ( $response->import_state == 'scheduled' ) {
				$params['history']['data'][] = $response->data;				
			} else {
				if ( $response->data['imported'] > 0 ) {
					$params['history']['data'][] = $response->data;
				}
			}	

			$item->history = json_encode( $params['history'] );			

			// Schedule
			if ( ! empty( $params['schedule'] ) ) {
				if ( $response->import_state == 'completed' ) {
					$item->next_import_date = NULL;
				} else {
					if ( $response->import_state == 'rescheduled' && ! empty( $response->params['pageToken'] ) ) {
						$params['schedule'] = 5 * 60;
					}

					$item->next_import_date = date( 'Y-m-d H:i:s', strtotime( '+' . (int) $params['schedule'] . ' seconds' ) );
				}
			}	
			
			$db->updateObject( '#__yendifvideoshare_imports', $item, 'id' );
		} else {
			// History
			$item = new \stdClass();

			$item->id = $id;

			$params['history']['last_error'] = $response->last_error;
			$item->history = json_encode( $params['history'] );	

			$db->updateObject( '#__yendifvideoshare_imports', $item, 'id' );			
		}
	}	

	/**
	 * Request Api for videos.
	 * 
   	 * @param   array  $params  Array of query params.

     * @return  mixed
	 * 
	 * @since   2.0.0
     */
	private function requestApi( $params ) {
		// Vars
		$insert_keys = array();
		foreach ( $params['history']['data'] as $data ) {
			$insert_keys[] = $data['key'];
		}

		$exclude = array();
		foreach ( $params['exclude'] as $url ) {
			$exclude[] = YendifVideoShareHelper::getYouTubeVideoId( $url );
		}

		$limit = (int) $params['limit'];
		$per_request = 50;
		$iterations = ceil( $limit / $per_request );		

		$args = array(
			'apiKey'         => $params['api_key'],
			'type'           => $params['type'],
			'src'            => $params['src'],
			'playlistId'     => isset( $params['params']['playlistId'] ) ? $params['params']['playlistId'] : '',
			'pageToken'      => isset( $params['params']['pageToken'] ) ? $params['params']['pageToken'] : '',
			'publishedAfter' => isset( $params['params']['publishedAfter'] ) ? $params['params']['publishedAfter'] : ''
		);

		if ( $args['type'] == 'search' ) {
			$args['order'] = $params['order_by'];
		}

		// Request API
		$response = new \stdClass();
		$response->success = 0;
		$response->videos = array();
		$response->import_state = $params['import_state'];
		$response->params = $params['params'];
		$response->data = array(
			'key'        => '',
			'date'       => date( 'Y-m-d H:i:s' ),
			'imported'   => 0,
			'excluded'   => 0,
			'duplicates' => 0
		);	
		$response->last_error = '';

		for ( $i = 0; $i < $iterations; $i++ ) {
			if ( $i == $iterations - 1 ) { // Last iteration
				$args['maxResults'] = $limit - ( $i * $per_request );
			} else {
				$args['maxResults'] = $per_request;
			}

			$youtube = new YouTubeApi();		
			$data = $youtube->query( $args );

			if ( ! isset( $data->error ) ) {
				$response->success = 1;

				$bypass = false;

				$update_history = 1;
				if ( $params['import_state'] == 'completed' || $params['import_state'] == 'rescheduled' ) {
					$update_history = 0;
				}

				// Set Params				
				$args = array_merge( $args, $data->params );
				$response->params = $data->params;

				// Set Videos
				$videos = array();

				foreach ( $data->videos as $video ) {
					if ( $params['type'] !== 'playlist' ) {
						if ( $params['import_state'] == 'completed' || $params['import_state'] == 'rescheduled' ) {
							if ( in_array( $video->id, $insert_keys ) ) {
								$bypass = true;
								break;
							}
						}
					}

					// Set the first video id as key
					if ( $response->data['key'] == '' ) {
						$response->data['key'] = $video->id;
					}

					// Check in the excluded list
					if ( in_array( $video->id, $exclude ) ) {
						if ( $update_history ) {
							++$response->data['excluded'];
						}

						continue;
					}

					// Check if the video post already exists
					if ( $this->isVideoExists( $video->id, $params ) ) {
						if ( $update_history ) {
							++$response->data['duplicates'];
						}
						
						continue;
					}

					// OK to import
					$datetime = new \DateTime( $video->date );
			 		$video->date = date_format( $datetime, 'Y-m-d H:i:s' );
			 
					$videos[] = $video;

					++$response->data['imported'];
				}				

				if ( ! empty( $videos ) ) {
					$response->videos = array_merge( $response->videos, $videos );					
				} elseif ( $params['type'] !== 'playlist' ) {
					if ( $params['import_state'] == 'completed' || $params['import_state'] == 'rescheduled' ) {
						$bypass = true;				
					}
				}			

				// Bypass the loop
				if ( empty( $args['pageToken'] ) ) {
					$bypass = true;
				}

				if ( $bypass ) {
					$response->params['pageToken'] = '';
					break;
				}
			} else {
				$response->last_error = $data->error_message;
				break;
			}
		}

		// Import status
		if ( $response->success ) {
			if ( empty( $response->import_state ) ) {
				$response->import_state = 'scheduled';
			}

			if ( empty( $response->params['pageToken'] ) ) {
				$response->import_state = 'completed';
			}
			
			if ( $response->import_state == 'completed' || $response->import_state == 'rescheduled' ) {
				$response->import_state = ! empty( $params['reschedule'] ) ? 'rescheduled' : 'completed';
			}
		}

		return $response;
	}

	/**
	 * Get query params.
	 * 
	 * @param   int  $id  Import Id.
	 * 
     * @return  array  Array of query params.
	 * 
	 * @since   2.0.0
     */
	private function getParams( $id ) {		
		$import = $this->getImport( $id );
		$params = ComponentHelper::getParams( 'com_yendifvideoshare' );

		$data = array(
			'api_key'           => $params->get( 'youtube_api_key' ),
			'service'           => 'youtube',
			'type'              => $import->type,			
			'src'               => '',
			'exclude'           => ! empty( $import->exclude ) ? $import->exclude : array(),
			'allow_duplicates'  => 0,
			'order_by'          => $import->order_by,
			'limit'             => ! empty( $import->limit ) ? $import->limit : 50,
			'schedule'          => $import->schedule,
			'reschedule'        => $import->reschedule,
			'import_state'      => $import->import_state,
			'params'            => ! empty( $import->params ) ? json_decode( $import->params, true ) : '',				
			'history'           => ! empty( $import->history ) ? json_decode( $import->history, true ) : '',
			'video_catid'       => $import->video_catid,
			'video_description' => $import->video_description,
			'video_date'        => $import->video_date, 
			'video_userid'      => $import->video_userid,
			'video_state'       => $import->video_state			
		);
		
		// Source
		$type = $data['type'];
		$data['src'] = $import->{$type};

		// Exclude
		if ( ! empty( $data['exclude'] ) ) {
			$exclude = str_replace( array( "\n", "\n\r", ' ' ), ',', $data['exclude'] );
			$exclude = explode( ',', $exclude );
			$data['exclude'] = array_filter( $exclude );
		}

		// Reschedule
		if ( empty( $data['schedule'] ) ) {
			$data['reschedule'] = 0;
		}

		if ( $data['type'] == 'videos' ) {
			$data['reschedule'] = 0;
		}

		if ( $data['type'] == 'search' && $data['order'] != 'date' ) {
			$data['reschedule'] = 0;
		}

		// Limit
		if ( $data['type'] == 'playlist' && $data['import_state'] == 'rescheduled' ) {
			$data['limit'] = max( 250, (int) $data['limit'] );
		}

		// History
		if ( empty( $data['history'] ) ) {
			$data['history'] = array( 
				'last_error' => '',
				'data'       => array()
			);
		}

		return $data;
	}

	/**
	 * Get the import item
	 *
	 * @param   int  $id  The import Id
	 *
	 * @return  object
	 * 
	 * @since   2.0.0
	 */
	private function getImport( $id ) {
		$db = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( '*' )
			->from( $db->quoteName( '#__yendifvideoshare_imports' ) )
			->where( $db->quoteName( 'id' ) . ' = ' . (int) $id )
			->where( $db->quoteName( 'state' ) . ' = 1' );

        $db->setQuery( $query );
        $item = $db->loadObject();

		return $item;
	}

	/**
	 * Check if the video already exists.
	 * 
	 * @param   int    $src     Video Url.
	 * @param   array  $params  Array of query params.
	 * 
     * @return  bool   True if exists, false if not.
	 * 
	 * @since  2.0.0
     */
	private function isVideoExists( $src, $params ) {
		if ( $params['allow_duplicates'] ) {
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( 'COUNT(id)' )
			->from( $db->quoteName( '#__yendifvideoshare_videos' ) )
			->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'youtube' ) )			
			->where( $db->quoteName( 'youtube' ) . ' LIKE ' . $db->quote( '%' . $db->escape( $src, true ) . '%' ) )
			->where( $db->quoteName( 'state' ) . ' = 1' );

        $db->setQuery( $query );
        $count = $db->loadResult();

		if ( $count > 0 ) {
			return true;
		}

		return false;
	}
	
	/**
	 * Get the video insert order
	 * 
	 * @param   int  $catid  Video category Id
	 * 
     * @return  int  Insert order
	 * 
	 * @since   2.0.0
     */
	private function getNextOrder( $catid ) {
		$db = Factory::getDbo();

		$db->setQuery( 'SELECT MAX(ordering) FROM #__yendifvideoshare_videos WHERE catid=' . (int) $catid );
		$max = $db->loadResult();

		return $max + 1;
	}

	/**
	 * Update the "Exclude URLs" list
	 *
	 * @param  object  $item  Video data
	 * 
	 * @since  2.0.0
	 */
	public function onVideoDeleted( $item ) {
		if ( $item->type == 'youtube' && ! empty( $item->import_id ) ) {
			$db = Factory::getDbo();

			$db->setQuery( 'SELECT exclude FROM #__yendifvideoshare_imports WHERE id=' . (int) $item->import_id );
			$excluded = $db->loadResult();

			if ( ! empty( $excluded ) ) {
				$excluded = str_replace( array( "\n", "\n\r", ' ' ), ',', $excluded );
				$excluded = explode( ',', $excluded );
				$excluded = array_map( 'trim', $excluded );

				$excluded[] = $item->youtube;

				$excluded = array_filter( $excluded );
				$excluded = array_unique( $excluded );

				$excluded = implode( "\n", $excluded );
			} else {
				$excluded = $item->youtube;
			}

			$query = 'UPDATE #__yendifvideoshare_imports SET exclude=' . $db->quote( $excluded ) . ' WHERE id=' . (int) $item->import_id;
			$db->setQuery( $query );
			$db->execute();
		}
	}

}
