/* global $, ajaxurl, edd_order_statuses */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {
	const order_data_wrap = $( '.edd-order-data' );

	$( document.body ).on( 'change', '#edd_payment_status', function( e ) {
		e.preventDefault();

		let selected_status = $(this).val();
		let found_value     = edd_order_statuses.incomplete.indexOf( selected_status );
		console.log(found_value);
		if ( found_value >= 0 ) {
			$('.completed-date-wrapper').slideUp();
		} else {
			$('.completed-date-wrapper').slideDown();
		}
	} );
} );
