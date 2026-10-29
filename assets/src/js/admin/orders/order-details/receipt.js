/* global edd_vars */

const sendEmailButton = document.getElementById( 'edd-resend-receipt' );

// Nothing to do if there's no button or the button has no href (disabled state).
if ( sendEmailButton?.getAttribute( 'href' ) ) {
	const emailSelectSelector = '.edd-order-resend-receipt-email';
	const url = new URLSearchParams( sendEmailButton.getAttribute( 'href' ) );

	document.body.addEventListener( 'change', ( e ) => {
		if ( ! e.target.matches( emailSelectSelector ) ) {
			return;
		}
		url.set( 'email', e.target.value );
		sendEmailButton.setAttribute( 'href', decodeURIComponent( url.toString() ) );
	} );

	// Trigger initial value to be set.
	document.querySelector( emailSelectSelector )?.dispatchEvent( new Event( 'change', { bubbles: true } ) );

	sendEmailButton.addEventListener( 'click', ( e ) => {
		if ( ! confirm( edd_vars.resend_receipt ) ) { // eslint-disable-line no-alert
			e.preventDefault();
		}
	} );
}
