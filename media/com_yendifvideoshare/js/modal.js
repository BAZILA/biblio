(function( $ ) {
  'use strict';

  /**
   * Called when the page has loaded.
   *
   * @since 2.1.1
   */
  $(function() {   
        
    // Select Video Modal
    var elements = document.querySelectorAll( '.yendif-video-share-select-link' );

    for ( var i = 0, l = elements.length; l > i; i += 1 ) {
      elements[ i ].addEventListener( 'click', function( event ) {
        event.preventDefault();

        var functionName = event.target.getAttribute( 'data-function' );

        window.parent[ functionName ](
          event.target.getAttribute( 'data-id' ),
          event.target.getAttribute( 'data-title' ),
          null,
          null,
          event.target.getAttribute( 'data-uri' ),
          event.target.getAttribute( 'data-language' ),
          null
        );

        if ( window.parent.Joomla.Modal ) {
          window.parent.Joomla.Modal.getCurrent().close();
        };
      });
    };

    // Related Videos Modal
    if ( $( '#relatedVideosList' ).length > 0 ) {
      var videos = window.parent.yendif.params;
      
      for ( var i = 0; i < videos.length; i++ ) {
        var item = videos[ i ];
        $( '.form-check-input', '#yendif-video-share-related-item-' + item.id ).prop( 'checked', true );
      }

      $( '#relatedVideosList .form-check-input' ).each(function() {
        if ( $( this ).is( ':checked' ) ) {
          var params = $( this ).closest( 'tr' ).data( 'params' );
          videos.push( params );
        }
      });

      $( '.yendif-video-share-related-item' ).on( 'click', function() {
        var params = $( this ).closest( 'tr' ).data( 'params' );
        var videos = [ params ];
        
        window.parent.yendifInsertRelated( videos );

        if ( window.parent.Joomla.Modal ) {
          window.parent.Joomla.Modal.getCurrent().close();
        };
      }); 
      
      $( '#relatedVideosList input[name=checkall-toggle]' ).on( 'change', function() {
        var videos = [];

        if ( $( this ).is( ':checked' ) ) {
          $( '#relatedVideosList .form-check-input' ).each(function() {
            var params = $( this ).closest( 'tr' ).data( 'params' );
            videos.push( params );
          });
        }

        window.parent.yendifProcessRelated( videos );
      });

      $( '#relatedVideosList .form-check-input' ).on( 'change', function() {
        var videos = [];

        $( '#relatedVideosList .form-check-input' ).each(function() {
          if ( $( this ).is( ':checked' ) ) {
            var params = $( this ).closest( 'tr' ).data( 'params' );
            videos.push( params );
          }
        });

        window.parent.yendifProcessRelated( videos );
      });
    }
  });

})( jQuery );