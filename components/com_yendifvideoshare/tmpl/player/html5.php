<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

$app = Factory::getApplication();

// Video Sources
$sources = array();

switch ( $this->item->type ) {
    case 'youtube':
        $sources['youtube'] = array(
            'src'  => $this->item->youtube,
            'type' => 'video/youtube',
        );
        break;
    case 'vimeo':
        $sources['vimeo'] = array(
            'src'  => $this->item->vimeo,
            'type' => 'video/vimeo',
        );
        break;
    case 'rtmp':
		if ( ! empty( $this->item->hls ) ) {
			$sources['hls'] = array(
				'src'  => $this->item->hls,
				'type' => 'application/x-mpegurl',
			);
		}

		if ( ! empty( $this->item->dash ) ) {
			$sources['dash'] = array(
				'src'  => $this->item->dash,
				'type' => 'application/dash+xml',
			);
		}
        break;
    default:
		$this->item->type = 'default';

        // SD
		$ext = pathinfo( $this->item->mp4, PATHINFO_EXTENSION );
        if ( ! in_array( $ext, array( 'webm', 'ogv' ) ) ) {
			$ext = 'mp4';
		}

        $sources['sd'] = array(
            'src'  => YendifVideoShareHelper::makeUrlAbsolute( $this->item->mp4 ),
            'type' => 'video/' . $ext,
        );
        
        // HD
        if ( ! empty( $this->item->mp4_hd ) ) {
			$sources['sd']['label'] = 'SD';

            $sources['hd'] = array(
                'src'   => YendifVideoShareHelper::makeUrlAbsolute( $this->item->mp4_hd ),
                'type'  => 'video/mp4',
                'label' => 'HD'
            );
        }

		// WebM
        if ( ! empty( $this->item->webm ) ) {
            $sources['webm'] = array(
                'src'  => YendifVideoShareHelper::makeUrlAbsolute( $this->item->webm ),
                'type' => 'video/webm',
            );
        }

		// OGV
        if ( ! empty( $this->item->ogv ) ) {
            $sources['ogv'] = array(
                'src'  => YendifVideoShareHelper::makeUrlAbsolute( $this->item->ogv ),
                'type' => 'video/ogv',
            );
        }
} 

// Video Tracks
$tracks = array();

if ( ! empty( $this->item->captions ) ) {
	$tracks[] = YendifVideoShareHelper::makeUrlAbsolute( $this->item->captions );
}

// Video Attributes
$attributes = array(
    'id'            => 'player',
	'class'         => 'video-js vjs-fill',
	'style'         => 'width: 100%; height: 100%;',
	'controls'      => '',
	'playsinline'   => '',
	'controlsList'  => 'nodownload',
	'oncontextmenu' => 'return false;'
);

if ( $this->params->get( 'volume', -1 ) == 0 ) {
	$attributes['muted'] = true;
}

$autoplaylist = ( $app->input->getInt( 'uid', 0 ) > 0 && $this->params->get( 'autoplaylist' ) ) ? 1 : 0;
if ( $autoplaylist == 0 && $this->params->get( 'loop' ) ) {
    $attributes['loop'] = true;
}

$poster = '';
if ( ! empty( $this->item->image ) ) {
	$poster = YendifVideoShareHelper::makeUrlAbsolute( $this->item->image );
    $attributes['poster'] = $poster;
}

// Player Settings
$settings = array(	
	'uid'          => $app->input->getInt( 'uid', 0 ),
	'siteUrl'      => URI::root(),
	'videoId'      => $this->item->id,	
	'videoTitle'   => $this->item->title,	
	'videoExcerpt' => '',
	'ipAddress'    => $this->getIpAddress(),
	'autoAdvance'  => $autoplaylist,
	'loop'         => $this->params->get( 'loop' ) ? 1 : 0,
	'volume'       => (int) $this->params->get( 'volume', -1 ),
	'hotkeys'      => $this->params->get( 'hotkeys' ) ? 1 : 0,	
	'player'       => array(
		'autoplay'                  => $this->params->get( 'autoplay' ) ? true : false,
		'controlBar'                => array(),
		'liveui'                    => true,
		'textTrackSettings'         => false,
		'bigPlayButton'             => $this->params->get( 'playbtn' ) ? true : false,
		'playbackRates'             => array( 0.5, 0.75, 1, 1.5, 2 ),
		'techCanOverridePoster'     => true,
		'suppressNotSupportedError' => true
	),
	'i18n'         => array(
		'streamNotFound' => Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_STREAM_NOT_FOUND' )
	)
);

if ( $this->item->id > 0 ) {
	$excerpt = $this->item->meta_description;

	if ( empty( $excerpt ) && ! empty( $this->item->description ) ) {
		$excerpt = YendifVideoShareHelper::Truncate( $this->item->description );
		$excerpt = str_replace( '...', '', $excerpt );
	}

	$settings['videoExcerpt'] = $excerpt;
}

// Player Settings: Controlbar
$controls = array();

if ( $this->params->get( 'controlbar' ) ) { 
	$controls = array( 
		'playpause'   => 'PlayToggle', 
		'currenttime' => 'CurrentTimeDisplay', 
		'progress'    => 'progressControl', 
		'duration'    => 'durationDisplay',
		'spacer'      => 'CustomControlSpacer',
		'tracks'      => 'SubtitlesButton',
		'speed'       => 'PlaybackRateMenuButton',
		'quality'     => 'qualitySelector',		  
		'volumebtn'   => 'VolumePanel', 
		'fullscreen'  => 'fullscreenToggle'
	);

	foreach ( $controls as $key => $control ) {
		switch ( $key ) {
			case 'spacer':
				$enabled = 1;
				break;

			case 'quality':
				$enabled = ( isset( $sources['hd'] ) || isset( $sources['hls'] ) || isset( $sources['dash'] ) ) ? 1 : 0;
				break;

			case 'tracks':
				$enabled = ! empty( $this->item->captions ) ? 1 : 0;
				break;

			default:
				$enabled = $this->params->get( $key, 0 );
		}

		if ( ! $enabled ) {	
			unset( $controls[ $key ] );	
		}	
	}

	if ( isset( $sources['hls'] ) || isset( $sources['dash'] ) ) {
		if ( isset( $controls['progress'] ) ) {
			$controls = YendifVideoShareHelper::insertArrayAfter( 'progress', $controls, array( 
				'liveui' => 'SeekToLive'
			));
		}
	}

	if ( isset( $controls['currenttime'] ) && isset( $controls['duration'] ) ) {
		if ( ! isset( $controls['progress'] ) ) {
			$controls = YendifVideoShareHelper::insertArrayAfter( 'currenttime', $controls, array( 
				'timedivider' => 'TimeDivider'
			));
		}
	}

	if ( isset( $controls['tracks'] ) ) {
		$controls = YendifVideoShareHelper::insertArrayAfter( 'tracks', $controls, array( 
			'audio' => 'AudioTrackButton'
		));
	}	
}

$settings['player']['controlBar']['children'] = array_values( $controls );

if ( empty( $controls ) || ( 1 == count( $controls ) && isset( $controls['spacer'] ) ) ) {
	$attributes['class'] .= ' vjs-no-control-bar';
}

// Player Settings: YouTube
if ( isset( $sources['youtube'] ) ) {
	$settings['player']['techOrder'] = array( 'youtube' );

	$settings['player']['youtube'] = array( 
		'iv_load_policy' => 3 
	);

	parse_str( $sources['youtube']['src'], $queries );

	if ( isset( $queries['start'] ) ) {
		$settings['start'] = (int) $queries['start'];
	}

	if ( isset( $queries['t'] ) ) {
		$settings['start'] = (int) $queries['t'];
	}

	if ( isset( $queries['end'] ) ) {
		$settings['end'] = (int) $queries['end'];
	}

	// Hide YouTube Logo
	$attributes['class'] .= ' vjs-hide-youtube-logo';
}

// Player Settings: Vimeo
if ( isset( $sources['vimeo'] ) ) {
	$settings['player']['techOrder'] = array( 'vimeo2' );
}

// Player Settings: Ads
if ( $this->hasAds() ) {
	$settings['ads'] = array(
		'adTagUrl'      => $this->params->get( 'vasturl' ),
		'showCountdown' => ( $this->params->get( 'ad_engine' ) == 'vast' && ! $this->params->get( 'show_adverts_timeframe' ) ) ? false : true
	);	
}

// Player Settings: Logo
$settings['license'] = array(	 
    'secretKey'        => trim( $this->params->get( 'license', '' ) ),
    'showLogo'         => $this->params->get( 'displaylogo' ) ? 1 : 0,
    'logoImage'        => $this->params->get( 'logo' ),
	'logoLink'         => $this->params->get( 'logotarget' ),
    'logoPosition'     => $this->params->get( 'logoposition' ),
    'logoOpacity'      => (int) $this->params->get( 'logoalpha' ) / 100,    
	'contextmenuLabel' => $this->canDo ? $this->params->get( 'copyright_text', $app->get( 'sitename' ) ) : 'Powered by Yendif'
);

if ( $this->canDo && $this->item->id > 0 ) {
	// Player Settings: Embed
	if ( $this->params->get( 'embed' ) ) {
		$settings['embed'] = array(
			'code' => '<div style="position:relative;padding-bottom:' . $this->params->get( 'ratio', 56.25 ) . '%;height:0;overflow:hidden;"><iframe src="' . URI::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $this->item->id . '&format=raw" style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
		);
	}
		
	// Player Settings: Share
	if ( $this->params->get( 'share' ) ) {
		$shareUrl   = urlencode( $this->getURL() );
		$shareTitle = urlencode( $this->item->title );
		$shareImage = ! empty( $poster ) ? urlencode( $poster ) : '';

		$settings['share'] = array(
			'facebook'  => "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}",
			'twitter'   => "https://twitter.com/intent/tweet?text={$shareTitle}&amp;url={$shareUrl}",
			'linkedin'  => "https://www.linkedin.com/shareArticle?url={$shareUrl}&amp;title={$shareTitle}",
			'pinterest' => "https://pinterest.com/pin/create/button/?url={$shareUrl}&amp;media={$shareImage}&amp;description={$shareTitle}",
			'tumblr'    => "https://www.tumblr.com/share/link?url={$shareUrl}&amp;name={$shareTitle}"
		);
	}

	// Player Settings: Download
	if ( $this->params->get( 'download' ) && $this->item->type == 'default' ) {
		$settings['download'] = array(
			'url' => Uri::root() . 'index.php?option=com_yendifvideoshare&task=video.download&id=' . $this->item->id
		);
	}
}

// Merge Attributes
$temp = array();

foreach ( $attributes as $key => $value ) {
	if ( '' === $value ) {
		$temp[] = $key;
	} else {
		$temp[] = sprintf( '%s="%s"', $key, $value );
	}
}

$attributes = implode( ' ', $temp );
?>
<!DOCTYPE html>
<html translate="no">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/video-js.min.css?v=8.18.1" />

	<?php if ( isset( $sources['hd'] ) ) : ?>
    	<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-selector/quality-selector.min.css?v=1.3.1" />
	<?php endif; ?>

	<?php if ( isset( $sources['hls'] ) || isset( $sources['dash'] ) ) : ?>
    	<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-quality-menu/videojs-contrib-quality-menu.min.css?v=1.0.3" />
	<?php endif; ?>

    <link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/overlay/videojs-overlay.min.css?v=3.1.0" />

	<?php if ( isset( $settings['ads'] ) ) : ?>
		<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-ads/videojs-contrib-ads.min.css?v=7.5.2" />
		<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/ima/videojs.ima.min.css?v=2.3.0" /> 
	<?php endif; ?>

	<style type="text/css">
        html, 
        body {
            width: 100%;
            height: 100%;
            margin: 0; 
            padding: 0; 
            overflow: hidden;
        }

		/* Common */
		.yendifplayer .video-js {
            width: 100%;
            height: 100%;
        }

		.yendifplayer .video-js a,
        .yendifplayer .video-js a:hover,
        .yendifplayer .video-js a:focus {
            text-decoration: none;
        }

		.yendifplayer .video-js *:not(.vjs-input-embed-code):focus {
			outline: none;
		}

		/* Poster */
		.yendifplayer .video-js .vjs-poster {
			background-color: #000;
		}

		.yendifplayer .video-js.vjs-ended .vjs-poster {
			display: inline-block;
		}
	
		/* Text Track */
		.yendifplayer .video-js:not(.vjs-has-started) .vjs-text-track-display {
			display: none;
		}

		.yendifplayer .video-js.vjs-ended .vjs-text-track-display {
			display: none;
		}

		/* Spinner */
		.yendifplayer .vjs-waiting.vjs-paused .vjs-loading-spinner {
			display: none;
		}

		.yendifplayer.vjs-waiting .vjs-loading-spinner {
			display: flex !important;
		}

		/* Big Play Button */
		.yendifplayer .video-js .vjs-big-play-button {
			transform: translate3d( -50%, -50%, 0 );
			margin: 0;
			border: 0;
			border-radius: 50%;
			background-color: rgba( 0, 0, 0, 0.5 );
			background-image: url( "data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8.56047 5.09337C8.34001 4.9668 8.07015 4.96875 7.85254 5.10019C7.63398 5.23162 7.5 5.47113 7.5 5.73011L7.5 18.2698C7.5 18.5298 7.63398 18.7693 7.85254 18.9007C7.96372 18.9669 8.0882 19 8.21268 19C8.33241 19 8.45309 18.9688 8.56047 18.9075L18.1351 12.6377C18.3603 12.5082 18.5 12.2648 18.5 12C18.5 11.7361 18.3603 11.4917 18.1351 11.3632L8.56047 5.09337Z' fill='%23fff'%3E%3C/path%3E%3C/svg%3E" );
			background-position: center;
			background-size: 75%;
			width: 3em;
			height: 3em;
		}

		.yendifplayer .video-js:hover .vjs-big-play-button,
		.yendifplayer .video-js .vjs-big-play-button:focus {
			background-color: rgba( 0, 0, 0, 0.8 );			
		}		

		.yendifplayer .vjs-waiting.vjs-paused .vjs-big-play-button,
		.yendifplayer .video-js.vjs-ended .vjs-big-play-button {
			display: block;
		}

		.yendifplayer.vjs-waiting .vjs-big-play-button {
			display: none !important;
		}
		
		.yendifplayer .video-js .vjs-big-play-button .vjs-icon-placeholder:before {
			content: "";
		}

		/* Control Bar */
		.yendifplayer .video-js.vjs-no-control-bar .vjs-control-bar {
			display: none;
		}		

		.yendifplayer .video-js.vjs-ended .vjs-control-bar {
			display: none;
		}

		/* Menu */
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu ul {
			background-color: transparent;	
			max-height: 20em;		
		}

		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li,
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li:hover,
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li:focus {
			margin-bottom: 1px;
			border-radius: 2px;			
			background-color: rgba( 0, 0, 0, 0.8 );			
			padding: 0.5em;
			color: #fff;
			text-transform: initial;
		}	

		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li:hover,
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li:focus {
			background-color: #000;
		}

		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li.vjs-selected,
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li.vjs-selected:hover,
		.yendifplayer .video-js .vjs-menu-button-popup .vjs-menu li.vjs-selected:focus {			
			background-color: #fff;	
			color: #000;
		}

		/* Current Time & Duration */
		.yendifplayer .video-js .vjs-current-time {
			display: flex;
		}

		.yendifplayer .video-js:not(.vjs-live) .vjs-time-divider {
			display: flex;
			margin: 0;
			padding-right: 0;
			padding-left: 0;
			min-width: auto;
		}

		.yendifplayer .video-js:not(.vjs-live) .vjs-duration {
			display: flex;
		}

		/* Progress Control */
		.yendifplayer .video-js .vjs-progress-control {
			width: 100%;
			-webkit-tap-highlight-color: rgba( 0, 0, 0, 0 );
		}

		.yendifplayer .video-js .vjs-play-progress:before {
			top: 50%;
			transform: translateY( -50% );
		}		

		.yendifplayer .video-js .vjs-progress-control .vjs-time-tooltip {
			padding: 0.5em;
		}

		.yendifplayer .video-js .vjs-progress-control:hover .vjs-time-tooltip {
			display: flex;
			gap: 0.2em;
			transform: translateX( -50% );
			width: max-content;
		}

		/* Spacer */
		.yendifplayer .video-js .vjs-custom-control-spacer {
			display: flex;
			flex: 1 1 auto;
		}

		/* Playback Rate */
		.yendifplayer .video-js .vjs-playback-rate .vjs-menu {
			left: -3em;
			width: 10em;
		}

		.yendifplayer .video-js .vjs-playback-rate .vjs-playback-rate-value {
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.2em;
		}

		/* Quality Selector (Self Hosted / External URL) */
		.yendifplayer .video-js.vjs-hd .vjs-quality-selector:after {
			pointer-events: none; 
			display: flex;
			position: absolute;
			top: 0.5em;
			right: 0;
			align-items: center;
			justify-content: center;			
			border-radius: 2em;	
			background-color: #f00;
			padding: 0;
			width: 2.2em;
			height: 2.2em;
			text-align: center; 
			letter-spacing: 0.1em;
			line-height: 1;
			color: inherit;								 
			font-size: 0.7em;
			font-weight: 300;   
			content: "";			
		}

		.yendifplayer .video-js.vjs-hd .vjs-quality-selector:after {
			content: "HD";
		}
		
		.yendifplayer .video-js .vjs-quality-selector .vjs-menu .vjs-quality-menu-item-sub-label {			
			position: absolute;
			right: 0;
			width: 4em;
			text-align: center;
			text-transform: none;	
			font-size: 75%;
			font-weight: bold;					
		}

		/* Quality Menu (HLS / MPEG-DASH) */
		.yendifplayer .video-js.vjs-quality-menu .vjs-quality-menu-button-4K-flag:after, 
		.yendifplayer .video-js.vjs-quality-menu .vjs-quality-menu-button-HD-flag:after {
			display: flex;
			align-items: center;
			justify-content: center;
			background-color: #f00;
			line-height: 1;
		}

		/* Volume Panel */
		.yendifplayer .video-js .vjs-slider-horizontal .vjs-volume-level:before {
			top: 50%;
			transform: translateY( -50% );
		}
		
		.yendifplayer .video-js .vjs-slider-horizontal .vjs-volume-tooltip {
			padding: 0.5em;
		}
		
		/* Share & Embed */
		.yendifplayer .video-js .vjs-share {
			display: none;
			margin: 0.5em;
		}
		
		.yendifplayer .video-js.vjs-has-started .vjs-share {
			display: block;			
			transition: visibility 0.1s, opacity 0.1s;
			visibility: visible;
			opacity: 1;
		}

		.yendifplayer .video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-share {			
			transition: visibility 1s, opacity 1s;
			visibility: visible;
			opacity: 0;
		}

		.yendifplayer .video-js .vjs-share button {
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0;
			border: 0;		
			border-radius: 2px;
			box-shadow: none;
			background: rgba( 0, 0, 0, 0.5 );
			cursor: pointer;	
			padding: 0;
			width: 4em;
			height: 4em;
			line-height: 1;
			color: #fff;
		}

		.yendifplayer .video-js .vjs-share:hover button {
			background-color: rgba( 0, 0, 0, 0.8 );
		}

		.yendifplayer .video-js .vjs-share .vjs-icon-share {
			line-height: 1;
			font-size: 1.8em;
		}

		.yendifplayer .video-js .vjs-modal-dialog-share-embed {
            background: #222 !important;
        }

		.yendifplayer .video-js .vjs-modal-dialog-share-embed .vjs-modal-dialog-content {
			line-height: 1;
			font-size: 1em;
		}

		.yendifplayer .video-js .vjs-modal-dialog-share-embed .vjs-close-button {
            margin: 1em;
        }

		.yendifplayer .video-js .vjs-share-embed {
			display: flex !important;
            flex-direction: column; 
            align-items: center;
			justify-content: center;
			gap: 2.5em;
			width: 100%;
			height: 100%;   
        }

		.yendifplayer .video-js .vjs-share-buttons {
            display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: center;
			gap: 0.5em;
        }

		.yendifplayer .video-js .vjs-share-button {
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 2px;
            width: 4em;
			height: 4em;            
			line-height: 1;
			color: #fff;
        }       

        .yendifplayer .video-js .vjs-share-button:hover {
            opacity: 0.9;
        } 

        .yendifplayer .video-js .vjs-share-button-facebook {
            background-color: #3B5996;
        }   
		
		.yendifplayer .video-js .vjs-share-button-twitter {
            background-color: #55ACEE;
        }

        .yendifplayer .video-js .vjs-share-button-linkedin {
            background-color: #006699;
        }

        .yendifplayer .video-js .vjs-share-button-pinterest {
            background-color: #C00117;
        }

        .yendifplayer .video-js .vjs-share-button-tumblr {
            background-color: #28364B;
        } 
		
		.yendifplayer .video-js .vjs-share-button-whatsapp {
            background-color: #25d366;
        }  

        .yendifplayer .video-js .vjs-share-button span {
            line-height: 1;
            font-size: 2em;
        }

        .yendifplayer .video-js .vjs-embed-code {
            display: flex;
			flex-direction: column;
			align-items: center;
			gap: 1em;
			width: 100%;
			max-width: 640px;
        }

        .yendifplayer .video-js .vjs-embed-code label {
			text-transform: uppercase;
			font-size: 11px;
        }

        .yendifplayer .video-js .vjs-embed-code input {
            border: 1px solid #fff;
			border-radius: 2px;
			background: #fff;
			padding: 0.5em;
			width: 100%;            
			line-height: 1;
			color: #000;
        }

        .yendifplayer .video-js .vjs-embed-code input:focus {
            border: 1px solid #fff;
        }

		/* Download Button */
		.yendifplayer .video-js .vjs-download {
			display: none;
			margin: 0.5em;
			cursor: pointer;
		}

		.yendifplayer .video-js .vjs-has-share.vjs-download {
			margin-top: 5em;
		}

		.yendifplayer .video-js.vjs-has-started .vjs-download {
			display: block;			
			transition: visibility 0.1s, opacity 0.1s;
			visibility: visible;
			opacity: 1;
		}

		.yendifplayer .video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-download {
			transition: visibility 1s, opacity 1s;
			visibility: visible;
			opacity: 0;
		}

		.yendifplayer .video-js .vjs-download a {
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0;
			border-radius: 2px;
			background-color: rgba( 0, 0, 0, 0.5 );	
			padding: 0;
			width: 4em;
			height: 4em;
			line-height: 1;
			color: #fff;
		}	
		
		.yendifplayer .video-js .vjs-download:hover a {
			background-color: rgba( 0, 0, 0, 0.8 );
		}

		.yendifplayer .video-js .vjs-download .vjs-icon-file-download {
			line-height: 1;
			font-size: 1.8em;
		}

		/* Custom Logo & Branding */
		.yendifplayer .video-js .vjs-logo a {
			display: inline-block;
			line-height: 1;
		}

		.yendifplayer .video-js .vjs-logo img {
			transition: opacity 0.1s;
			max-width: 100%;
		}

		.yendifplayer .video-js.vjs-has-started .vjs-logo:hover img {
			opacity: 1 !important;
		}

		.yendifplayer .video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-logo img {
			transition: opacity 1s;
			opacity: 0 !important;
		}	

		.yendifplayer .video-js.vjs-ended .vjs-logo {
			display: none;
		}	

		/* Error Display */
		.yendifplayer .video-js .vjs-error-display {
			background: #222 !important;
		}

		/* Tech: YouTube */
		.yendifplayer .vjs-hide-youtube-logo iframe.vjs-tech {
			top: -50% !important;
			height: 200% !important;
		}

		/* Custom ContextMenu */
		.vjs-contextmenu {
            position: absolute;
            top: 0;
            left: 0;
			z-index: 9999999999; /* make sure it shows on fullscreen */
            margin: 0;
			border-radius: 2px;
  			background-color: rgba( 0, 0, 0, 0.5 );
            padding: 0;	
        }
        
       	.vjs-contextmenu-content {
            margin: 0;
			cursor: pointer;
            padding: 6px 10px;
			white-space: nowrap;
			color: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }
    </style>    

    <?php
    if ( ! empty( $this->params->get( 'custom_css' ) ) ) {
        printf( '<style type="text/css">%s</style>', $this->params->get( 'custom_css' ) );
    }
    ?>
</head>
<body id="body" class="yendifplayer vjs-waiting">
    <video-js <?php echo $attributes; ?>>
        <?php 
		// Video Sources
		foreach ( $sources as $source ) {
			printf( 
				'<source type="%s" src="%s" label="%s" />', 
				$source['type'], 
				$source['src'],
				( isset( $source['label'] ) ? $source['label'] : '' ) 
			);
		}
		
		// Video Tracks
		foreach ( $tracks as $track ) {
        	printf( 
				'<track kind="captions" src="%s" label="subtitles on" srclang="en" />', 
				$track
			);
		}
       ?>       
	</video-js>    

	<div id="vjs-share-embed" class="vjs-share-embed" style="display: none;">
		<?php if ( isset( $settings['share'] ) ) : ?>
			<!-- Share Icons -->
			<div class="vjs-share-buttons">
				<?php
				foreach ( $settings['share'] as $service => $url ) {
					printf( 
						'<a href="%1$s" class="vjs-share-button vjs-share-button-%2$s" target="_blank"><span class="vjs-icon-%2$s" aria-hidden="true"></span><span class="vjs-control-text" aria-live="polite">%3$s</span></a>',							
						$url, 
						$service,
						ucfirst( $service )
					);
				}
				?>              
			</div>
		<?php endif; ?>

		<?php if ( isset( $settings['embed'] ) ) : ?>
			<!-- Embed URL -->
			<div class="vjs-embed-code">
				<label for="vjs-input-embed-code"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_EMBED_TITLE' ); ?></label>
				<input type="text" id="vjs-input-embed-code" class="vjs-input-embed-code" value="<?php echo htmlspecialchars( $settings['embed']['code'] ); ?>" readonly />
			</div>
		<?php endif; ?>
    </div>

    <div id="vjs-contextmenu" class="vjs-contextmenu" style="display: none;">
        <div class="vjs-contextmenu-content"><?php echo $settings['license']['contextmenuLabel']; ?></div>
    </div>

    <script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/video.min.js?v=8.18.1" type="text/javascript"></script>

	<?php if ( isset( $sources['hd'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-selector/silvermine-videojs-quality-selector.min.js?v=1.3.1" type="text/javascript"></script>
    <?php endif; ?>

	<?php if ( isset( $sources['hls'] ) || isset( $sources['dash'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-quality-menu/videojs-contrib-quality-menu.min.js?v=1.0.3" type="text/javascript"></script>
    <?php endif; ?>

    <?php if ( isset( $sources['youtube'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/youtube/Youtube.min.js?v=3.1.0" type="text/javascript"></script>
	<?php endif; ?>

	<?php if ( isset( $settings['start'] ) || isset( $settings['end'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/offset/videojs-offset.min.js?v=2.1.3" type="text/javascript"></script>
	<?php endif; ?>

    <?php if ( isset( $sources['vimeo'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/vimeo/Vimeo.min.js?v=3.1.0" type="text/javascript"></script>
	<?php endif; ?>

    <script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/overlay/videojs-overlay.min.js?v=3.1.0" type="text/javascript"></script>

	<?php if ( isset( $settings['ads'] ) ) : ?>
		<script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js" type="text/javascript"></script>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-ads/videojs-contrib-ads.min.js?v=7.5.2" type="text/javascript"></script>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/ima/videojs.ima.min.js?v=2.3.0" type="text/javascript"></script>
	<?php endif; ?>

	<?php if ( ! empty( $settings['hotkeys'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/hotkeys/videojs.hotkeys.min.js?v=0.2.30" type="text/javascript"></script>
	<?php endif; ?>

	<script type="text/javascript">
		'use strict';

		// Vars
		var settings = <?php echo json_encode( $settings ); ?>;
		settings.canDo = canDo();		

		var body = document.getElementById( 'body' );
		var player = null;
		var playButton = null;
		var overlays = [];
		var hasVideoStarted = false;
		var lastEvent = null;

		/**
		 * Called when the big play button in the player is clicked.
		 */
		function onPlayClicked() {
			if ( ! hasVideoStarted ) {
				body.classList.add( 'vjs-waiting' );
			}

			playButton.removeEventListener( 'click', onPlayClicked );
		}		

		/**
		 * Update video views count.
		 */
		function updateViewsCount() {
			var xmlhttp;

			if ( window.XMLHttpRequest ) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
			}
			
			xmlhttp.onreadystatechange = function() {				
				if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 && xmlhttp.responseText ) {					
					/** console.log( xmlhttp.responseText ); */	
				}					
			};	

			xmlhttp.open( 'GET', '<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&task=video.views&id=' + settings.videoId, true );
			xmlhttp.send();							
		}

		/**
		 * Merge attributes.
		 */
		function combineAttributes( attributes ) {
			var str = '';

			for ( var key in attributes ) {
				str += ( key + '="' + attributes[ key ] + '" ' );
			}

			return str;
		}

		function canDo() {
			function a(e){var d=document.createElement("a");d.href=e;return d.hostname.replace("www.","")}function b(e){for(var d=0,f=e.length-1;0<=f;f--)d+=9187263540*e.charCodeAt(f);return(""+d).substring(0,10)}function c(e){for(var d=0,f=0;f<e.length;f++)d+=2465130798*e.charCodeAt(f);return(""+98765243*d).substring(0,10)};
			var d = a( settings.siteUrl );

			if ( settings.license.secretKey == 'Y'+b(d)+'<'+c(d)+'>!' ) {
				return true;
			}

			return false;
		}

		/**
		 * Initialize ads.
		 */
		function initAds() {
			var container   = null;	
			var startEvent  = 'click';		
			var initialized = false;
			var src         = player.src();
			var isIPhone    = videojs.browser.IS_IPHONE;

			if ( isIPhone && ( /youtube.com/.test( src ) || /youtu.be/.test( src ) || /vimeo.com/.test( src ) ) ) {
				return false;
			}

			// Init IMA SDK
			var initIma = function() {
				if ( initialized ) return false;
				initialized = true;

				player.ima.initializeAdDisplayContainer();
				container.removeEventListener( startEvent, initIma );
			};

			// On ad event
			var onAdEvent = function( event ) {
				switch ( event.type ) {
					case google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED:
						if ( player && ! player.ended() && player.paused() ) {
							player.play();
						}					
						break;
				}			
			};

			// On ads manager loaded
			var onAdsManagerLoaded = function() {
				var events = [
					google.ima.AdEvent.Type.ALL_ADS_COMPLETED,
					google.ima.AdEvent.Type.CLICK,
					google.ima.AdEvent.Type.COMPLETE,
					google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
					google.ima.AdEvent.Type.FIRST_QUARTILE,
					google.ima.AdEvent.Type.LOADED,
					google.ima.AdEvent.Type.MIDPOINT,
					google.ima.AdEvent.Type.PAUSED,
					google.ima.AdEvent.Type.RESUMED,
					google.ima.AdEvent.Type.STARTED,
					google.ima.AdEvent.Type.THIRD_QUARTILE
				];

				for ( var index = 0; index < events.length; index++ ) {
					player.ima.addEventListener( events[ index ], onAdEvent );
				}
			};			

			// Remove controls from the player on iPad to stop native controls from stealing
			// our click
			try {
				var contentPlayer = document.getElementById( 'player_html5_api' );
				if ( ( navigator.userAgent.match( /iPad/i ) || navigator.userAgent.match( /Android/i ) ) &&	contentPlayer.hasAttribute( 'controls' ) ) {
					contentPlayer.removeAttribute( 'controls' );
				}
			} catch ( error ) {
				/** console.log( error ); */
			}

			// Start ads when the video player is clicked, but only the first time it's
			// clicked.				
			if ( navigator.userAgent.match( /iPhone/i ) ||	navigator.userAgent.match( /iPad/i ) ||	navigator.userAgent.match( /Android/i ) ) {
				startEvent = 'touchend';
			}			

			// ...
			var options = {
				id: 'player',
				adTagUrl: getAdTagUrl(),
				showCountdown: settings.ads.showCountdown,
				vpaidMode: google.ima.ImaSdkSettings.VpaidMode.ENABLED,
				adsManagerLoadedCallback: onAdsManagerLoaded
			};

			player.ima( options );

			container = document.getElementById( 'player' );
			container.addEventListener( startEvent, initIma );
			player.one( 'play', initIma );
		}			

		/**
		 * Get VAST URL
		 */
		function getAdTagUrl() {
			var url = settings.ads.adTagUrl;

			url = url.replace( '[domain]', encodeURIComponent( settings.siteUrl ) );
			url = url.replace( '[player_width]', player.currentWidth() );
			url = url.replace( '[player_height]', player.currentHeight() );
			url = url.replace( '[random_number]', Date.now() );
			url = url.replace( '[timestamp]', Date.now() );
			url = url.replace( '[page_url]', encodeURIComponent( window.top.location ) );
			url = url.replace( '[referrer]', encodeURIComponent( document.referrer ) );
			url = url.replace( '[ip_address]', settings.ipAddress );
			url = url.replace( '[post_id]', settings.videoId );
			url = url.replace( '[post_title]', encodeURIComponent( settings.videoTitle ) );
			url = url.replace( '[post_excerpt]', encodeURIComponent( settings.videoExcerpt ) );
			url = url.replace( '[video_file]', encodeURIComponent( player.currentSrc() ) );
			url = url.replace( '[video_duration]', player.duration() || '' );
			url = url.replace( '[autoplay]', settings.player.autoplay );

			return url;
		}
		
		/**
		 * Init context menu.
		 */
		function initContextMenu() {
			var contextMenu = document.getElementById( 'vjs-contextmenu' );
			var timeoutHandler = '';
			
			document.addEventListener( 'contextmenu', function( e ) {						
				if ( e.keyCode === 3 || e.which === 3 ) {
					e.preventDefault();
					e.stopPropagation();
					
					var width = contextMenu.offsetWidth,
						height = contextMenu.offsetHeight,
						x = e.pageX,
						y = e.pageY,
						doc = document.documentElement,
						scrollLeft = ( window.pageXOffset || doc.scrollLeft ) - ( doc.clientLeft || 0 ),
						scrollTop = ( window.pageYOffset || doc.scrollTop ) - ( doc.clientTop || 0 ),
						left = x + width > window.innerWidth + scrollLeft ? x - width : x,
						top = y + height > window.innerHeight + scrollTop ? y - height : y;
			
					contextMenu.style.display = '';
					contextMenu.style.left = left + 'px';
					contextMenu.style.top = top + 'px';
					
					clearTimeout( timeoutHandler );

					timeoutHandler = setTimeout(function() {
						contextMenu.style.display = 'none';
					}, 1500 );				
				}														 
			});
			
			if ( settings.license.logoLink ) {
				contextMenu.addEventListener( 'click', function() {
					top.window.location.href = settings.license.logoLink;
				});
			}
			
			document.addEventListener( 'click', function() {
				contextMenu.style.display = 'none';								 
			});
		}

		/**
		 * Initialize the player.
		 */		
		function initPlayer() {
			settings.player.html5 = {
				vhs: {
					overrideNative: ! videojs.browser.IS_ANY_SAFARI,
				}
			};
			
			player = videojs( 'player', settings.player );			

			// Dispatch an event
			var evt = document.createEvent( 'CustomEvent' );
			evt.initCustomEvent( 'player.init', false, false, { player: player, settings: settings } );
			window.dispatchEvent( evt );

			// On player ready
			player.ready(function() {
				lastEvent = 'ready';
				body.classList.remove( 'vjs-waiting' );				

				playButton = document.querySelector( '.vjs-big-play-button' );
				if ( playButton !== null ) {
					playButton.addEventListener( 'click', onPlayClicked );
				}

				if ( settings.volume > -1 ) {
					player.volume( settings.volume / 100 );
				}
			});
			
			// On metadata loaded
			player.one( 'loadedmetadata', function() {
				lastEvent = 'loadedmetadata';

				// Standard quality selector
				var qualitySelector = document.querySelector( '.vjs-quality-selector' );

				if ( qualitySelector !== null ) {
					var nodes = qualitySelector.querySelectorAll( '.vjs-menu-item' );

					for ( var i = 0; i < nodes.length; i++ ) {
						var node = nodes[ i ];
						var textNode = node.querySelector( '.vjs-menu-item-text' );
						
						if ( textNode == 'HD' ) {
							node.innerHTML += '<span class="vjs-quality-menu-item-sub-label">HD</span>';
						}
					}
				}
			});

			// On play
			player.on( 'play', function() {
				lastEvent = 'play';

				if ( ! hasVideoStarted ) {
					hasVideoStarted = true;
					body.classList.remove( 'vjs-waiting' );

					if ( settings.videoId > 0 ) {
						updateViewsCount();
					}
				}
			});

			// On playing
			player.on( 'playing', function() {
				lastEvent = 'playing';
				player.trigger( 'controlsshown' );
			});

			// On ended
			player.on( 'ended', function() {
				if ( lastEvent == 'ended' ) {
					return false;
				}

				lastEvent = 'ended';
				player.trigger( 'controlshidden' );

				// Autoplay Next Video
				if ( settings.autoAdvance ) {
					parent.postMessage({ 				
						message: 'ON_YENDIFVIDEOSHARE_ENDED',			
						id: settings.uid,
						loop: settings.loop,
					}, '*' ); 
				}
			});

			// Standard quality selector
			player.on( 'qualitySelected', function( event, source ) {
				player.removeClass( 'vjs-hd' );

				if ( source.label == 'HD' ) {
					player.addClass( 'vjs-hd' );
				}
			});

			// HLS quality selector
			var src = player.src();

			if ( /.m3u8/.test( src ) || /.mpd/.test( src ) ) {
				if ( settings.player.controlBar.children.indexOf( 'qualitySelector' ) !== -1 ) {
					player.qualityMenu();
				}
			}

			// Offset
			var offset = {};

			if ( settings.hasOwnProperty( 'start' ) ) {
				offset.start = settings.start;
			}

			if ( settings.hasOwnProperty( 'end' ) ) {
				offset.end = settings.end;
			}
			
			if ( Object.keys( offset ).length > 1 ) {
				offset.restart_beginning = false;
				player.offset( offset );
			}

			// Share / Embed
			if ( settings.hasOwnProperty( 'share' ) || settings.hasOwnProperty( 'embed' ) ) {
				overlays.push({
					content: '<button type="button" id="vjs-share-embed-button" class="vjs-share-embed-button" title="Share"><span class="vjs-icon-share" aria-hidden="true"></span><span class="vjs-control-text" aria-live="polite">Share</span></button>',
					class: 'vjs-share',
					align: 'top-right',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});					
			}		

			// Download
			if ( settings.hasOwnProperty( 'download' ) ) {
				var className = 'vjs-download';

				if ( settings.hasOwnProperty( 'share' ) || settings.hasOwnProperty( 'embed' ) ) {
					className += ' vjs-has-share';
				}

				overlays.push({
					content: '<a href="' + settings.download.url + '" id="vjs-download-button" title="Download" target="_blank"><span class="vjs-icon-file-download" aria-hidden="true"></span><span class="vjs-control-text" aria-live="polite">Download</span></a>',
					class: className,
					align: 'top-right',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});
			}

			// Logo
			if ( settings.canDo ) {
				if ( settings.license.showLogo && settings.license.logoImage ) {
					var logo  = '';
					var style = 'opacity: ' + settings.license.logoOpacity;
					var align = 'bottom-left';

					switch ( settings.license.logoPosition ) {
						case 'topleft':
							align = 'top-left';
							break;
						case 'topright':
							align = 'top-right';
							break;					
						case 'bottomright':
							align = 'bottom-right';
							break;
					}

					logo = '<img src="' + settings.license.logoImage + '" style="' + style + '" alt="" />';
					if ( settings.license.logoLink ) {
						logo = '<a href="' + settings.license.logoLink + '" target="_top"><img src="' + settings.license.logoImage + '" style="' + style + '" alt="" /><span class="vjs-control-text" aria-live="polite">Logo</span></a>';
					}

					overlays.push({
						content: logo,
						class: 'vjs-logo',
						align: align,
						start: 'controlsshown',
						end: 'controlshidden',
						showBackground: false					
					});
				}
			} else {
				overlays.push({
					content: '<a href="https://yendifplayer.com/" target="_blank" style="display:inline-block;background-color:#E34D2B;border-radius:2px;margin:5px;padding:6px;color:#FFF;font-family:Trebuchet MS;font-size:12px;font-style:italic;text-decoration:none;-moz-opacity:0.7;opacity:0.7;z-index:0011;cursor:pointer;">Powered by Yendif</a>',
					align: 'bottom-left',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});
			}

			// Overlay
			if ( overlays.length > 0 ) {
				player.overlay({
					content: '',
					overlays: overlays
				});

				if ( settings.hasOwnProperty( 'share' ) || settings.hasOwnProperty( 'embed' ) ) {
					var options = {};
					options.content = document.getElementById( 'vjs-share-embed' );
					options.temporary = false;

					var ModalDialog = videojs.getComponent( 'ModalDialog' );
					var modal = new ModalDialog( player, options );
					modal.addClass( 'vjs-modal-dialog-share-embed' );

					player.addChild( modal );

					var wasPlaying = true;
					document.getElementById( 'vjs-share-embed-button' ).addEventListener( 'click', function() {
						wasPlaying = ! player.paused;
						modal.open();						
					});

					modal.on( 'modalclose', function() {
						if ( wasPlaying ) {
							player.play();
						}						
					});
				}

				if ( settings.hasOwnProperty( 'embed' ) ) {
					document.getElementById( 'vjs-input-embed-code' ).addEventListener( 'focus', function() {
						this.select();	
						document.execCommand( 'copy' );					
					});
				}
			}

			// Initialize ads
			if ( settings.hasOwnProperty( 'ads' ) ) {
				initAds();
			}

			// Keyboard hotkeys
			if ( settings.hotkeys ) {
				player.hotkeys();
			}
			
			// Initialize context menu
			if ( settings.license.contextmenuLabel ) {
				initContextMenu();
			}

			// Custom error
			videojs.hook( 'beforeerror', function( player, error ) {
				// Prevent current error from being cleared out
				if ( error === null ) {
					return player.error();
				}

				// But allow changing to a new error
				if ( error.code == 2 || error.code == 4 ) {
					var src = player.src();

					if ( /.m3u8/.test( src ) || /.mpd/.test( src ) ) {
						return {
							code: error.code,
							message: settings.i18n.streamNotFound
						}
					}
				}
				
				return error;
			});
		}	

		document.addEventListener( 'DOMContentLoaded', function() {
			initPlayer();
		});
    </script>
</body>
</html>