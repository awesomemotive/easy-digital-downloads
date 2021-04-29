jQuery( document ).ready( function ( $ ) {
	/**
	 * Connect to PayPal
	 */
	$( '#edd-paypal-commerce-connect' ).on( 'click', function ( e ) {
		e.preventDefault();

		// Clear errors.
		const errorContainer = $( '#edd-paypal-commerce-errors' );
		errorContainer.empty().removeClass( 'notice notice-error' );

		const button = document.getElementById( 'edd-paypal-commerce-connect' );
		button.classList.add( 'updating-message' );
		button.disabled = true;

		$.post( ajaxurl, {
			action: 'edd_paypal_commerce_connect',
			_ajax_nonce: $( this ).data( 'nonce' )
		}, function( response ) {
			if ( ! response.success ) {
				console.log( 'Connection failure', response.data );
				button.classList.remove( 'updating-message' );
				button.disabled = false;

				// Set errors.
				errorContainer.html( '<p>' + response.data + '</p>' ).addClass( 'notice notice-error' );
				return;
			}

			const paypalLinkEl = document.getElementById( 'edd-paypal-commerce-link' );
			paypalLinkEl.href = response.data.signupLink + '&displayMode=minibrowser';

			paypalLinkEl.click();
		} );
	} );

	/**
	 * Checks the PayPal connection & webhook status.
	 */
	function eddPayPalGetAccountStatus() {
		const accountInfoEl = document.getElementById( 'edd-paypal-commerce-connect-wrap' );
		if ( accountInfoEl ) {
			$.post( ajaxurl, {
				action: 'edd_paypal_commerce_get_account_info',
				_ajax_nonce: accountInfoEl.getAttribute( 'data-nonce' )
			}, function( response ) {
				let newHtml = response.data.account_status + response.data.webhook_status;

				if ( response.data.actions && response.data.actions.length ) {
					newHtml += '<p class="edd-paypal-connect-actions">' + response.data.actions.join() + '</p>';
				}

				accountInfoEl.innerHTML = newHtml;

				// Remove old status messages.
				accountInfoEl.classList.remove( 'notice-success', 'notice-warning', 'notice-error' );

				// Add new one.
				const newClass = response.success && response.data.status ? 'notice-' + response.data.status : 'notice-error';
				accountInfoEl.classList.add( newClass );
			} );
		}
	}
	eddPayPalGetAccountStatus();

	/**
	 * Create webhook
	 */
	$( document ).on( 'click', '.edd-paypal-connect-action', function ( e ) {
		e.preventDefault();

		const button = $( this );
		button.prop( 'disabled', true );
		button.addClass( 'updating-message' );

		const errorWrap = button.closest( '.edd-paypal-actions-error-wrap' );
		if ( errorWrap.length ) {
			errorWrap.remove();
		}

		$.post( ajaxurl, {
			action: button.data( 'action' ),
			_ajax_nonce: button.data( 'nonce' )
		}, function( response ) {
			button.prop( 'disabled', false );
			button.removeClass( 'updating-message' );

			if ( response.success ) {
				button.addClass( 'updated-message' );

				// Refresh account status.
				eddPayPalGetAccountStatus();
			} else {
				button.parent().after( '<div class="edd-paypal-actions-error-wrap">' + response.data + '</div>' );
			}
		} );
	} );
} );

function eddPayPalOnboardingCallback( authCode, shareId ) {
	const connectButton = document.getElementById( 'edd-paypal-commerce-connect' );
	const errorContainer = document.getElementById( 'edd-paypal-commerce-errors' );

	jQuery.post( ajaxurl, {
		action: 'edd_paypal_commerce_get_access_token',
		auth_code: authCode,
		share_id: shareId,
		_ajax_nonce: connectButton.getAttribute( 'data-nonce' )
	}, function( response ) {
		connectButton.classList.remove( 'updating-message' );

		if ( ! response.success ) {
			connectButton.disabled = false;

			errorContainer.innerHTML = '<p>' + response.data + '</p>';
			errorContainer.classList.add( 'notice notice-error' );
			return;
		}

		connectButton.classList.add( 'updated-message' );

		window.location.reload();
	} );
}
