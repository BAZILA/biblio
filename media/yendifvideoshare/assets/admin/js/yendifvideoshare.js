/*
 * @version		$Id: yendifvideoshare.js 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd 
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

if( typeof( yendif ) === 'undefined' ) {
    var yendif = {};
};

yendif.fields = {};
yendif.files = 0;

jQuery(document).ready(function() {
	
	// allow user to videos
	jQuery('.yendif-allow-users input[type="checkbox"]').on('change', function(){
	  	this.value = this.checked ? 1 : 0;
	}).change();
	
	//Advertising custom or vast ads
	jQuery( "input[name$='yendif-ads']" ).click( function() {
		var value = jQuery(this).val();
	
		jQuery("div.yendif-advert").hide();
		jQuery("#yendif-advert-" + value ).show();
	});
	
	// YT Embed
	jQuery( "#yt-type" ).on( 'change', function() {
												
		var type = jQuery( this ).val(),
			placeholder = '';
		
		switch( type ) {
			case 'search':
				placeholder = yendif.msg['yt_search_placeholder'];
				break;
			case 'channel':
			case 'user':
			case 'playlist':
				placeholder = yendif.msg['yt_search_placeholder_'+type];
				break;
		};
		
		jQuery( '#yt-keyword' ).attr( "placeholder", placeholder );
		
	});
	
	jQuery( "#yt-search-form" ).on( 'submit', function() {
		
		jQuery( '#yt-notes' ).slideUp( 'fast' );
		jQuery( "#yt-videos-list" ).html('');
		
		ytEmbed.init({
			'block'   : 'yt-videos-list',
			'key'     : document.getElementById( 'yt-api-key' ).value,
			'q'       : document.getElementById( 'yt-keyword' ).value,
			'type'    : document.getElementById( 'yt-type' ).value,
			'results' : document.getElementById( 'yt-results' ).value,
			'order'   : 'most_relevance',
			'player'  : 'link',
			'layout'  : 'full'
		});
		
		return false;
		
	});
	
	jQuery( "#yt-reset-btn" ).on( 'click', function() {
		jQuery( "#yt-preloader" ).hide();
		jQuery( '#yt-notes' ).slideDown( 'fast' );
		jQuery( "#yt-videos-list" ).html('');
	});
	
	jQuery( "#yt-import-form" ).on( 'submit', function() {

		jQuery( "#yt-preloader" ).show();
		
		jQuery.ajax({
			url        : "index.php?option=com_yendifvideoshare&view=import&task=insertVideos",
			dataType   : "html",
			data       : jQuery( "#yt-import-form" ).serialize(),
			type       : "POST",
			success    : function( e ) {
				jQuery( "#yt-preloader" ).hide();
				jQuery( "#system-message-container" ).html( '<div class="alert alert-error">'+e+'</div>' );
				jQuery( "html, body" ).animate({ scrollTop: 0 }, "slow");
			},
			error      : function( e ) {
				alert( 'Occurs error' );
			}
		});
		
		return false;
		
	});
	
	// Enable / Disable Facebook settings
	jQuery( 'select#comments' ).on( 'change', function() {	
		var value = jQuery(this).val();
		
		if( value == 'facebook' ) {
			jQuery( '.yendif-facebook-options' ).show();
		} else {
			jQuery( '.yendif-facebook-options' ).hide();
		};
	}).change();
	
	// ...
	jQuery( 'select#sef_video' ).on( 'change', function() {
		var value = jQuery(this).val();
		
		if( value == 1 ) {
			jQuery( '#sef_video_prefix' ).addClass('hide');
		} else {
			jQuery( '#sef_video_prefix' ).removeClass('hide');
		};
	}).change();
			
	// Show or Hide media fields according to the "Type" selected
	jQuery( '#type', '.yendif-media-types' ).on( 'change', function() {	
		var num_fields = jQuery('.yendif-media-fields').length;
		var option = jQuery( this ).val();
		
		if ( 'pro_only' == option ) {
			alert( 'Sorry, this is a PRO Only.' );
			jQuery( this ).val( 'video' );
			type = 'video';
		}
		
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
					//jQuery( '#rtmp' ).removeClass( 'required' );
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
			view = document.getElementById('yendif-insert-view').value,
			field = jQuery( this ).data( 'field' ),
			accept = jQuery( this ).data( 'accept' ),
			iframe_id = 'yendif-upload-iframe-'+field;
		
		if( typeof yendif.fields[ field ] === 'undefined' ) {
			yendif.fields[ field ] = true;
			
			jQuery( '#yendif-media-uploader' ).append( '<form name="yendif-upload-form-'+field+'" id="yendif-upload-form-'+field+'" target="'+iframe_id+'" action="index.php?option=com_yendifvideoshare&view=upload&format=raw&id='+id+'&f='+field+'&p='+view+'" method="post" enctype="multipart/form-data" encoding="multipart/form-data"><input type="file" name="upload_'+field+'" id="yendif-upload-field-'+field+'" class="yendif-upload-field"  accept="'+accept+'" data-field="'+field+'" /></form>' );
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



