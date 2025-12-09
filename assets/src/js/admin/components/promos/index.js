/* global ajaxurl */

jQuery( document ).ready( function( $ ) {
	/**
	 * Show overlay notices on a delay.
	 */
	const overlayNotices = $( '.edd-admin-notice-overlay' );

	if ( overlayNotices.length ) {
		// Wrap each overlay notice and store reference to its notice ID
		overlayNotices.each( function() {
			const overlayNotice = $( this );
			overlayNotice.wrap( '<div class="edd-promo-notice__overlay"></div>' );

			// Get the notice ID from the inner .edd-promo-notice element
			const noticeId = overlayNotice.data( 'id' );
			if ( noticeId ) {
				overlayNotice.parent().attr( 'data-notice-id', noticeId );
			}
		} );

	$( document ).on( 'click', '.edd-promo-notice__trigger', function ( e ) {
		e.preventDefault();

		const noticeId = $( this ).data( 'id' );
		// If no notice ID, assume there's only one overlay on the page
		const targetOverlay = noticeId
			? $( '.edd-promo-notice__overlay[data-notice-id="' + noticeId + '"]' )
			: $( '.edd-promo-notice__overlay' ).first();

		if ( ! targetOverlay.length ) {
			return;
		}

			if ( $( this ).hasClass( 'edd-promo-notice__trigger--ajax' ) ) {
				const overlayNotice = targetOverlay.find( '.edd-admin-notice-overlay' );
				$.ajax( {
					type: 'GET',
					url: ajaxurl,
					data: {
						action: 'edd_get_promo_notice',
						notice_id: noticeId,
						product_id: $( this ).data( 'product' ),
						value: $( this ).data( 'value' ),
					},
					success: function ( response ) {
						if ( response.data ) {
							overlayNotice.html( response.data );
							// add a class to the overlay notice
							targetOverlay.addClass( 'edd-promo-notice__ajax' );
						}
						triggerNoticeEnter( targetOverlay );
					}
				} );
			} else {
				triggerNoticeEnter( targetOverlay );
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

			// Find the overlay wrapper if this notice is in one
			const overlayWrapper = notice.closest( '.edd-promo-notice__overlay' );
			const noticeToClose = overlayWrapper.length ? overlayWrapper : notice;

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
					triggerNoticeDismiss( noticeToClose );
				}
			} );
		} );
	} );

	// Close overlay on Escape key
	$( document ).on( 'keydown', function ( event ) {
		if ( 27 === event.keyCode ) {
			const visibleOverlay = $( '.edd-promo-notice__overlay:visible' );
			if ( visibleOverlay.length ) {
				triggerNoticeDismiss( visibleOverlay );
			}
		}
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

		if ( el.hasClass( 'edd-promo-notice__overlay' ) ) {
			el.fadeOut();
			$( '.edd-extension-manager__key-notice' ).hide();
		} else {
			el.slideUp( 400, function () {
				$( this ).addClass( 'edd-hidden' );
			} );
		}

		// trigger native custom event as jQuery and Vanilla JS both can listen to it.
		document.dispatchEvent( new CustomEvent( 'edd_promo_notice_dismiss', { detail: { notice: el } } ) );
	}
} );
