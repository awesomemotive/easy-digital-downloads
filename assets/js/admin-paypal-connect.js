jQuery( document ).ready( function ( $ ) {
	$( '#edd-paypal-commerce-connect' ).on( 'click', function ( e ) {
		e.preventDefault();

		// Clear errors.
		const errorContainer = $( '#edd-paypal-commerce-errors' );
		errorContainer.empty().removeClass( 'notice notice-error' );

		// @todo start spinner
		const button = $( this );
		button.prop( 'disabled', true );

		$.post( ajaxurl, {
			action: 'edd_paypal_commerce_connect',
			_ajax_nonce: $( this ).data( 'nonce' )
		}, function( response ) {
			if ( ! response.success ) {
				console.log( 'Connection failure', response.data );
				// @todo end spinner
				button.prop( 'disabled', false );

				// Set errors.
				errorContainer.html( '<p>' + response.data + '</p>' ).addClass( 'notice notice-error' );
				return;
			}

			console.log( 'Success' );

			const paypalLinkEl = document.getElementById( 'edd-paypal-commerce-link' );
			paypalLinkEl.href = response.data.signupLink + '&displayMode=minibrowser';

			//const paypalLinkEl = $( '#edd-paypal-commerce-link' );
			//paypalLinkEl.attr( 'href', response.data.signupLink + '&displayMode=minibrowser' );
			paypalLinkEl.click();
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
		if ( ! response.success ) {
			connectButton.disabled = false;

			errorContainer.innerHTML = '<p>' + response.data + '</p>';
			errorContainer.classList.add( 'notice notice-error' );
			return;
		}

		console.log( 'Success' );
	} );
}
