<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Controller;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use \Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHtml;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;

/**
 * VideoController class.
 *
 * @since  2.0.0
 */
class VideoController extends BaseController {
	
	public function cookie() {
        $app = Factory::getApplication();

        // Set the cookie
        $time = time() + 604800; // 1 week
        $app->input->cookie->set( 'com_yendifvideoshare_gdpr', 1, $time, $app->get( 'cookie_path', '/' ), $app->get( 'cookie_domain' ), $app->isSSLConnection() );
        
        ob_start();
		echo 'success';
		echo ob_get_clean();
        exit;
    }

	public function views() {
		$app = Factory::getApplication();

        $id = $app->input->getInt( 'id' );
        YendifVideoSharePlayer::updateViews( $id );

		ob_start();
		echo 'success';
		echo ob_get_clean();
        exit;
    }
	
    public function rating() {	
		$app     = Factory::getApplication();		
		$db      = Factory::getDbo();
		$user    = Factory::getUser();
		$session = Factory::getSession();

		$params    = ComponentHelper::getParams( 'com_yendifvideoshare' );			
		$videoId   = $app->input->getInt( 'id' );	
		$rating    = $app->input->getFloat( 'rating' );
		$userId    = $user->get( 'id' );
		$sessionId = $session->getId();
		
        $query = 'SELECT COUNT(id) FROM #__yendifvideoshare_ratings WHERE videoid=' . $videoId;
		if ( $params->get( 'allow_guest_rating' ) ) {
			$query .= ' AND sessionid=' . $db->quote( $sessionId );
		} else {
			$query .= ' AND userid=' . (int) $userId;
		}

        $db->setQuery( $query );
        $count = $db->loadResult();
		
		if ( $count ) {
			$query  = 'UPDATE #__yendifvideoshare_ratings SET rating=' . $rating . ' WHERE videoid=' . $videoId;
			if ( $params->get( 'allow_guest_rating' ) ) {
				$query .= ' AND sessionid=' . $db->quote( $sessionId );
			} else {
				$query .= ' AND userid=' . (int) $userId;
			}

			$db->setQuery( $query );
			$db->execute();			
		} else {
			$row = new \stdClass();
   			$row->id = NULL;
			$row->videoid = $videoId;
			$row->rating = $rating;
			$row->sessionid = $sessionId;
			$row->userid = $userId;		

   			$db->insertObject( '#__yendifvideoshare_ratings', $row );
		}	
		
		$query = 'SELECT SUM(rating) as total_ratings, COUNT(id) as total_users FROM #__yendifvideoshare_ratings WHERE videoid=' . $videoId;
		$db->setQuery( $query );
		$item = $db->loadObject();

		$rating = ( $item->total_ratings / ( $item->total_users * 5 ) ) * 100;
					
		$query = 'UPDATE #__yendifvideoshare_videos SET rating=' . $rating . ' WHERE id=' . $videoId;
		$db->setQuery( $query );
		$db->execute();	

		ob_start();
		echo YendifVideoShareHtml::RatingWidget( $videoId, $params );
		echo ob_get_clean();
		exit();
	}

	public function vote() {	
		$app     = Factory::getApplication();		
		$db      = Factory::getDbo();
		$user    = Factory::getUser();
		$session = Factory::getSession();

		$params    = ComponentHelper::getParams( 'com_yendifvideoshare' );
		$videoId   = $app->input->getInt( 'id' );	
		$like      = $app->input->getInt( 'like' );
		$dislike   = $app->input->getInt( 'dislike' );			
		$userId    = $user->get( 'id' );
		$sessionId = $session->getId();

		$query  = 'SELECT COUNT(id) FROM #__yendifvideoshare_likes_dislikes WHERE videoid=' . $videoId;
		if ( $params->get( 'allow_guest_like' ) ) {
			$query .= ' AND sessionid=' . $db->quote( $sessionId );
		} else {
			$query .= ' AND userid=' . $db->quote( $userId );
		}

		$db->setQuery( $query );			
		$count = $db->loadResult();				
		
		if ( $count ) {
			$query = 'UPDATE #__yendifvideoshare_likes_dislikes SET likes=' . $like . ', dislikes=' . $dislike . ' WHERE videoid=' . $videoId;
			if ( $params->get( 'allow_guest_like' ) ) {
				$query .= ' AND sessionid=' . $db->quote( $sessionId );
			} else {
				$query .= ' AND userid=' . $db->quote( $userId );
			}

			$db->setQuery( $query );
			$db->execute();
		} else {	
			$row = new \stdClass();	
			$row->id = NULL;
			$row->videoid = $videoId;
			$row->likes = $like;
			$row->dislikes = $dislike;	
			$row->sessionid = $sessionId;
			$row->userid = $userId;		
			
			$result = $db->insertObject( '#__yendifvideoshare_likes_dislikes', $row );												
		}
		
		ob_start();
		echo YendifVideoShareHtml::LikesDislikesWidget( $videoId, $params );
		echo ob_get_clean();
		exit();					
	}	

	public function download() {
		$app = Factory::getApplication();
		$db  = Factory::getDbo(); 		
		
        $query = "SELECT mp4 FROM #__yendifvideoshare_videos WHERE id=" . $app->input->getInt( 'id' ) . ' AND state=1';
		$db->setQuery( $query );
		$file = $db->loadResult();

		if ( empty( $file ) ) {
			die( Text::_( 'COM_YENDIFVIDEOSHARE_DOWNLOAD_FILE_URL_EMPTY' ) );
           	exit;
        }

		// Vars
		$isRemoteFile  = true;
        $formattedPath = 'url';        	
		$mimeType      = 'video/mp4'; 
		$fileSize      = '';		

		// Removing spaces and replacing with %20 ascii code
        $file = preg_replace( '/\s+/', '%20', trim( $file ) );  
	  	$file = str_replace( '         ', '%20', $file );
	  	$file = str_replace( '        ', '%20', $file );
	  	$file = str_replace( '       ', '%20', $file );
	  	$file = str_replace( '      ', '%20', $file );
	  	$file = str_replace( '     ', '%20', $file );
	  	$file = str_replace( '    ', '%20', $file );
	  	$file = str_replace( '   ', '%20', $file );
	  	$file = str_replace( '  ', '%20', $file );
	  	$file = str_replace( ' ', '%20', $file );

		// Detect the file type	
		if ( strpos( $file, 'media/com_yendifvideoshare/' ) !== false || strpos( $file, 'media/yendifvideoshare/' ) !== false ) {
			$parsed = explode( 'media', $file );
			$file = URI::root() . 'media' . $parsed[1];

			$isRemoteFile = false;
		} else {
			if ( strpos( $file, URI::root() ) !== false ) {
				$isRemoteFile = false;
			}
		}		        		
          
        if ( preg_match( '#http://#', $file ) || preg_match( '#https://#', $file ) ) {
          	$formattedPath = 'url';
        } else {
          	$formattedPath = 'filepath';
        }
        
        if ( $formattedPath == 'url' ) {
          	$fileHeaders = @get_headers( $file );
  
          	if ( $fileHeaders[0] == 'HTTP/1.1 404 Not Found' ) {
           		die( Text::_( 'COM_YENDIFVIDEOSHARE_DOWNLOAD_FILE_NOT_FOUND' ) );
           		exit;
          	}          
        } elseif ( $formattedPath == 'filepath' ) {		
          	if ( ! @is_readable( $file ) ) {
				die( Text::_( 'COM_YENDIFVIDEOSHARE_DOWNLOAD_FILE_NOT_FOUND' ) );
               	exit;
          	}
        }
        
       	// Fetching File Size Located in Remote Server
       	if ( $isRemoteFile || $formattedPath == 'url' ) {         
          	$data = @get_headers( $file, true );
          
          	if ( ! empty( $data['Content-Length'] ) ) {
          		$fileSize = (int) $data[ 'Content-Length' ];          
          	} else {               
               	// If get_headers fails then try to fetch fileSize with curl
               	$ch = @curl_init();

               	if ( ! @curl_setopt( $ch, CURLOPT_URL, $file ) ) {
                 	@curl_close( $ch );
                 	@exit;
               	}
               
               	@curl_setopt( $ch, CURLOPT_NOBODY, true );
               	@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
               	@curl_setopt( $ch, CURLOPT_HEADER, true );
               	@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
               	@curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
               	@curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
               	@curl_exec( $ch );
               
               	if ( ! @curl_errno( $ch ) ) {
                	$httpStatus = (int) @curl_getinfo( $ch, CURLINFO_HTTP_CODE );
                    if ( $httpStatus >= 200 && $httpStatus <= 300 )
                    	$fileSize = (int) @curl_getinfo( $ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
               	}

               	@curl_close( $ch );               
          	}          
		} elseif ( $formattedPath == 'filepath' ) {		   
		    $fileSize = (int) @filesize( $file );			   			   
       	}
          
		// Get the extension of the file
		$path = @parse_url( $file, PHP_URL_PATH ); 
		$ext  = @pathinfo( $path, PATHINFO_EXTENSION );

		switch ( $ext ) {          
			case 'mp3':
				$mimeType = "audio/mpeg";
				break;
			case 'wav':
				$mimeType = "audio/x-wav";
				break;
			case 'au':
				$mimeType = "audio/basic";
				break;
			case 'snd':
				$mimeType = "audio/basic";
				break;
			case 'm3u':
				$mimeType = "audio/x-mpegurl";
				break;
			case 'ra':
				$mimeType = "audio/x-pn-realaudio";
				break;
			case 'mp2':
				$mimeType = "video/mpeg";
				break;
			case 'mov':
				$mimeType = "video/quicktime";
				break;
			case 'qt':
				$mimeType = "video/quicktime";
				break;
			case 'mp4':
				$mimeType = "video/mp4";
				break;
			case 'm4a':
				$mimeType = "audio/mp4";
				break;
			case 'mp4a':
				$mimeType = "audio/mp4";
				break;
			case 'm4p':
				$mimeType = "audio/mp4";
				break;
			case 'm3a':
				$mimeType = "audio/mpeg";
				break;
			case 'm2a':
				$mimeType = "audio/mpeg";
				break;
			case 'mp2a':
				$mimeType = "audio/mpeg";
				break;
			case 'mp2':
				$mimeType = "audio/mpeg";
				break;
			case 'mpga':
				$mimeType = "audio/mpeg";
				break;
			case '3gp':
				$mimeType = "video/3gpp";
				break;
			case '3g2':
				$mimeType = "video/3gpp2";
				break;
			case 'mp4v':
				$mimeType = "video/mp4";
				break;
			case 'mpg4':
				$mimeType = "video/mp4";
				break;
			case 'm2v':
				$mimeType = "video/mpeg";
				break;
			case 'm1v':
				$mimeType = "video/mpeg";
				break;
			case 'mpe':
				$mimeType = "video/mpeg";
				break;
			case 'avi':
				$mimeType = "video/x-msvideo";
				break;
			case 'midi':
				$mimeType = "audio/midi";
				break;
			case 'mid':
				$mimeType = "audio/mid";
				break;
			case 'amr':
				$mimeType = "audio/amr";
				break;            
		
			default:
				$mimeType = "application/octet-stream";
		}

		if ( 'application/octet-stream' == $mimeType ) {
			die( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ) );
			exit;
		}
        
        // Off output buffering to decrease Server usage
        @ob_end_clean();
        
        if ( ini_get( 'zlib.output_compression' ) ) {
        	ini_set( 'zlib.output_compression', 'Off' );
        }
        
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: '. $mimeType );
        header( 'Content-Disposition: attachment; filename=' . (string) @basename( $file ) );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: Wed, 07 May 2013 09:09:09 GMT' );
	    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	    header( 'Cache-Control: post-check=0, pre-check=0', false );
	    header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	    header( 'Pragma: no-cache' );
        header( 'Content-Length: '. $fileSize);        
        
        // Will Download 1 MB in chunkwise
        $chunk = 1 * ( 1024 * 1024 );
		
		if ( $nfile = @fopen( $file, 'rb' ) ) {
			while ( ! feof( $nfile ) ) {                 
				print( @fread( $nfile, $chunk ) );
				@ob_flush();
				@flush();
			}
			@fclose( $nfile );
		}
	}
	
}
