/* global ajaxurl */

jQuery( document ).ready( function( $ ) {

	/**
	 * Show overlay notices on a delay.
	 */
	const overlayNotice = $( '.edd-admin-notice-overlay' );
	let overlayNoticeWrapper = $(); // empty jQuery object, so chaining still works

	if ( overlayNotice ) {
		overlayNotice.wrap( '<div class="edd-promo-notice__overlay"></div>' );
		overlayNoticeWrapper = overlayNotice.parent();

		$( document ).on( 'click', '.edd-promo-notice__trigger', function () {
			if ( $( this ).hasClass( 'edd-promo-notice__trigger--ajax' ) ) {
				$.ajax( {
					type: 'GET',
					url: ajaxurl,
					data: {
						action: 'edd_get_promo_notice',
						notice_id: $( this ).data( 'id' ),
						product_id: $( this ).data( 'product' ),
						value: $( this ).data( 'value' ),
					},
					success: function ( response ) {
						if ( response.data ) {
							overlayNotice.html( response.data );
							// add a class to the overlay notice
							overlayNoticeWrapper.addClass( 'edd-promo-notice__ajax' );
						}
						triggerNoticeEnter( overlayNoticeWrapper );
					}
				} );
			} else {
				triggerNoticeEnter( overlayNoticeWrapper );
			}
		} );
	}

	/**
	 * Dismiss notices
	 */
	$( '.edd-promo-notice' ).each( function() {
		const notice = $( this );

		notice.on( 'click', '.edd-promo-notice-dismiss', function( e ) {
			// Only prevent default behavior for buttons, not links.
			if ( ! $( this ).attr( 'href' ) ) {
				e.preventDefault();
			}

			$.ajax( {
				type: 'POST',
				data: {
					action: 'edd_dismiss_promo_notice',
					notice_id: notice.data( 'id' ),
					nonce: notice.data( 'nonce' ),
					lifespan: notice.data( 'lifespan' )
				},
				url: ajaxurl,
				success: function( response ) {
					triggerNoticeDismiss( overlayNoticeWrapper.length ? overlayNoticeWrapper : notice );
				}
			} );
		} );

		$( document ).on( 'keydown', function ( event ) {
			if ( !overlayNoticeWrapper.length ) {
				return;
			}
			if ( 27 === event.keyCode ) {
				triggerNoticeDismiss( overlayNoticeWrapper );
			}
		} );
	} );

	/**
	 * Show notice and trigger event
	 *
	 * @param {jQuery} el The notice element to show
	 */
	function triggerNoticeEnter( el ) {
		// trigger native custom event as jQuery and Vanilla JS both can listen to it.
		document.dispatchEvent( new CustomEvent( 'edd_promo_notice_enter', { detail: { notice: el } } ) );

		el.css( 'display', 'flex' ).hide().fadeIn();
	}

	/**
	 * Dismiss notice and trigger event
	 *
	 * @param {jQuery} el The notice element to dismiss
	 */
	function triggerNoticeDismiss( el ) {
		if ( ! el.is( ':visible' ) ) {
			return;
		}

		if ( el.is( overlayNoticeWrapper ) ) {
			el.fadeOut();
			$( '.edd-extension-manager__key-notice' ).hide();
		} else {
			el.slideUp();
		}

		// trigger native custom event as jQuery and Vanilla JS both can listen to it.
		document.dispatchEvent( new CustomEvent( 'edd_promo_notice_dismiss', { detail: { notice: el } } ) );
	}
} );
