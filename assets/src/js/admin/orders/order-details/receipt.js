/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {

	const sendEmailButton = $( '#edd-resend-receipt' );
	// If the button is disabled, do nothing.
	if ( ! sendEmailButton.attr( 'href' ) ) {
		return;
	}
	const emailSelectSelector = '.edd-order-resend-receipt-email';
	const url = new URLSearchParams( sendEmailButton.attr( 'href' ) );

	$( document.body ).on( 'change', emailSelectSelector, function() {
		url.set( 'email', $( this ).val() );
		sendEmailButton.attr( 'href', decodeURIComponent( url.toString() ) );
	} );

	// trigger initial value to be set on change
	$( emailSelectSelector ).trigger( 'change' );

	// confirm before sending
	sendEmailButton.on( 'click', function() {
		return confirm( edd_vars.resend_receipt );
	});
} );
