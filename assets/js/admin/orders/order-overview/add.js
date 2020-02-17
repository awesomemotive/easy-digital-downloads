/* global $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';
import { reindex } from './index.js';
import { updateAmounts } from './../order-amounts';

jQueryReady( () => {
	const button = $( '.edd-add-order-item-button' );

	// Toggle form.
	$( '#edd-order-items' ).on( 'click', 'h3 .edd-metabox-title-action', function( e ) {
		e.preventDefault();
		$( '#edd-order-items' ).children( '.edd-add-download-to-purchase' ).slideToggle();
	} );

	button.prop( 'disabled', 'disabled' );

	$( '.edd-order-add-download-select' ).on( 'change', function() {
		button.removeAttr( 'disabled' );
	} );

	// Add item.
	button.on( 'click', function( e ) {
		e.preventDefault();

		const select = $( '.edd-order-add-download-select' ),
			spinner = $( '.edd-add-download-to-purchase .spinner' ),
			data = {
				action: 'edd_add_order_item',
				nonce: $( '#edd_add_order_nonce' ).val(),
				country: $( '.edd-order-address-country' ).val(),
				region: $( '.edd-order-address-region' ).val(),
				download: select.val(),
				quantity: $( '.edd-add-order-quantity' ).val(),
				editable: $( 'input[name="edd-order-download-is-overrideable"]' ).val(),
			};

		spinner.css( 'visibility', 'visible' );

		$.post( ajaxurl, data, function( response ) {
			const { success, data } = response;

			if ( ! success ) {
				return;
			}

			$( '.orderitems .no-items' ).hide();
			$( '.orderitems tbody' ).append( data.html );

			// @todo attach to edd-admin-add-order-download trigger in /order-amounts
			updateAmounts();
			reindex();

			spinner.css( 'visibility', 'hidden' );

			// Let other things happen. jQuery event for now.
			$( document ).trigger( 'edd-admin-add-order-download', response );
		}, 'json' );
	} );
} );
