(function( $ ) {
	'use strict';	

	function toggleLikesDislikes( data) {
		var el = document.getElementById( 'yendif-video-share-likes-dislikes-widget' );
		el.innerHTML = '<span class="yendif-video-share-spinner"></span>';		

		var xmlhttp;
		
		if ( window.XMLHttpRequest ) {
			xmlhttp = new XMLHttpRequest();
		} else {
			xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
		};

		xmlhttp.onreadystatechange = function() {
			if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
				if ( xmlhttp.responseText ) {	
					el.innerHTML = xmlhttp.responseText;
				};
			};
		};	

		var url = yendif.baseurl + 'index.php?option=com_yendifvideoshare&view=video&task=video.vote&format=raw&id=' + data.id + '&like=' + data.like + '&dislike=' + data.dislike;
		xmlhttp.open( 'GET', url, true );
		xmlhttp.send();
	}

	/**
	 * Called when the page has loaded
	 *
	 * @since  2.0.0
	 */
	$(function() {
		// Magnific Popup 
		if ( $.fn.magnificPopup !== undefined ) {
			$( '.yendif-video-share-popup' ).each(function() {	
				var $this = $( this );
				var ratio = parseFloat( $this.data( 'player_ratio' ) );
				var uid   = -1;

				$this.magnificPopup({ 
					delegate: '.yendif-video-share-grid-item', // the selector for gallery item
					type: 'iframe',
					allowHTMLInStatusIndicator: true,
					allowHTMLInTemplate: true,
					closeOnBgClick: true,
					mainClass: 'mfp-fade',
					overflowY: 'auto',			
					removalDelay: 300,
					iframe: { // to create title, close, iframe, counter div's
						markup: '<div class="mfp-title-bar">' +
							'<div class="mfp-close" title="Close (Esc)"></div>' +
						'</div>' +							
						'<div class="mfp-iframe-scaler" style="padding-top:' + ratio + '%;" >' +            												
							'<iframe class="mfp-iframe" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' +																								
						'</div>'																							        			
					},	
					gallery: { // to build gallery				
						enabled: true													
					},
					callbacks: {
						open: function() {
							if ( uid > 0 ) return;

							var popup = $.magnificPopup.instance;	
							var src   = popup.currItem.src;			
							
							const url   = new URL( src );
        					const query = new URLSearchParams( url.search ); 

							if ( query.has( 'uid' ) ) {
								uid = parseInt( query.get( 'uid' ) );
							}
						}
					}									
				});

				// On Video Ended
				window.addEventListener( 'message', function( event ) {
					if ( event.data.message == 'ON_YENDIFVIDEOSHARE_ENDED' && event.data.id == uid ) {
						var popup = $.magnificPopup.instance;
						var loop  = event.data.loop;
				
						var totalVideos  = popup.items.length;
						var currentIndex = popup.index;					
	
						if ( currentIndex == totalVideos - 1 ) {
							if ( loop ) {
								popup.next();
							}
						} else {
							popup.next();
						}
					}
				}, false );
			});
		};

		$( '.yendif-video-share-popup' ).on( 'click', '.yendif-video-share-category a', function( event ) {
			event.stopPropagation();            
		});

		// Playlist
		$( '.mod-yendifvideoshare-playlist' ).each(function() {
			var $this = $( this );
			var uid   = parseInt( $this.attr( 'id' ).replace( 'yendif-video-share-playlist-', '' ) );

			$this.find( '.yendif-video-share-playlist-item' ).on( 'click', function( event ) {			
				var $container = $( this ).closest( '.yendif-video-share-playlist' );
	
				var src = $( this ).data( 'src' );
				$container.find( 'iframe' ).attr( 'src', src );
	
				$container.find( '.yendif-video-share-playlist-item' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
			});
	
			// On Video Ended
			window.addEventListener( 'message', function( event ) {
				if ( event.data.message == 'ON_YENDIFVIDEOSHARE_ENDED' && event.data.id == uid ) {
					var loop = event.data.loop;
			
					var totalVideos  = $this.find( '.yendif-video-share-playlist-item' ).length;
					var currentIndex = parseInt( $this.find( '.yendif-video-share-playlist-item.active' ).data( 'index' ) );					
					var nextIndex    = ++currentIndex;

					if ( totalVideos == nextIndex ) {
						if ( loop ) {
							nextIndex = 0;
						} else {
							nextIndex = -1;
						}
					}
	
					if ( nextIndex > -1 ) {
						$this.find( '.yendif-video-share-playlist-item' ).eq( nextIndex ).trigger( 'click' );
					}
				}
			}, false );
		});		

		// Ratings
		$( 'body' ).on( 'click', '.yendif-video-share-ratings-star a', function( e ) {
			e.preventDefault();
			
			var id = $( this ).data( 'id' ),
				rating = $( this ).data( 'value' );
		
			if ( yendif.allow_guest_rating == 0 && yendif.userid == 0 ) {
				alert( yendif.message_login_required );
				return false;
			};

			document.getElementById( 'yendif-video-share-ratings-widget' ).innerHTML = '<span class="yendif-video-share-spinner"></span>';
			var xmlhttp;

			if ( window.XMLHttpRequest ) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
			};

			xmlhttp.onreadystatechange = function() {
				if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
					if ( xmlhttp.responseText ) {		    
						document.getElementById( 'yendif-video-share-ratings-widget' ).innerHTML = xmlhttp.responseText;
					};
				};
			};	

			xmlhttp.open( 'GET', yendif.baseurl + 'index.php?option=com_yendifvideoshare&task=video.rating&format=raw&id=' + id + '&rating=' + rating, true );
			xmlhttp.send();
				
			return false;			
		});

		// Likes
		$( 'body' ).on( 'click', '.yendif-video-share-like-btn', function( e ) {
			e.preventDefault();
			
			if ( yendif.allow_guest_like == 0 && yendif.userid == 0 ) {
				alert( yendif.message_login_required );
				return false;
			}

			var $this = $( this );

			var data  = {
				id: $this.data( 'id' ),
				like: 0,
				dislike: 0
			};

			if ( ! $this.hasClass( 'active' ) ) {
				data.like = 1;
			}

			toggleLikesDislikes( data );

			return false;			
		});
		
		// Dislikes
		$( 'body' ).on( 'click', '.yendif-video-share-dislike-btn', function( e ) {
			e.preventDefault();
			
			if ( yendif.allow_guest_like == 0 && yendif.userid == 0 ) {
				alert( yendif.message_login_required );
				return false;
			}

			var $this = $( this );

			var data  = {
				id: $this.data( 'id' ),
				like: 0,
				dislike: 0
			};

			if ( ! $this.hasClass( 'active' ) ) {
				data.dislike = 1;
			}

			toggleLikesDislikes( data );
			
			return false;			
		});
	});

})( jQuery );