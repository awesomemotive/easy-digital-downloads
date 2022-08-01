/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {
	const emails_wrap = $( '.edd-order-resend-receipt-addresses' );

	$( document.body ).on( 'click', '#edd-select-receipt-email', function( e ) {
		e.preventDefault();
		emails_wrap.slideDown();
	} );

	$( document.body ).on( 'change', '.edd-order-resend-receipt-email', function() {
		const selected = $('input:radio.edd-order-resend-receipt-email:checked').val();

		$( '#edd-select-receipt-email').data( 'email', selected );
	} );

	$( document.body).on( 'click', '#edd-select-receipt-email', function () {
		if ( confirm( edd_vars.resend_receipt ) ) {
			const href = $( this ).prop( 'href' ) + '&email=' + $( this ).data( 'email' );
			window.location = href;
		}
	} );

	$( document.body ).on( 'click', '#edd-resend-receipt', function() {
		return confirm( edd_vars.resend_receipt );
	} );
} );
