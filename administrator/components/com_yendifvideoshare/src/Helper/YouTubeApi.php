<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Helper;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

/**
 * Class YouTubeApi.
 *
 * @since  2.0.0
 */
class YouTubeApi {

	/**
     * @var  string  The YouTube API Key
	 * 
	 * @since  4.0.0
     */
	protected $apiKey;

	/**
     * @var  array  The YouTube API URLs
	 * 
	 * @since  2.0.0
     */
    protected $apiUrls = array(       
		'playlistItems.list' => 'https://www.googleapis.com/youtube/v3/playlistItems',
		'channels.list'      => 'https://www.googleapis.com/youtube/v3/channels',
		'search.list'        => 'https://www.googleapis.com/youtube/v3/search',
		'videos.list'        => 'https://www.googleapis.com/youtube/v3/videos'
	);

	/**
	 * Get videos
	 * 
     * @param   array  $params  Array of query params
	 * 
     * @return  mixed
	 * 
	 * @since   4.0.0
     */
    public function query( $params = array() ) {
		if ( empty( $params['apiKey'] ) ) {
			return $this->getError( Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_INVALID_YOUTUBE_API_KEY' ) );
		}

		if ( empty( $params['src'] ) ) {
			return $this->getError( Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_INVALID_SOURCE' ) );
		}

		$this->apiKey = $params['apiKey'];

		$response = new \stdClass();
		
		$response->params = $this->safeMergeParams(
			array(
				'playlistId'     => '',				
				'pageToken'      => '',
				'publishedAfter' => ''
			),
			$params
		);

		// Search
		if ( 'search' == $params['type'] ) {	
			// Get video ids, page token using the search API	
			$params['q'] = $params['src'];
			$data = $this->requestApiSearch( $params );

			if ( ! isset( $data->error ) ) {
				$response->params['pageToken'] = $data->pageToken;

				// Get videos using the videos API
				if ( ! empty( $data->videos ) ) {
					$params['id'] = $data->videos;
					$data = $this->requestApiVideos( $params );

					if ( ! isset( $data->error ) ) {
						$response->videos = $data->videos;

						if ( 'date' == $params['order'] && empty( $params['pageToken'] ) ) {
							$dates = array();

							foreach ( $data->videos as $video ) {
								$dates[] = $video->date;
							}

							rsort( $dates );

							$response->params['publishedAfter'] = $dates[0];
						}
					} else {
						$response = $data;
					}
				} else {
					$response->videos = $data->videos;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Playlist
		if ( 'playlist' == $params['type'] || ! empty( $params['playlistId'] ) ) {
			// Get video ids, page token using the playlistItems API	
			if ( 'playlist' == $params['type'] ) {
				$params['playlistId'] = $this->parseYouTubeIdFromUrl( $params['src'], 'playlist' );
			}			
			$data = $this->requestApiPlaylistItems( $params );

			if ( ! isset( $data->error ) ) {
				$response->params['pageToken'] = $data->pageToken;

				// Get videos using the videos API
				$params['id'] = $data->videos;
				$data = $this->requestApiVideos( $params );

				if ( ! isset( $data->error ) ) {
					$response->videos = $data->videos;
				} else {
					$response = $data;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Channel / Username
		if ( 'channel' == $params['type'] || 'username' == $params['type'] ) {
			// Find playlistId using the channels API
			if ( 'channel' == $params['type'] ) {
				$params['id'] = $this->parseYouTubeIdFromUrl( $params['src'], 'channel' );
			} else {
				$params['forUsername'] = $this->parseYouTubeIdFromUrl( $params['src'], 'username' );
			}
			$data = $this->requestApiChannels( $params );

			// Get videos using the playlistItems API
			if ( ! isset( $data->error ) ) {
				$params['playlistId'] = $data->playlistId;
				$response->params['playlistId'] = $data->playlistId;

				$data = $this->requestApiPlaylistItems( $params );

				if ( ! isset( $data->error ) ) {
					$response->params['pageToken'] = $data->pageToken;
	
					// Get videos using the videos API
					$params['id'] = $data->videos;
					$data = $this->requestApiVideos( $params );
	
					if ( ! isset( $data->error ) ) {
						$response->videos = $data->videos;
					} else {
						$response = $data;
					}
				} else {
					$response = $data;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Videos
		if ( 'videos' == $params['type'] ) {
			$urls = str_replace( array( "\n", "\n\r", ' ' ), ',', $params['src'] );
			$urls = explode( ',', $urls );
			$urls = array_map( 'trim', $urls );
			$urls = array_filter( $urls );

			$all_ids = array();
			foreach ( $urls as $url ) {
				$all_ids[] = $this->parseYouTubeIdFromUrl( $url, 'video' );
			}
			$total_videos = count( $all_ids );
			$total_pages  = ceil( $total_videos / $params['maxResults'] );

			$current_page = isset( $params['pageToken'] ) ? (int) $params['pageToken'] : 1;
			$current_page = max( $current_page, 1 );
			$current_page = min( $current_page, $total_pages );

			$offset = ( $current_page - 1 ) * $params['maxResults'];
			if ( $offset < 0 ) {
				$offset = 0;
			}

			$current_ids  = array_slice( $all_ids, $offset, $params['maxResults'] );
			$params['id'] = implode( ',', $current_ids );

			$data = $this->requestApiVideos( $params );

			if ( ! isset( $data->error ) ) {
				$response->videos = $data->videos;

				if ( $current_page < $total_pages ) {
					$response->params['pageToken'] = $current_page + 1;
				} else {
					$response->params['pageToken'] = '';
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		return $response;
	}

	/**
	 * Grab the playlist, channel or video ID using the YouTube URL given
	 * 
     * @param   string  $url   YouTube URL
	 * @param   string  $type  Type of the URL (playlist|channel|video)
	 * 
     * @return  mixed
	 * 
	 * @since   4.0.0
     */
    private function parseYouTubeIdFromUrl( $url, $type = 'video' ) {
		$url = trim( $url );
		$id = $url;

		switch ( $type ) {
			case 'playlist':
				if ( preg_match( '/list=(.*)&?\/?/', $url, $matches ) ) {
					$id = $matches[1];
				}
				break;

			case 'channel':
				if ( filter_var( $id, FILTER_VALIDATE_URL ) ) {
					$id = '';
				}

				$url = parse_url( rtrim( $url, '/' ) );

				if ( isset( $url['path'] ) && preg_match( '/^\/channel\/(([^\/])+?)$/', $url['path'], $matches ) ) {
					$id = $matches[1];
				}
				break;

			case 'username':
				$url = parse_url( rtrim( $url, '/' ) );

				if ( isset( $url['path'] ) && preg_match( '/^\/user\/(([^\/])+?)$/', $url['path'], $matches ) ) {
					$id = $matches[1];
				}
				break;
			
			default: // video
				if ( filter_var( $id, FILTER_VALIDATE_URL ) ) {
					$id = '';
				}

				$url = parse_url( $url );
			
				if ( array_key_exists( 'host', $url ) ) {				
					if ( 0 === strcasecmp( $url['host'], 'youtu.be' ) ) {
						$id = substr( $url['path'], 1 );
					} elseif ( 0 === strcasecmp( $url['host'], 'youtube.com' ) || 0 === strcasecmp( $url['host'], 'www.youtube.com' ) ) {
						if ( isset( $url['query'] ) ) {
							parse_str( $url['query'], $url['query'] );

							if ( isset( $url['query']['v'] ) ) {
								$id = $url['query']['v'];
							}
						}
							
						if ( empty( $id ) ) {
							$url['path'] = explode( '/', substr( $url['path'], 1 ) );

							if ( in_array( $url['path'][0], array( 'e', 'embed', 'v', 'shorts', 'live' ) ) ) {
								$id = $url['path'][1];
							}
						}
					}
				}
		}

		return $id;
	}

	/**
	 * Get videos using search API

     * @param   array  $params  Array of query params
	 * 
     * @return  stdClass
	 * 
	 * @since   4.0.0
     */
    private function requestApiSearch( $params = array() ) {
		$api_url = $this->getApiUrl( 'search.list' );				

		$params['q'] = str_replace( '|', '%7C', $params['q'] );
		$params['type'] = 'video'; // Overrides user defined type value 'search'

		$api_params = $this->safeMergeParams(
			array(
				'q'          => '',
				'type'       => 'video',
				'part'       => 'id',
				'order'      => 'date',
				'maxResults' => 50,
				'pageToken'  => ''
			),
			$params
		);
		
		if ( 'date' == $api_params['order'] && empty( $api_params['pageToken'] ) && ! empty( $params['publishedAfter'] ) ) {
			$api_params['publishedAfter'] = $params['publishedAfter'];
		}
		
		$data = $this->requestApi( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		// Process result
		$response = new \stdClass();	
		$response->videos = array();
		$response->pageToken = '';	

		if ( count( $data->items ) > 0 ) {
			$videos = array();
			foreach ( $data->items as $item ) {
				if ( isset( $item->id ) && isset( $item->id->videoId ) ) {
					$videos[] = $item->id->videoId;
				}
			}
			$response->videos = implode( ',', $videos );
			
			if ( ! isset( $api_params['publishedAfter'] ) && isset( $data->nextPageToken ) ) {
				$response->pageToken = $data->nextPageToken;
			}
		}

		return $response;		
	}

	/**
	 * Get videos using playlistItems API
	 * 
     * @param   array  $params  Array of query params
	 * 
     * @return  stdClass
	 * 
	 * @since   4.0.0
     */
    private function requestApiPlaylistItems( $params = array() ) {
		$api_url = $this->getApiUrl( 'playlistItems.list' );		

    	$api_params = $this->safeMergeParams(
			array(
				'playlistId' => '',
				'part'       => 'contentDetails',
				'maxResults' => 50,
				'pageToken'  => ''
			),
			$params
		);
		
		$data = $this->requestApi( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		if ( 0 == count( $data->items ) ) {
			return $this->getError( Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_NO_VIDEOS_FOUND' ) );
		}

		// Process result
		$response = new \stdClass();	

		$videos = array();
		foreach ( $data->items as $item ) {
			if ( isset( $item->contentDetails ) && isset( $item->contentDetails->videoId ) ) {
				$videos[] = $item->contentDetails->videoId;
			}
		}
		$response->videos = implode( ',', $videos );

		$response->pageToken = '';
		if ( isset( $data->nextPageToken ) ) {
			$response->pageToken = $data->nextPageToken;
		}

		return $response;		
	}

	/**
	 * Find playlistId using channels API
	 * 
     * @param   array  $params  Array of query params
	 * 
     * @return  mixed
	 * 
	 * @since   4.0.0
     */
  	private function requestApiChannels( $params = array() ) {
		$key = isset( $params['id'] ) ? $params['id'] : $params['forUsername'];

		$api_url = $this->getApiUrl( 'channels.list' );

		$api_params = $this->safeMergeParams(
			array(
				'id'          => '',
				'forUsername' => '',
				'part'        => 'contentDetails'
			),
			$params
		);

		$data = $this->requestApi( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		if ( 0 == count( $data->items ) ) {
			return $this->getError( Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_NO_VIDEOS_FOUND' ) );
		}

		$playlist_id = $data->items[0]->contentDetails->relatedPlaylists->uploads;

		// Process result
		$response = new \stdClass();
		$response->playlistId = $playlist_id;		

		return $response;
	}	

	/**
	 * Get details of the given video IDs
	 * 
     * @param   array  $params  Array of query params
	 * 
     * @return  stdClass
	 * 
	 * @since   4.0.0
     */
  	private function requestApiVideos( $params = array() ) {
		$api_url = $this->getApiUrl( 'videos.list' );	

		$api_params = $this->safeMergeParams(
			array(
        		'id'   => '',
				'part' => 'id,snippet,contentDetails,status'
			), 
			$params
		);

		$data = $this->requestApi( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		// Process result
		$response = new \stdClass();

		$videos = array();

		if ( count( $data->items ) > 0 ) {
			foreach ( $data->items as $item ) {
				$video = new \stdClass();

				// Video ID
				$video->id = $item->id;

				// Video URL
				$video->url = 'https://www.youtube.com/watch?v=' . $video->id;

				// Video title
				$video->title = $item->snippet->title;

				// Video description
				$video->description = $item->snippet->description;

				// Video image
				$video->image = '';	

				if ( isset( $item->snippet->thumbnails->maxres ) ) {
					$video->image = $item->snippet->thumbnails->maxres->url;
				} elseif ( isset( $item->snippet->thumbnails->medium ) ) {
					$video->image = $item->snippet->thumbnails->medium->url;
				} elseif ( isset( $item->snippet->thumbnails->default ) ) {
					$video->image = $item->snippet->thumbnails->default->url;
				}
				
				// Video duration
				$video->duration = '';

				if ( isset( $item->contentDetails->duration ) ) {
					$time = new \DateTime( '@0' ); // Unix epoch
					$time->add( new \DateInterval( $item->contentDetails->duration ) );
					$duration = $time->format( 'H:i:s' );

					$video->duration = ltrim( $duration, '00:' );
				}

				// Video publish date
				$video->date = $item->snippet->publishedAt;			

				// Push resulting object to the main array
				$status = 'private';
			
				if ( isset( $item->status ) && ( $item->status->privacyStatus == 'public' || $item->status->privacyStatus == 'unlisted' ) ) {
					$status = 'public';				
				}

				if ( isset( $item->snippet->status ) && ( $item->snippet->status->privacyStatus == 'public' || $item->snippet->status->privacyStatus == 'unlisted' ) ) {
					$status = 'public';				
				}

				if ( $item->kind == 'youtube#searchResult' ) {
					$status = 'public';				
				}

				if ( $status == 'public' ) {
					$videos[] = $video;
				}
			}
		}

		$response->videos = $videos;

		return $response;		
	}
	
	/**
     * Get API URL by request
	 *
     * @param   array  $name
	 * 
     * @return  string
	 * 
	 * @since   4.0.0
     */
  	private function getApiUrl( $name ) {
    	return $this->apiUrls[ $name ];
	}	

	/**
     * Request data from the API server
     *
     * @param   string  $url     YouTube API URL
     * @param   array   $params  Array of query params
	 * 
     * @return  mixed  
	 * 
	 * @since   4.0.0   
     */
  	private function requestApi( $url, $params ) {
		$app = Factory::getApplication();

		$data = array();
		$response = NULL;

		$params['key'] = $this->apiKey;
		
		$q = '';
		if ( isset( $params['q'] ) ) {
			$q = $params['q'];
			unset( $params['q'] );
		}

		$apiUrl = $url . '?' . http_build_query( $params ); // Request data from API server
		if ( ! empty( $q ) ) {
			$apiUrl .= '&q=' . $q; 
		}

		if ( function_exists( 'curl_init' ) ) { 
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_URL, $apiUrl );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );

			curl_close( $ch );		
		} elseif ( function_exists( 'file_get_contents' ) ) {
			$response = file_get_contents( $apiUrl );
		}

		if ( $response ) {
			$data = json_decode( $response );

			if ( $data ) {
				return $data;			
			} else {
				return $this->getError( json_last_error_msg() );	
			}
		}

		// Finally return the data
		return $data;
	}

	/**
	 * Combine user params with known params and fill in defaults when needed
	 *
	 * @param   array  $pairs   Entire list of supported params and their defaults
	 * @param   array  $params  User defined params
	 * 
	 * @return  array  $out  Combined and filtered params array
	 * 
	 * @since   4.0.0
	*/
	private function safeMergeParams( $pairs, $params ) {
		$params = (array) $params;
		$out = array();
		
		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $params ) ) {
				$out[ $name ] = $params[ $name ];
			} else {
				$out[ $name ] = $default;
			}

			if ( empty( $out[ $name ] ) ) {
				unset( $out[ $name ] );
			}
		}
		
		return $out;
	}

	/**
	 * Build error object
	 *
	 * @param   string  $message  Error message
	 * 
	 * @return  object  Error object
	 * 
	 * @since   4.0.0
	*/
	private function getError( $message ) {
		$obj = new \stdClass();
		$obj->error = 1;
		$obj->error_message = $message;

		return $obj;
	}
	
}
