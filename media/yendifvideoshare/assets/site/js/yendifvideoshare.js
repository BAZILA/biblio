/*
 * @version		$Id: yendifvideoshare.js 1.2.6 05-05-2017 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2017 Yendif Technologies (P) Ltd 
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

if( typeof( yendif ) === 'undefined' ) {//
    var yendif = {};
};

yendif.fields = {};
yendif.files = 0;

function onEnded( event ){
	
	//console.log( event.data.message );
	var hasPlaylist = jQuery( '.vjs-playlist ul#vjs-playlist-data li' ).length;
	if( event.data.message == 'YVS_VIDEO_ENDED'  && hasPlaylist) {
		
		// get iframeId 
		var iframeId = event.data.iframeId;

		if( iframeId ) {
			
			// get the Values
			var vid = jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-selected').next('li').attr('data-vid');
			var mid = jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-selected').next('li').attr('data-mid');
			var baseurl = jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-selected').next('li').attr('data-baseurl');
	
			$url = baseurl +'index.php?option=com_yendifvideoshare&view=player&vid='+ vid +'&mid='+mid+'&autoplay=1&format=raw';
			
			if( vid != null ){
				
				// Set thumbnail active & removed
				jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-selected').next('li').addClass( 'vjs-selected' );
				jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-selected').prev('li').removeClass( 'vjs-selected' );
				
				// Player Setsrc & Play
				jQuery('.yendifplayers #'+ iframeId ).attr( 'src', $url );
			}
		}

	 }
}

function yendifgetCookie( name ) {
	var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
	return v ? v[2] : null;
}

function yendifsetCookie( name, value ) {
	document.cookie = name + "=" + value;
}

	
jQuery(document).ready(function() {
	
	window.addEventListener( "message", onEnded, false );
	
	//new 1.2.8 Code
	jQuery( 'body' ).on( 'click', '.vjs-playlist-item', function( e ) {
																 
		// Vars
		var vid = jQuery( this ).data( 'vid' );
		var mid = jQuery( this ).data( 'mid' );
		var baseurl = jQuery( this ).data( 'baseurl' );
		var iframeId = jQuery( this ).closest( '.yendif-playlist-container' ).find('iframe').attr('id');

		// Set thumbnail active
		jQuery('#'+ iframeId ).closest('.yendif-playlist-player').next('.vjs-playlist').find('ul#vjs-playlist-data li.vjs-playlist-item').removeClass( 'vjs-selected' );
		jQuery( this ).addClass( 'vjs-selected' );
		
		var url = baseurl +'index.php?option=com_yendifvideoshare&view=player&vid='+ vid +'&mid='+mid+'&autoplay=1&format=raw';
		
		// Player Setsrc & Play
		jQuery(' .yendifplayers #'+ iframeId ).attr( 'src', url );

	});
	
	// GDPR SetCookie
	jQuery( '.yendifgdprConsent' ).click(function() {
												  
		yendifsetCookie( 'yendif_gdpr_consent',1 );
		jQuery('iframe').attr('src', function() { return jQuery(this).attr('data-src'); })
		.removeAttr('data-src');
		location.reload(true);
		
	});
	

	// Magnific Popup 
	if( jQuery.fn.magnificPopup !== undefined ) {
		var yendif_ratio = parseFloat( jQuery(this).data('ratio') ) || 0.5625;
		jQuery('.yendif-popup-gallery').magnificPopup({ 
			delegate: 'li', // the selector for gallery item
			type: 'iframe',
			overflowY: 'auto',			
			removalDelay: 300,
			mainClass: 'yendif-video-share-popup',
			iframe: { //to create title, close, iframe, counter div's
				markup: '<div class="mfp-title-bar">'+
							'<h2 class="mfp-title">Default Title</h2>'+
							'<div class="mfp-close" title="Close (Esc)"></div>'+
						'</div>'+							
						'<div class="mfp-iframe-scaler" style="padding-top:'+( yendif_ratio * 100 )+'%;" >'+            												
							'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+																								
						'</div>'+
						'<div class="mfp-bottom-bar">'+
							'<div class="mfp-counter"></div>'+								
						'</div>'																								        			
			},		
			callbacks: { //to assign title 									
				markupParse: function(template, values, item) {						 							
					values.title = item.el.attr('data-title');					
				}																			
			},		
			gallery: { //to build gallery				
				enabled: true,
				navigateByImgClick: true,
				tPrev: 'Previous',
				tNext: 'Next'													
			}									
		});	
	};
	
	// Rating
	jQuery( 'body' ).on( 'click', 'a.yendif-rating-trigger', function( e ) {
		e.preventDefault();
		
		var rating  = jQuery( this ).data( 'value' ),		
			videoid = jQuery( this ).data( 'id' ),
			userid  = yendif.userid,
			base    = yendif.base;
	
		if( yendif.allow_guest_rating == 0 && userid == 0 ) {
			alert( yendif.alert_message );
			return false;
		};

		document.getElementById("yendif-ratings-widget").innerHTML = '<span class="yendif-preloader"></span>';
		var xmlhttp;

   		if( window.XMLHttpRequest ) {
   			xmlhttp = new XMLHttpRequest();
   		} else {
   			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
   		};

   		xmlhttp.onreadystatechange = function() {
   			if( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
				if( xmlhttp.responseText ) {		    
					document.getElementById("yendif-ratings-widget").innerHTML = xmlhttp.responseText;
				};
       		};
   		};	

		xmlhttp.open( "GET", base + "index.php?option=com_yendifvideoshare&view=ajax&task=ratings&format=raw&rating=" + rating + "&videoid=" + videoid, true );
   		xmlhttp.send();
			
		return false;
		
	});
	
	// Likes & Dislikes
	jQuery( 'body' ).on( 'click', '.yendif-like-btn, .yendif-dislike-btn', function( e ) {
		e.preventDefault();
		
		var like    = jQuery( this ).data( 'like' ),
			dislike = jQuery( this ).data( 'dislike' ),
			videoid = jQuery( this ).data( 'id' ),
			userid  = yendif.userid,
			base    = yendif.base;
	
		if( yendif.allow_guest_like == 0 && userid == 0 ) {
			alert( yendif.alert_message );
			return false;
		};

		document.getElementById("yendif-likes-dislikes-widget").innerHTML = '<span class="yendif-preloader"></span>';
		var xmlhttp;

   		if( window.XMLHttpRequest ) {
   			xmlhttp = new XMLHttpRequest();
   		} else {
   			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
   		};

   		xmlhttp.onreadystatechange = function() {
   			if( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
				if( xmlhttp.responseText ) {	
					document.getElementById("yendif-likes-dislikes-widget").innerHTML = xmlhttp.responseText;
				};
       		};
   		};	

		xmlhttp.open( "GET", base + "index.php?option=com_yendifvideoshare&view=ajax&task=likes_dislikes&format=raw&likes=" + like + "&dislikes=" + dislike + "&videoid=" + videoid, true );
   		xmlhttp.send();
			
		return false;
		
	});
	
	// Show or Hide media fields according to the "Type" selected
	jQuery( '#type', '.yendif-media-types' ).on( 'change', function() {	
		var num_fields = jQuery('.yendif-media-fields').length;
		var option = jQuery( this ).val();
		jQuery( '.yendif-media-fields' ).fadeOut( 200, function() {
			if( --num_fields > 0 ) return;
			jQuery( '.yendif-type-'+option ).fadeIn(200);
			
			jQuery( '.yendif-media-required' ).removeClass( 'required' );
			switch( option ) {
				case 'video' :
					jQuery( '#mp4' ).addClass( 'required' );
					break;
				case 'rtmp' :
					jQuery( '#hls' ).addClass( 'required' );
					break;
				default :
					jQuery( '#'+option ).addClass( 'required' );
			};
		});	
	}).change();
	
	// Show or Hide more video formats
	jQuery( '#yendif-more-formats' ).on( 'click', function( e ) {
		e.preventDefault();

		if( jQuery( '#yendif-more-text' ).hasClass( 'hide' ) ) {
			jQuery( '#yendif-more-text' ).removeClass( 'hide' );
			jQuery( '#yendif-less-text' ).addClass( 'hide' );
			jQuery( '#yendif-more-formats-container' ).addClass( 'hide' );
		} else {
			jQuery( '#yendif-more-text' ).addClass( 'hide' );
			jQuery( '#yendif-less-text' ).removeClass( 'hide' );
			jQuery( '#yendif-more-formats-container' ).removeClass( 'hide' );
		};
	});
	
	// Show or Hide browse button
	jQuery( '.yendif-media-uploader-widget input[type="radio"]' ).on( 'change', function() {
		jQuery( this ).closest( '.yendif-media-uploader-widget' ).find( 'button' ).toggle();
	});
	
	// Trigger upload
	jQuery( '.yendif-media-uploader-widget .yendif-browse-btn' ).on( 'click', function( e ) {

		var id = document.getElementById('yendif-insert-id').value,
			field = jQuery( this ).data( 'field' ),
			accept = jQuery( this ).data( 'accept' ),
			iframe_id = 'yendif-upload-iframe-'+field,
			base = yendif.base;
		
		if( typeof yendif.fields[ field ] === 'undefined' ) {
			yendif.fields[ field ] = true;
			
			jQuery( '#yendif-media-uploader' ).append( '<form name="yendif-upload-form-'+field+'" id="yendif-upload-form-'+field+'" target="'+iframe_id+'" action="'+base+'index.php?option=com_yendifvideoshare&view=upload&format=raw&id='+id+'&f='+field+'" method="post" enctype="multipart/form-data" encoding="multipart/form-data"><input type="file" name="upload_'+field+'" id="yendif-upload-field-'+field+'" class="yendif-upload-field"  accept="'+accept+'" data-field="'+field+'" /></form>' );
		};
		
		jQuery('#yendif-upload-field-'+field).trigger('click');
		
	});
	
	// Do upload
	jQuery('body').on( 'change', '.yendif-upload-field', function() {
		
			var field = jQuery( this ).data( 'field' );
			
			var $browse_button = jQuery( '#yendif-browse-btn-'+field ),			
				iframe_id = 'yendif-upload-iframe-'+field;
			
			jQuery( '#yendif-media-uploader' ).append( '<iframe id="'+iframe_id+'" name="'+iframe_id+'" width="0" height="0" border="0" style="width:0; height:0; border:0;"></iframe>' );
		
			window.frames[ iframe_id ].name = iframe_id;
		
			var $value_field = document.getElementById( field ),
				$form_elem   = document.getElementById( 'yendif-upload-form-'+field ),
				$file_elem   = document.getElementById( 'yendif-upload-field-'+field ),
				$iframe_elem = document.getElementById( iframe_id ),
				$resp_elem   = document.getElementById( 'yendif-upload-response-'+field );
			
			// Add event...
   			var yendif_upload_handler = function() {		
   				if( $iframe_elem.detachEvent ) {
					$iframe_elem.detachEvent("onload", yendif_upload_handler);
				} else {
					$iframe_elem.removeEventListener("load", yendif_upload_handler, false);
				};

       			// Message from server...
				var content = null;
		
				if( $iframe_elem.contentWindow && $iframe_elem.contentWindow.document.body ) {
   					content = $iframe_elem.contentWindow.document.body.innerHTML;
				} else if( $iframe_elem.document && $iframe_elem.document.body ) {
   					content = $iframe_elem.document.body.innerHTML;
				} else if( $iframe_elem.contentDocument && $iframe_elem.contentDocument.body ) {
   					content = $iframe_elem.contentDocument.body.innerHTML;
				};

				--yendif.files;
		
				if( content == '' ) content = 'unknown_error';
				
				if( /invalid_file_type|invalid_file_size|invalid_mime_type|error_moving_file|unknown_error/.test(content) ) {
					$value_field.value = '';
					$resp_elem.innerHTML = '<span class="yendif-upload-failed"></span>' + yendif.msg[content] + ' - <a href="javascript:void(0);" onclick="yendif_reset_upload(\''+field+'\');">' + yendif.msg['retry'] + '</a>';
				} else {
					$value_field.value = content;
					$resp_elem.innerHTML = '<span class="yendif-upload-success"></span>' + yendif.msg['success'] + ' - <a href="javascript:void(0);" onclick="yendif_reset_upload(\''+field+'\');">' + yendif.msg['reset'] + '</a>';
				};
				
			};

   			if( $iframe_elem.addEventListener ) {
				$iframe_elem.addEventListener("load", yendif_upload_handler, true);
			};
	
   			if( $iframe_elem.attachEvent ) {
				$iframe_elem.attachEvent("onload", yendif_upload_handler);
			};
	
   			// Submit the form...	
   			$form_elem.submit();
			++yendif.files;
	
			$browse_button.hide();
			var file_elem_path = $file_elem.value.substring($file_elem.value.lastIndexOf("\\") + 1, $file_elem.value.length);
			$resp_elem.innerHTML = '<span class="yendif-upload-preloader"></span>' + file_elem_path + ' - <a href="javascript:void(0);" onclick="yendif_abort_upload(\''+field+'\');">' + yendif.msg['cancel'] + '</a>';
		
		});

});


function yendif_abort_upload( field ) {
	var iframe_id = 'yendif-upload-iframe-'+field;
	
	var $browse_button = jQuery( '#yendif-browse-btn-'+field ),	
		$value_field   = document.getElementById( field ),
		$resp_elem     = document.getElementById( 'yendif-upload-response-'+field ),
		$iframe_elem   = document.getElementById( iframe_id );

	if( $iframe_elem.contentWindow.stop ) {
    	$iframe_elem.contentWindow.stop();
    } else {
    	$iframe_elem.contentWindow.document.execCommand('Stop');
    };
	
	--yendif.files;
	
	$browse_button.show();	
	$value_field.value   = '';
	$resp_elem.innerHTML = '';	
	$iframe_elem.parentNode.removeChild( $iframe_elem );
	
	return false;
};

function yendif_reset_upload( field ) {	
	var iframe_id = 'yendif-upload-iframe-'+field;
	
	var $browse_button = jQuery( '#yendif-browse-btn-'+field ),	
		$value_field   = document.getElementById( field ),
		$resp_elem     = document.getElementById( 'yendif-upload-response-'+field ),
		$iframe_elem   = document.getElementById( iframe_id );
		
	$browse_button.show();	
	$value_field.value   = '';
	$resp_elem.innerHTML = '';
	$iframe_elem.parentNode.removeChild( $iframe_elem );
	
	return false;
};




