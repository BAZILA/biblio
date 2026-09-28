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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Uri\Uri;

/**
 * Class YendifVideoShareHtml.
 *
 * @since  2.0.0
 */
class YendifVideoShareHtml {	

	public static function RatingWidget( $item, $params ) {	
		$db = Factory::getDbo();	 

		if ( is_numeric( $item ) ) {
			$videoId = $item;

			$query = 'SELECT rating FROM #__yendifvideoshare_videos WHERE id=' . (int) $videoId;
			$db->setQuery( $query );
			$rating = $db->loadResult();
		} else {
			$videoId = $item->id;
			$rating  = $item->rating;
		}

    	$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_ratings WHERE videoid=' . (int) $videoId;
        $db->setQuery( $query );
		$total = $db->loadResult();

		$html  = '<div class="yendif-video-share-ratings">';
		$html .= '<span class="yendif-video-share-ratings-stars">';
		$html .= '<span class="yendif-video-share-ratings-current" style="width:' . (float) $rating . '%;"></span>';
		
		$j = 0.5;
		for ( $i = 0; $i < 10; $i++ ) {
			$j += 0.5;

			$html .= '<span class="yendif-video-share-ratings-star">';
			$html .= '<a href="javascript: void(0);" class="yendif-video-share-ratings-star-' . ( $j * 10 ) . '" title="' . Text::sprintf( 'COM_YENDIFVIDEOSHARE_RATING_TITLE', $j, 5 ) . '" data-id="' . (int) $videoId . '" data-value="' . $j . '">1</a>';
			$html .= '</span>';
		}
		
		$html .= '</span>';
		$html .= '<span class="yendif-video-share-ratings-info">' . Text::sprintf( 'COM_YENDIFVIDEOSHARE_RATING_INFO', ( $rating * 5 ) / 100, $total ) . '</span>';
		$html .= '</div>';
		
		return $html;		
	}

	public static function LikesDislikesWidget( $item, $params ) {			
		$db      = Factory::getDBO();
		$user    = Factory::getUser();
		$session = Factory::getSession();	
				
		$videoId   = is_numeric( $item ) ? $item : $item->id;
		$userId    = $user->get( 'id' );
		$sessionId = $session->getId();	

		// Has Liked/Disliked?
		$query = 'SELECT likes,dislikes FROM #__yendifvideoshare_likes_dislikes WHERE videoid=' . (int) $videoId;
		if ( $params->get( 'allow_guest_like' ) ) {
			$query .= ' AND sessionid=' . $db->quote( $sessionId );
		} else {
			$query .= ' AND userid=' . (int) $userId;
		}
		$db->setQuery( $query );		
		$status = $db->loadObject();

		// Total number of likes
		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_likes_dislikes WHERE videoid=' . (int) $videoId . ' AND likes=1';
		$db->setQuery( $query );		
		$likes_count = $db->loadResult();

		// Total number of dislikes
		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_likes_dislikes WHERE videoid=' . (int) $videoId . ' AND dislikes=1';
		$db->setQuery( $query );		
		$dislikes_count = $db->loadResult();	
	
		// Build HTML output
		$html  = '<div class="yendif-video-share-likes-dislikes">';        
        $html .= '<span class="yendif-video-share-dislike-btn' . ( $status && $status->dislikes > 0 ? ' active' : '' ) . '" data-id="' . (int) $videoId . '">';
        $html .= '<span class="yendif-video-share-dislike-icon"></span>';
        $html .= '<span class="yendif-video-share-like-dislike-separator"></span>';
        $html .= '<span class="yendif-video-share-dislike-count">' . (int) $dislikes_count . '</span>';          
      	$html .= '</span>';
		$html .= '<span class="yendif-video-share-like-btn' . ( $status && $status->likes > 0 ? ' active' : '' ) . '" data-id="' . (int) $videoId . '">';
        $html .= '<span class="yendif-video-share-like-icon"></span>';
        $html .= '<span class="yendif-video-share-like-dislike-separator"></span>';
        $html .= '<span class="yendif-video-share-like-count">' . (int) $likes_count . '</span>';              
        $html .= '</span>';
        $html .= '</div>';
		
		return $html;				
	}

}
