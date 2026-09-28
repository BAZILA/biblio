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
	 * Called when the page has loaded.
	 *
	 * @since 2.0.0
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

        // Video: Toggle required fields
        $( '#jform_type', '#video-form' ).on('change', function() {
            var type = $( this ).val();

            $( '.required-conditionally' ).removeClass( 'required' ).removeAttr( 'required' );

            switch ( type ) {
                case 'youtube':
                case 'vimeo':                
                case 'thirdparty':
                    $( '#jform_' + type ).addClass( 'required' );
                    break;
                case 'rtmp':
                    toggleHlsDashFieldsRequired();
                    break;
                default:
                    toggleMp4FieldsRequired();
            }

            document.formvalidator.attachToForm( $( '#video-form' ).get(0) );
        }).trigger( 'change' );

        $( 'input[name="jform_mp4_field_type"]' ).on( 'change', function() {
            toggleMp4FieldsRequired();
            document.formvalidator.attachToForm( $( '#video-form' ).get(0) );            
        });

        $( '#jform_dash' ).on( 'blur', function() {
            toggleHlsDashFieldsRequired();
            document.formvalidator.attachToForm( $( '#video-form' ).get(0) );
        });

        // Video: Modal
        var modalbox = document.getElementById( 'yendif-video-share-modal-box' );

        if ( modalbox ) {
            var modalTitle = modalbox.querySelector( '.modal-title' );
            var modalBody = modalbox.querySelector( '.modal-body' );	

            modalbox.addEventListener( 'show.bs.modal', function( event ) {
                // Button that triggered the modal
                var button = event.relatedTarget;

                // Set the modal title
                var title = button.getAttribute( 'data-bs-title' );
                modalTitle.textContent = title;               
                
                // Set the modal content
                var item_url = button.getAttribute( 'data-bs-url' );
                modalBody.innerHTML = '<iframe width="640" height="360" src="' + item_url + '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            });

            modalbox.addEventListener( 'hidden.bs.modal', function( event ) {
                // Set the modal title & content empty
                modalTitle.textContent = ''; 
                modalBody.innerHTML = '';
            });
        }
        
        // Video: Insert related videos
        $( '#yendif-video-share-related-insert-btn' ).on( 'click', function() {
            window.yendifInsertRelated( yendif.params );

            if ( window.Joomla.Modal ) {
                window.Joomla.Modal.getCurrent().close();
            }
        });

        // Video: Remove related video
        $( document ).on( 'click', '.yendif-video-share-related-item-remove', function() {
            $( this ).closest( 'tr' ).remove();
            
            if ( $( '#yendif-video-share-related-items tr' ).length == 0 ) {
                $( '#yendif-video-share-related-empty-note' ).removeClass( 'hidden' );
            }
        });
        
        // Import: Toggle required fields
        $( '#jform_type', '#import-form' ).on('change', function() {  
            $( '.required-conditionally' ).removeClass( 'required' );
            document.formvalidator.validate( $( '.required-conditionally' ).get(0) );

            var type = $( this ).val();
            $( '#jform_' + type ).addClass( 'required' );
        }).trigger( 'change' );

        // Import: Disable fields on edit
        if ( $( '#import-form' ).length ) {
            var id = parseInt( $( '#jform_id' ).val() );

            if ( id > 0 ) {
                $( '.disable-on-edit', '#import-form' ).prop( 'disabled', true );
            }
        }
	});

})( jQuery );

/**
 * Custom Toggle Button
 *
 * @since  2.0.0
 */
window.Joomla = window.Joomla || {};

(function ( window, Joomla ) {
    Joomla.toggleField = function ( id, task, field ) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if ( ! cb ) return false;

        while ( true ) {
            cbx = f[ 'cb' + i ];

            if ( ! cbx ) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement( 'input' );

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild( inputField );

        cb.checked = true;
        f.boxchecked.value = 1;
        Joomla.submitform( task );

        return false;
    };
})( window, Joomla );

/**
 * Process related videos
 *
 * @since  2.1.1
 */
function yendifProcessRelated( videos ) {
    yendif.params = videos;

    if ( videos.length > 0 ) {
        $( '#yendif-video-share-related-insert-btn' ).prop( 'disabled', false );
    } else {
        $( '#yendif-video-share-related-insert-btn' ).prop( 'disabled', true );
    }
}

/**
 * Insert related videos
 *
 * @since  2.1.1
 */
function yendifInsertRelated( videos ) {
    var html;

    for ( var i = 0; i < videos.length; i++ ) {
        var item = videos[ i ];

        if ( jQuery( '#yendif-video-share-related-item-' + item.id ).length > 0  ) {
            continue;
        }

        html += '<tr id="yendif-video-share-related-item-' + item.id + '">';

        // Sortable handle
        html += '<td class="text-center d-none d-md-table-cell">' + 
            '<span class="sortable-handler">' + 
                '<span class="icon-ellipsis-v" aria-hidden="true"></span>' + 
            '</span>' + 
        '</td>';

        // Title & Category
        html += '<td>' + 
            item.title + 
            '<div class="small mt-1">' + yendif.i18n_category + ': ' + item.category + '</div>' + 
        '</td>';

        // User
        html += '<td class="small d-none d-md-table-cell">' + 
            item.user + 
        '</td>';

        // Access
        html += '<td class="small d-none d-md-table-cell">' + 
            item.access + 
        '</td>';

        // Views
        html += '<td class="text-center d-none d-md-table-cell">' + 
            '<span class="badge bg-info">' + item.views + '</span>' + 
        '</td>';

        // Featured
        html += '<td class="text-center d-none d-md-table-cell">' + 
            '<span class="tbody-icon"><span class="icon-' + ( item.featured ? 'publish' : 'unpublish' ) + '" aria-hidden="true"></span></span>' + 
        '</td>';

        // State
        html += '<td class="text-center">' + 
            '<span class="tbody-icon"><span class="icon-' + ( item.state ? 'publish' : 'unpublish' ) + '" aria-hidden="true"></span></span>' + 
        '</td>';

        // ID
        html += '<td class="text-center d-none d-md-table-cell">' + 
            + item.id + 
            '<input type="hidden" name="jform[related][]" value="' + item.id + '" />' + 
        '</td>';

        // Remove
        html += '<td class="text-center">' + 
            '<span class="tbody-icon">' + 
                '<a href="javascript: void(0);" class="yendif-video-share-related-item-remove">' + 
                    '<span class="icon-delete" aria-hidden="true"></span>' + 
                '</a>' + 
            '</span>' + 
        '</td>';

        html += '</tr>';
    }

    jQuery( '#yendif-video-share-related-empty-note' ).addClass( 'hidden' );
    jQuery( '#yendif-video-share-related-items' ).append( html );
}