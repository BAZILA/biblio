(function( $ ) {
	'use strict';

    /**
	 * Toggle MP4 upload/url fields required
	 *
	 * @since  2.0.1
	 */
    function toggleMp4FieldsRequired() {
        if ( $( 'input[name="jform_mp4_field_type"]:checked' ).val() == 'url' ) {
            $( '#jform_mp4' ).removeClass( 'required' ).removeAttr( 'required' );
            $( '#jform_mp4_url' ).addClass( 'required' );
        } else {
            $( '#jform_mp4_url' ).removeClass( 'required' ).removeAttr( 'required' );
            $( '#jform_mp4' ).addClass( 'required' );
        };
    }

    /**
	 * Toggle HLS/DASH fields required
	 *
	 * @since  2.0.1
	 */
    function toggleHlsDashFieldsRequired() {
        var dash_value = $( '#jform_dash' ).val();

        if ( dash_value.trim() == '' ) {
            $( '#jform_hls' ).addClass( 'required' );
            $( '#jform_hls-lbl span' ).show();
        } else {
            $( '#jform_hls' ).removeClass( 'required' ).removeAttr( 'required' );
            $( '#jform_hls-lbl span' ).hide();
        }
    }

	/**
	 * Called when the page has loaded
	 *
	 * @since  2.0.0
	 */
	$(function() {
        // Toggle upload/url fields
        $( '.yendif-video-share-form-field-type input[type=radio]' ).on( 'change', function() {
            var type = this.value;

            if ( type == 'url' ) {
                $( this ).closest( '.controls' ).find( '.yendif-video-share-form-field-upload' ).hide();
                $( this ).closest( '.controls' ).find( '.yendif-video-share-form-field-url' ).show();     
            } else {
                $( this ).closest( '.controls' ).find( '.yendif-video-share-form-field-url' ).hide();
                $( this ).closest( '.controls' ).find( '.yendif-video-share-form-field-upload' ).show();
            }
        });

        // Append a star on the conditionally required field elements
        $( '.required-conditionally-label' ).append( '<span class="star" aria-hidden="true">&nbsp;*</span>' );

        // Toggle required fields
        $( '#jform_type', '#form-video' ).on('change', function() {
            var type = $( this ).val();

            $( '.required-conditionally' ).removeClass( 'required' ).removeAttr( 'required' ); 

            switch ( type ) {
                case 'youtube':
                case 'vimeo':
                    $( '#jform_' + type ).addClass( 'required' );
                    break;
                case 'rtmp':
                    toggleHlsDashFieldsRequired();
                    break;
                default:
                    toggleMp4FieldsRequired();
            }

            document.formvalidator.attachToForm( $( '#form-video' ).get(0) );
        }).trigger( 'change' );

        $( 'input[name="jform_mp4_field_type"]' ).on( 'change', function() {
            toggleMp4FieldsRequired();
            document.formvalidator.attachToForm( $( '#form-video' ).get(0) );            
        });

        $( '#jform_dash' ).on( 'blur', function() {
            toggleHlsDashFieldsRequired();
            document.formvalidator.attachToForm( $( '#form-video' ).get(0) );
        });
	});

})( jQuery );