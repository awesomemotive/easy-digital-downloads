/* global $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from '@easydigitaldownloads/utils';

jQueryReady( () => {

	// Change Customer.
	$( '.edd-payment-change-customer-input' ).on( 'change', function() {
		const $this = $( this ),
			data = {
				action: 'edd_customer_details',
				customer_id: $this.val(),
				nonce: $( '#edd_customer_details_nonce' ).val(),
			};

		if ( '' === data.customer_id ) {
			return;
		}

		$( '.customer-details' ).css( 'display', 'none' );
		$( '#customer-avatar' ).html( '<span class="spinner is-active"></span>' );

		$.post( ajaxurl, data, function( response ) {
			const { success, data } = response;

			if ( success ) {
				$( '.customer-details' ).css( 'display', 'flex' );
				$( '.customer-details-wrap' ).css( 'display', 'flex' );

				$( '#customer-avatar' ).html( data.avatar );
				$( '.customer-name' ).html( data.name );
				$( '.customer-since span' ).html( data.date_created_i18n );
				$( '.customer-record a' ).prop( 'href', data._links.self );
			} else {
				$( '.customer-details-wrap' ).css( 'display', 'none' );
			}
		}, 'json' );
	} );

	$( '.edd-payment-change-customer-input' ).trigger( 'change' );

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
