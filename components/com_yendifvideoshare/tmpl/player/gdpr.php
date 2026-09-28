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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

	<style type="text/css">
        html, 
        body {			
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important; 
			padding: 0 !important; 
			font-family: Verdana, Geneva, sans-serif;
			font-size: 14px;
            line-height: 1.5;
            overflow: hidden;
        }

        #privacy-wrapper {            
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #222;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #FFF;
            text-align: center;
        }

        #privacy-consent-block {
            position: relative;
            margin: 0 30px;
            padding: 15px;
            top: 50%;
            background: rgba( 43, 51, 63, 0.7 );
            border-radius: 3px;
            transform: translateY( -50% );
            -ms-transform: translateY(- 50% );
            -webkit-transform: translateY( -50% );
        }

        #privacy-consent-button {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 15px;
            background: #F00;
            border-radius: 3px;
            cursor: pointer;
        }

        #privacy-consent-button:hover {
            opacity: 0.8;
        }

        @media only screen and (max-width: 250px) {
            #privacy-consent-block {
                margin: 0;
                font-size: 12px;               
            }
        }
    </style>
</head>
<body>    
    <div id="privacy-wrapper" style="background-image: url(<?php echo $this->item->image; ?>);">
		<div id="privacy-consent-block" >
			<div id="privacy-consent-message"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_GDPR_CONSENT_MESSAGE' ); ?></div>
			<div id="privacy-consent-button"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_GDPR_CONSENT_BUTTON_LABEL' ) ; ?></div>
		</div>
	</div>
				
	<script type="text/javascript">
		/**
		* Set cookie for accepting the privacy consent.
		*
		* @since  2.0.0
		*/
		function ajaxSubmit() {	
            document.getElementById( 'privacy-consent-button' ).innerHTML = '...';

			var xmlhttp;

			if ( window.XMLHttpRequest ) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
			};
			
			xmlhttp.onreadystatechange = function() {				
				if ( 4 == xmlhttp.readyState && 200 == xmlhttp.status && xmlhttp.responseText ) {					
                    var url = window.location.href;
                    var separator = url.indexOf( '?' ) > -1 ? '&' : '?';

                    window.location.href = url + separator + 'autoplay=1'; // Reload document
				}					
			};	

			xmlhttp.open( 'GET', '<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&task=video.cookie', true );
			xmlhttp.send();							
		}
		
		document.getElementById( 'privacy-consent-button' ).addEventListener( 'click', ajaxSubmit );
	</script>
</body>
</html>
