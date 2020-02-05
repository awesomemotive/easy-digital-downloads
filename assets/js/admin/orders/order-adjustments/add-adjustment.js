/* global $ */

/**
 * Internal dependencies
 */
import { reindex } from './index.js';
import { jQueryReady } from 'utils/jquery.js';
import { updateAmounts } from './../order-amounts';

jQueryReady( () => {

	// Toggle form.
	$( '#edd-order-adjustments' ).on( 'click', 'h3 .edd-metabox-title-action', function( e ) {
		e.preventDefault();
		$( '#edd-order-adjustments' ).children( '.edd-add-adjustment-to-purchase' ).slideToggle();
	} );

	$( '.edd-order-add-adjustment-select' ).on( 'change', function() {
		const type = $( this ).val();

		$( '.edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.discount, .edd-add-adjustment-to-purchase li.fee, .edd-add-adjustment-to-purchase li.credit' ).hide();

		$( '.' + type, '.edd-add-adjustment-to-purchase' ).show();
	} );

	$( '.edd-add-order-adjustment-button' ).on( 'click', function( e ) {
		e.preventDefault();

		const data = {
			action: 'edd_add_adjustment_to_order',
			nonce: $( '#edd_add_order_nonce' ).val(),
			type: $( '.edd-order-add-adjustment-select' ).val(),
			adjustment_data: {
				fee: $( '.edd-order-add-fee-select' ).val(),
				discount: $( '.edd-order-add-discount-select' ).val(),
				credit: {
					description: $( '.edd-add-order-credit-description' ).val(),
					amount: $( '.edd-add-order-credit-amount' ).val(),
				},
			},
		},
			spinner = $( '.edd-add-adjustment-to-purchase .spinner' );

		spinner.css( 'visibility', 'visible' );

		$.post( ajaxurl, data, function( response ) {
			const { success, data } = response;

			if ( ! success ) {
				return;
			}

			$( '.orderadjustments .no-items' ).hide();
			$( '.orderadjustments tbody' ).append( data.html );

			updateAmounts();
			reindex();

			spinner.css( 'visibility', 'hidden' );

			// Let other things happen. jQuery event for now.
			$( document ).trigger( 'edd-admin-add-order-adjustment', response );
		}, 'json' );
	} );
} );
