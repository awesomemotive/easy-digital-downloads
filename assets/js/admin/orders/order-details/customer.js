/* global $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';

jQueryReady( () => {

	// Change Customer.
	$( '#edd-customer-details' ).on( 'click', '.edd-payment-change-customer, .edd-payment-change-customer-cancel', function( e ) {
		e.preventDefault();

		const change_customer = $( this ).hasClass( 'edd-payment-change-customer' ),
			cancel = $( this ).hasClass( 'edd-payment-change-customer-cancel' );

		if ( change_customer ) {
			$( '.order-customer-info' ).hide();
			$( '.change-customer' ).show();
			setTimeout( function() {
				$( '.edd-payment-change-customer-input' ).css( 'width', '300' );
			}, 1 );
		} else if ( cancel ) {
			$( '.order-customer-info' ).show();
			$( '.change-customer' ).hide();
		}
	} );

	// New Customer.
	$( '#edd-customer-details' ).on( 'click', '.edd-payment-new-customer, .edd-payment-new-customer-cancel', function( e ) {
		e.preventDefault();

		var new_customer = $( this ).hasClass( 'edd-payment-new-customer' ),
			cancel = $( this ).hasClass( 'edd-payment-new-customer-cancel' );

		if ( new_customer ) {
			$( '.order-customer-info' ).hide();
			$( '.new-customer' ).show();
		} else if ( cancel ) {
			$( '.order-customer-info' ).show();
			$( '.new-customer' ).hide();
		}

		var new_customer = $( '#edd-new-customer' );

		if ( $( '.new-customer' ).is( ':visible' ) ) {
			new_customer.val( 1 );
		} else {
			new_customer.val( 0 );
		}
	} );

} );
