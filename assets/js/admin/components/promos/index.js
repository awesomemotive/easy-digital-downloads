/* global ajaxurl */

jQuery( document ).ready( function( $ ) {

	/**
	 * Show overlay notices on a delay.
	 */
	const overlayNotice = $( '.edd-admin-notice-overlay' );
	const overlayNoticeClass = 'edd-promo-notice__overlay';
	if ( overlayNotice ) {
		overlayNotice.wrap( '<div class="' + overlayNoticeClass + '"></div>' );
		$( document ).on( 'click', '.edd-promo-notice__trigger', function () {
			$( '.' + overlayNoticeClass ).css( 'display', 'flex' ).hide().fadeIn();
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
					if ( $( '.' + overlayNoticeClass ).length ) {
						$( '.' + overlayNoticeClass ).fadeOut();
						$( '.edd-extension-manager__key-notice' ).hide();
					} else {
						notice.slideUp();
					}
				}
			} );
		} );

		$( document ).on( 'keydown', function ( event ) {
			if ( !$( '.' + overlayNoticeClass ).length ) {
				return;
			}
			if ( 27 === event.keyCode ) {
				$( '.' + overlayNoticeClass ).fadeOut();
				$( '.edd-extension-manager__key-notice' ).hide();
			}
		} );
	} );
} );
