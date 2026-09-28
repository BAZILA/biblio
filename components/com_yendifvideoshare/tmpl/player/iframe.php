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

use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;

YendifVideoSharePlayer::updateViews( $this->item->id );
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <style type="text/css">
        html, 
        body, 
        iframe {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important; 
            padding: 0 !important; 
            overflow: hidden;
        }
    </style>
</head>
<body>    
    <?php
    $thirdparty = $this->item->thirdparty;

    // Detect YouTube embed codes (covers youtube.com, youtu.be, and embed URLs)
    $isYouTube = preg_match( '/youtube\.com|youtu\.be/i', $thirdparty );

    if ( $isYouTube ) {
        // Check if referrerpolicy already exists
        if ( ! preg_match( '/referrerpolicy\s*=/i', $thirdparty ) ) {
            // Add referrerpolicy inside the iframe tag
            $thirdparty = preg_replace(
                '/<iframe/i',
                '<iframe referrerpolicy="strict-origin-when-cross-origin"',
                $thirdparty,
                1 // only replace the first iframe
            );
        }
    }

    echo $thirdparty;
    ?>
</body>
</html>