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

		const $trigger = $( this );

		// Prevent double-clicks or opening multiple modals.
		if ( $trigger.prop( 'disabled' ) ) {
			return;
		}

		const noticeId = $trigger.data( 'id' );
		// If no notice ID, assume there's only one overlay on the page
		const targetOverlay = noticeId
			? $( '.edd-promo-notice__overlay[data-notice-id="' + noticeId + '"]' )
			: $( '.edd-promo-notice__overlay' ).first();

		if ( ! targetOverlay.length ) {
			return;
		}

		// Disable the trigger button while modal is open.
		$trigger.prop( 'disabled', true );

		// Store reference to trigger on the overlay so we can re-enable it on dismiss.
		targetOverlay.data( 'trigger-button', $trigger );

			if ( $trigger.hasClass( 'edd-promo-notice__trigger--ajax' ) ) {
				const overlayNotice = targetOverlay.find( '.edd-admin-notice-overlay' );
				$.ajax( {
					type: 'GET',
					url: ajaxurl,
					data: {
						action: 'edd_get_promo_notice',
						notice_id: noticeId,
						product_id: $trigger.data( 'product' ),
						value: $trigger.data( 'value' ),
					},
					success: function ( response ) {
						if ( response.data ) {
							// Handle both new object format and legacy string format.
							const content = response.data.content || response.data;
							const borderColor = response.data.border_color || '';

							overlayNotice.html( content );
							// Add a class to the overlay notice.
							targetOverlay.addClass( 'edd-promo-notice__ajax' );

							// Store border color on overlay for triggerNoticeEnter to use.
							if ( borderColor ) {
								overlayNotice.data( 'ajax-border-color', borderColor );
							}
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
		// Apply custom border color if specified.
		const overlayNotice = el.find( '.edd-admin-notice-overlay' );
		const innerNotice = overlayNotice.find( '.edd-promo-notice' );

		// Check for border color from AJAX response first, then fall back to data attribute.
		const borderColor = overlayNotice.data( 'ajax-border-color' ) || innerNotice.data( 'border-color' );

		if ( borderColor ) {
			overlayNotice.css( 'border-top-color', borderColor );
		}

		// Trigger native custom event as jQuery and Vanilla JS both can listen to it.
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

		// Re-enable trigger button if one was stored.
		const $trigger = el.data( 'trigger-button' );
		if ( $trigger ) {
			$trigger.prop( 'disabled', false );
			el.removeData( 'trigger-button' );
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
