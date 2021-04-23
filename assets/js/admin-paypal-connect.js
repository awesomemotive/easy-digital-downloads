jQuery( document ).ready( function ( $ ) {
	$( '#edd-paypal-commerce-connect' ).on( 'click', function ( e ) {
		e.preventDefault();

		// @todo start spinner
		$.post( ajaxurl, {
			action: 'edd_paypal_commerce_connect'
		}, function( response ) {
			if ( ! response.success ) {
				console.log( 'Connection failure', response.data );
				// @todo end spinner, show error
				return;
			}

			const paypalLinkEl = $( '#edd-paypal-commerce-link' );
			paypalLinkEl.href = response.data.signupLink + '&displayMode=minibrowser';
			paypalLinkEl.click();
		} );
	} );
} );

function eddPayPalOnboardingCallback( authCode, shareId ) {
	jQuery.post( ajaxurl, {
		action: 'edd_paypal_commerce_get_access_token',
		auth_code: authCode,
		share_id: shareId
	} );
}
