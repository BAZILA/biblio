<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Helper;

defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Registry\Registry;

if ( ! class_exists( 'YendifVideoShareFile' ) ) {
	if ( class_exists( '\Joomla\CMS\Filesystem\File' ) ) {
		class_alias( '\Joomla\CMS\Filesystem\File', 'YendifVideoShareFile' );
		class_alias( '\Joomla\CMS\Filesystem\Folder', 'YendifVideoShareFolder' );
	} else {
		class_alias( '\Joomla\Filesystem\File', 'YendifVideoShareFile' );
		class_alias( '\Joomla\Filesystem\Folder', 'YendifVideoShareFolder' );
	}
}

/**
 * Class YendifVideoShareHelper.
 *
 * @since  2.0.0
 */
class YendifVideoShareHelper {	

	/**
	 * Check if a valid video file
	 *
	 * @param   string  $fileTemp  The temporary filename of the file
	 *
	 * @return  bool    true if valid, false if not.
	 * 
	 * @since   2.0.0
	 */
	public static function isVideo( $fileTemp ) {
		$allowed = false;
		$allowed_mime = array( 'video/*' );
		$illegal_mime = array( 'application/x-shockwave-flash', 'application/msword', 'application/excel', 'application/pdf', 'application/powerpoint', 'application/x-zip', 'text/plain', 'text/css', 'text/html', 'text/php', 'text/x-php', 'application/php', 'application/x-php', 'application/x-httpd-php', 'application/x-httpd-php-source' );	
		$type = '';

		if ( function_exists( 'finfo_open' ) ) {			
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$type = finfo_file( $finfo, $fileTemp );				
			finfo_close( $finfo );			
		} elseif ( function_exists( 'mime_content_type' ) ) {			
			$type = mime_content_type( $fileTemp );
		}
		
		if ( strlen( $type ) && ! in_array( $type, $illegal_mime ) ) {		
			list( $m1, $m2 )= explode( '/', $type );
			
			foreach ( $allowed_mime as $k => $v ) {
				list ( $v1, $v2 ) = explode( '/', $v );
				if ( ( $v1 == '*' && $v2 == '*' ) || ( $v1 == $m1 && ( $v2 == $m2 || $v2 == '*' ) ) ) {
					$allowed = true;
					break;
				}
			}			
		}			
	
		if ( function_exists( 'file_get_contents' ) ) {
			$xss_check = file_get_contents( $fileTemp, false, null, 0, 256 );
			$html_tags = array( 'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--' );
			
			foreach ( $html_tags as $tag ) {
				if ( stristr( $xss_check, '<' . $tag . ' ' ) || stristr( $xss_check, '<' . $tag . '>' ) || stristr( $xss_check, '<?php' ) ) {
					$allowed = false;
					break;
				}
			}
		}

		return $allowed;
	}

	/**
	 * Check if a valid WebVTT file
	 *
	 * @param   string  $fileTemp  The temporary filename of the file
	 *
	 * @return  bool    true if valid, false if not.
	 * 
	 * @since   2.0.0
	 */
	public static function isWebVTT( $fileTemp ) {
		$allowed = false;
		$allowed_mime = array( 'text/vtt', 'text/plain' );
		$illegal_mime = array( 'application/x-shockwave-flash', 'application/msword', 'application/excel', 'application/pdf', 'application/powerpoint', 'application/x-zip', 'text/css', 'text/html', 'text/php', 'text/x-php', 'application/php', 'application/x-php', 'application/x-httpd-php', 'application/x-httpd-php-source' );	
		$type = '';

		if ( function_exists( 'finfo_open' ) ) {			
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$type = finfo_file( $finfo, $fileTemp );				
			finfo_close( $finfo );			
		} elseif ( function_exists( 'mime_content_type' ) ) {			
			$type = mime_content_type( $fileTemp );
		}
		
		if ( strlen( $type ) && ! in_array( $type, $illegal_mime ) ) {		
			list( $m1, $m2 )= explode( '/', $type );
			
			foreach ( $allowed_mime as $k => $v ) {
				list ( $v1, $v2 ) = explode( '/', $v );
				if ( ( $v1 == '*' && $v2 == '*' ) || ( $v1 == $m1 && ( $v2 == $m2 || $v2 == '*' ) ) ) {
					$allowed = true;
					break;
				}
			}			
		}			

		return $allowed;
	}

	/**
	 * Gets the file attached to an item
	 *
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 *
	 * @return  string  The file
	 * 
	 * @since   2.0.0
	 */
	public static function getFile( $pk, $table, $field ) {
		$db = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( $field )
			->from( $table )
			->where( 'id = ' . (int) $pk );

		$db->setQuery( $query );

		return $db->loadResult();
	}

	/**
	 * Delete the file attached to an item
	 *
	 * @param   string  $file  Path to the file
	 * 
	 * @since   2.0.0
	 */
	public static function deleteFile( $file ) {
		if ( strpos( $file, 'media/com_yendifvideoshare' ) !== false || strpos( $file, 'media/yendifvideoshare' ) !== false ) {
			$parts = explode( 'media', $file );
			$file = JPATH_ROOT . '/' . 'media' . $parts[1];

			// Delete if the file exists
			if ( is_file( $file ) ) {
				\YendifVideoShareFile::delete( $file );
			}			
		} else {
			return; // We don't want to delete files uploaded outside of our component's folder
		}
		
		// Fallback to the versions prior to 2.0
		if ( strpos( $file, 'media/yendifvideoshare' ) !== false ) {
			$extension = pathinfo( $file, PATHINFO_EXTENSION );
			$fileName  = pathinfo( $file, PATHINFO_FILENAME );

			// Delete if the file (_thumb) exists
			$file_thumb = str_replace( $fileName . '.' . $extension, $fileName . '_thumb.' . $extension, $file );
			if ( is_file( $file_thumb ) ) {
				\YendifVideoShareFile::delete( $file_thumb );
			}

			// Delete if the file (_poster) exists
			$file_poster = str_replace( $fileName . '.' . $extension, $fileName . '_poster.' . $extension, $file );
			if ( is_file( $file_poster ) ) {
				\YendifVideoShareFile::delete( $file_poster );
			}
		}

		// Delete the parent directory if empty
		$directory = pathinfo( $file, PATHINFO_DIRNAME );
		if ( is_dir( $directory ) ) {
			$files = array_diff( scandir( $directory ), array( '.', '..' ) );
			if ( empty( $files ) ) {
				\YendifVideoShareFolder::delete( $directory );
			}
		}
	}

	/**
	 * Get image URL from YouTube/Vimeo embedcode
	 *
	 * @param   string  $embedcode  YouTube/Vimeo embedcode
	 *
	 * @return  string  The image URL
	 * 
	 * @since   2.0.0
	 */
	public static function getVideoImageFromEmbedCode( $embedcode ) {
		$image = '';

		$document = new \DOMDocument();
		@$document->loadHTML( $embedcode );	

		$iframes = $document->getElementsByTagName( 'iframe' ); 
		if ( $iframes->length > 0 ) {
			if ( $iframes->item(0)->hasAttribute( 'src' ) ) {
				$src = $iframes->item(0)->getAttribute( 'src' );

				// YouTube
				if ( false !== strpos( $src, 'youtube.com' ) || false !== strpos( $src, 'youtu.be' ) ) {
					$image = self::getYouTubeVideoImage( $src );
				}
				
				// Vimeo
				elseif ( false !== strpos( $src, 'vimeo.com' ) ) {
					$image = self::getVimeoVideoImage( $src );
				}
			}
		}

		return $image;
	}

	/**
	 * Get image URL from YouTube video URL
	 *
	 * @param   string  $url  YouTube video URL
	 *
	 * @return  string  The image URL
	 * 
	 * @since   2.0.0
	 */
	public static function getYouTubeVideoImage( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$videoId = self::getYouTubeVideoId( $url );
		
		$thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";	
		$hasHighResThumbnail = false;	
			
		// Check if the high quality thumbnail image is available
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init( $thumbnailUrl );
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_exec( $ch );
			
			$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );
	
			if ( $httpCode === 200 ) {
				$hasHighResThumbnail = true;
			}
		} else {		
			if ( ini_get( 'allow_url_fopen' ) ) {
				$headers = @get_headers( $thumbnailUrl );
				if ( $headers && strpos( $headers[0], '200' ) !== false ) {
					$hasHighResThumbnail = true;
				}
			}
		}
		
		// Fallback to the medium quality thumbnail image url
		if ( ! $hasHighResThumbnail ) {
			$thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";	
		}

		return $thumbnailUrl;
	}

	/**
	 * Get YouTube video ID from URL
	 *
	 * @param   string  $url  YouTube video URL
	 *
	 * @return  string  The video ID
	 * 
	 * @since   2.0.0
	 */
	public static function getYouTubeVideoId( $url ) {  
		if ( empty( $url ) ) {
			return '';
		}

		$videoId = false; 	
    	$url = parse_url( $url );		
		
    	if ( strcasecmp( $url['host'], 'youtu.be' ) === 0 ) {
        	$videoId = substr( $url['path'], 1 );
    	} elseif ( strcasecmp( $url['host'], 'youtube.com' ) === 0 || strcasecmp( $url['host'], 'www.youtube.com' ) === 0 ) {
        	if ( isset( $url['query'] ) ) {
           		parse_str( $url['query'], $url['query'] );
            	if  ( isset( $url['query']['v'] ) ) {
               		$videoId = $url['query']['v'];
            	}
        	}
			
        	if ( $videoId == false ) {
            	$url['path'] = explode( '/', substr( $url['path'], 1 ) );
            	if ( in_array( $url['path'][0], array( 'e', 'embed', 'v', 'shorts', 'live' ) ) ) {
                	$videoId = $url['path'][1];
            	}
        	}
    	}
		
    	return $videoId;
	}

	/**
	 * Get image URL from Vimeo video URL
	 *
	 * @param   string  $url  Vimeo video URL
	 *
	 * @return  string  The image URL
	 * 
	 * @since   2.0.0
	 */
	public static function getVimeoVideoImage( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$image   = '';
		$videoId = ''; 
		$updated = 0;

		// Get image using the standard OEmbed API
		if ( function_exists( 'file_get_contents' ) ) {
			$oembed = json_decode( file_get_contents( "https://vimeo.com/api/oembed.json?url={$url}" ) );

			if ( $oembed ) {
				if ( isset( $oembed->video_id ) ) {
					$videoId = $oembed->video_id;
				}

				if ( isset( $oembed->thumbnail_url ) ) {
					$image   = $oembed->thumbnail_url; 
					$updated = 1;     
				}
			}
		}

		// Fallback to our old method to get the Vimeo ID
		if ( empty( $videoId ) ) {			
			$isVimeo = preg_match( '/vimeo\.com/i', $url );  

			if ( $isVimeo ) {
				$videoId = preg_replace( '/[^\/]+[^0-9]|(\/)/', '', rtrim( $url, '/' ) );
			}
		}

		// Find large thumbnail using the Vimeo API v2
		if ( ! empty( $videoId ) ) {
			if ( function_exists( 'file_get_contents' ) ) {
				$response = unserialize( file_get_contents( "https://vimeo.com/api/v2/video/{$videoId}.php" ) );

				if ( is_array( $response ) && isset( $response[0]['thumbnail_large'] ) ) {
					$image = $response[0]['thumbnail_large'];
				}
			} elseif ( function_exists( 'curl_init' ) ) { 
				$url = "https://vimeo.com/api/v2/video/{$videoId}.json";

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_URL, $url );
				$response = curl_exec( $ch );
				curl_close( $ch );

				if ( $response ) {
					$json = json_decode( $response );

					if ( is_array( $json ) && isset( $json[0]->thumbnail_large ) ) {
						$image = $json[0]->thumbnail_large;
					}
				}				
			}
		}

		// Get image from private videos
		if ( ! empty( $videoId ) && empty( $image ) ) {
			if ( function_exists( 'curl_init' ) ) {
				$params = ComponentHelper::getParams( 'com_yendifvideoshare' );

				if ( $token = $params->get( 'vimeo_authorization_token' ) ) {
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, "https://api.vimeo.com/videos/{$videoId}/pictures" );
					
					$authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
					curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' , $authorization )); // Inject the token into the header
			
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
					$response = curl_exec( $ch );
					curl_close( $ch );
			
					if ( $response ) {
						$json = json_decode( $response );
			
						if ( $json && isset( $json->data ) ) {               
							$canBreak = false;
			
							foreach ( $json->data as $item ) {
								foreach ( $item->sizes as $picture ) {
									$image = $picture->link;
									$updated = 1;
			
									if ( $picture->width >= 400 ) {
										$canBreak = true;
										break;
									}
								}
			
								if ( $canBreak ) break;
							}
						}
					}
				}
			}
		}

		if ( $updated ) {
			if ( strpos( $image, '?' ) !== false ) {
				$image .= '&new=1';
			} else {
				$image .= '?new=1';
			}
		}
	
		return $image;
	}

	public static function canDo() {
		$db = Factory::getDbo();

		$query = 'SELECT id FROM #__yendifvideoshare_options WHERE name=' . $db->quote( 'access' );
		$db->setQuery( $query );
        $count = $db->loadResult();

		return ( $count > 0 ) ? true : false;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	public static function getActions() {
		$user   = Factory::getUser();
		$result = new \stdClass();

		$assetName = 'com_yendifvideoshare';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ( $actions as $action )	{
			$result->{$action} = $user->authorise( $action, $assetName );
		}

		return $result;
	}
	
	/**
	 * Resolve the component params
	 *
	 * @param   object  $params  The component params
	 * 
	 * @return  object  The component params
	 * 
	 * @since   2.0.0
	 */
	public static function resolveParams( $params ) {
		$global = ComponentHelper::getParams( 'com_yendifvideoshare' );

		// An ugly fallback to old versions
		$options = array(
			'show_excerpt',
			'show_videos_count',
			'enable_popup',
			'show_views',
			'show_rating',
			'show_likes',
			'show_feed',
			'autoplay',
			'loop',
			'controlbar',
			'playbtn',
			'playpause',
			'currenttime',
			'progress',
			'duration',
			'volumebtn',
			'fullscreen',
			'embed',
			'share',
			'download'
		);

		foreach ( $options as $option ) {
			if ( $params->get( $option ) == 'global' ) {
				$params->set( $option, '' );
			}
		}

		// Merge with global params
		$params = new Registry( json_decode( $params ) );
		$temp   = clone $global;

		$temp->merge( $params );
		$params = $temp;
		
		// Premium
		$canDo = self::canDo();
		if ( ! $canDo ) {
			$params->set( 'embed', 0 );
			$params->set( 'share', 0 );
			$params->set( 'download', 0 );
			$params->set( 'enable_popup', 0 );
			$params->set( 'show_rating', 0 );
			$params->set( 'show_likes', 0 );
		}

		return $params;				 
	}

	/**
	 * Gets the image URL attached to an item
	 *
	 * @param   object  $item  The item
	 *
	 * @return  string  The image file URL
	 * 
	 * @since   2.0.0
	 */
	public static function getImage( $item ) {
		$params = ComponentHelper::getParams( 'com_yendifvideoshare' );

		if ( $item ) {
			// Update old image URLs on the Vimeo videos
			if ( isset( $item->type ) && $item->type == 'vimeo' ) {
				$update = 0;

				if ( empty( $item->image ) ) {
					$update = 1;
				} else {
					if ( strpos( $item->image, 'vimeocdn.com' ) !== false ) {
						$query = parse_url( $item->image, PHP_URL_QUERY );
						parse_str( $query, $parsed );

						if ( ! isset( $parsed['new'] ) ) {
							$update = 1;
						}
					}
				}

				if ( $update ) {
					$image = self::getVimeoVideoImage( $item->vimeo );

					if ( ! empty( $image ) ) {
						$db = Factory::getDbo();

						$query = 'UPDATE #__yendifvideoshare_videos SET image=' . $db->quote( $image ) . ' WHERE id=' . (int) $item->id;
						$db->setQuery( $query );
						$db->execute();

						$item->image = $image;
					}
				}
			}

			if ( ! empty( $item->image ) )	{
				return self::makeUrlAbsolute( $item->image );
			}

			// Default Image
			if ( $default_image = $params->get( 'default_image' ) ) {
				$host = parse_url( $default_image, PHP_URL_HOST );
				if ( empty( $host ) ) {
					$default_image = URI::root() . $default_image;
				}

				return $default_image;
			}			
		}

		return Uri::root() . 'media/com_yendifvideoshare/images/placeholder.jpg';				 
	}

	public static function makeUrlAbsolute( $url ) {
		if ( empty( $url ) ) {
			return $url;
		}

		// Trim any unnecessary whitespaces
		$url = trim( $url );

		// If there's no host, it's likely a relative URL
		$host = parse_url( $url, PHP_URL_HOST );

		if ( empty( $host ) ) {
			// Prepend the site URL to make it absolute
			if ( strpos( $url, 'media/com_yendifvideoshare/' ) !== false || strpos( $url, 'media/yendifvideoshare/' ) !== false ) {
				$parsed = explode( 'media', $url );
				$url = URI::root() . 'media' . $parsed[1];
			} else {
				$url = URI::root() . ltrim( $url, "/" );
			}
		}

		return $url;
	}

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 * 
	 * @since   2.0.0
	 */
	public static function canUserEdit( $item ) {
		$permission = false;
		$user       = Factory::getUser();

		if ( $user->authorise( 'core.edit', 'com_yendifvideoshare' ) ) {
			$permission = true;
		} else {
			if ( isset( $item->created_by ) ) {
				if ( $user->authorise( 'core.edit.own', 'com_yendifvideoshare' ) && $item->created_by == $user->id ) {
					$permission = true;
				}
			} else {
				$permission = true;
			}
		}

		return $permission;
	}

	public static function getVideosCount( $id, $params = null ) {			
		$db = Factory::getDbo();	
		
		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_videos WHERE state=1';

		if ( $params && $params->get( 'schedule_video_publishing' ) ) {
			$nullDate = $db->getNullDate();
			$nowDate  = Factory::getDate()->toSql();

			$query .= ' AND (published_up IS NULL OR published_up = ' . $db->quote( $nullDate ) . ' OR published_up <= ' . $db->quote( $nowDate ) . ')';
			$query .= ' AND (published_down IS NULL OR published_down = ' . $db->quote( $nullDate ) . ' OR published_down >= ' . $db->quote( $nowDate ) . ')';
		}			

		$catids = self::getCategoryChildren( $id );
		$query .= ' AND catid IN (' . implode( ',', $catids ) . ')';

		$db->setQuery( $query );
		$count = $db->loadResult();			
		
		return $count;		
	}

	public static function getCategory( $catid, $columns = array( '*' ) ) {
		if ( empty( $catid ) ) {
			return null;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( implode( ',', $columns ) )
			->from( $db->quoteName( '#__yendifvideoshare_categories' ) )
			->where( $db->quoteName( 'id' ) . ' = ' . (int) $catid )
			->where( $db->quoteName( 'state' ) . ' = 1' );

		$db->setQuery( $query );
        $item = $db->loadObject();

		return $item;
	}	

	public static function getCategoryChildren( $parent ) {			
		$db = Factory::getDbo();	

		$ids = array( $parent );
		$array = $ids;

		while ( count( $array ) ) {
			$query = sprintf(
				'SELECT id FROM #__yendifvideoshare_categories WHERE state=1 AND parent IN (%s)',
				implode( ',', $array )
			);

			$db->setQuery( $query );
			$array = $db->loadColumn();			

			if ( ! empty( $array ) ) {
				$ids = array_merge( $ids, $array );
			}			
		}

		$ids = array_map( 'intval', $ids );
		$ids = array_unique( $ids );

		return $ids;
	}

	public static function insertArrayAfter( $key, $array, $new_array ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					foreach ( $new_array as $new_key => $new_value ) {
						$new[ $new_key ] = $new_value;
					}
				}
			}
			return $new;
		}
			
		return $array;  
	}

	public static function Truncate( $text, $length = 150 ) {
		if ( empty( $length ) )	{
			return $text;
		}
			
		$text = strip_tags( $text );
		
    	if ( $length > 0 && strlen( $text ) > $length ) {
        	$tmp = substr( $text, 0, $length );
            $tmp = substr( $tmp, 0, strrpos( $tmp, ' ' ) );

            if ( strlen( $tmp ) >= $length - 3 ) {
            	$tmp = substr( $tmp, 0, strrpos( $tmp, ' ' ) );
            }
 
            $text = $tmp . '...';
        }
 
        return $text;		
	}

	public static function getCSSClassNames( $params, $context = 'grid' ) {
		$class = '';

		// Grid
		if ( $context == 'grid' ) {
			$column_no = (int) $params->get( 'no_of_cols', 3 );
			
			$class = 'yendif-col yendif-col-' . $column_no;
			if ( $column_no > 3 ) $class .= ' yendif-col-sm-3';
			if ( $column_no > 2 ) $class .= ' yendif-col-xs-2';
		}

		// Grid: Related
		if ( $context == 'grid.related' ) {
			$column_no = (int) $params->get( 'related_no_of_cols', 0 );
			if ( empty( $column_no ) ) {
				$column_no = (int) $params->get( 'no_of_cols', 3 );
			}
			
			$class = 'yendif-col yendif-col-' . $column_no;
			if ( $column_no > 3 ) $class .= ' yendif-col-sm-3';
			if ( $column_no > 2 ) $class .= ' yendif-col-xs-2';
		}

		// Popup
		if ( $context == 'popup' ) {
			if ( $params->get( 'enable_popup' ) ) {
				$class = ' yendif-video-share-popup';
			}
		}

		return $class;
	}

	public static function isSEF() {
		$route = Route::_( 'index.php?option=com_yendifvideoshare' );

		if ( strpos( $route, 'index.php?option=com_yendifvideoshare' ) !== false ) {
			return false;
		}

		return true;
	}
	
}
