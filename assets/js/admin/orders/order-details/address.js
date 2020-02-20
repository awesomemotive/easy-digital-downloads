/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { getChosenVars } from 'utils/chosen.js';
import { jQueryReady } from 'utils/jquery.js';

// Store customer search results to help prefill address data.
let CUSTOMER_SEARCH_RESULTS = {};

jQueryReady( () => {

	// Update base state field based on selected base country
	$( 'select[name="edd-payment-address[0][country]"]' ).change( function() {
		const select = $( this ),
			data = {
				action: 'edd_get_shop_states',
				country: select.val(),
				nonce: select.data( 'nonce' ),
				field_name: 'edd-payment-address[0][region]',
			};

		$.post( ajaxurl, data, function( response ) {
			const state_wrapper = $( '#edd-order-address-state-wrap select, #edd-order-address-state-wrap input' );

			// Remove any chosen containers here too
			$( '#edd-order-address-state-wrap .chosen-container' ).remove();

			if ( 'nostates' === response ) {
				state_wrapper.replaceWith( '<input type="text" name="edd-payment-address[0][region]" value="" class="edd-edit-toggles medium-text"/>' );
			} else {
				state_wrapper.replaceWith( response );
				$( '#edd-order-address-state-wrap select' ).each( function() {
					const el = $( this );
					el.chosen( getChosenVars( el ) );
				} );
			}
		} );

		return false;
	} );

	$( '.edd-payment-change-customer-input' ).on( 'change', function() {
		const $this = $( this ),
			data = {
				action: 'edd_customer_addresses',
				customer_id: $this.val(),
				nonce: $( '#edd_add_order_nonce' ).val(),
			};

		$.post( ajaxurl, data, function( response ) {
			const { success, data } = response;

			if ( ! success ) {
				$( '.customer-address-select-wrap' ).html( '' ).hide();

				return;
			}

			// Store response for later use.
			CUSTOMER_SEARCH_RESULTS = data;

			if ( data.html ) {
				$('.customer-address-select-wrap').html(data.html).show();
				$('.customer-address-select-wrap select').each(function () {
					const el = $(this);
					el.chosen(getChosenVars(el));
				});
			} else {
				$( '.customer-address-select-wrap' ).html( '' ).hide();
			}
		}, 'json' );

		return false;
	} );

	$( document.body ).on( 'change', '.customer-address-select-wrap .add-order-customer-address-select', function() {
		const $this = $( this ),
			val = $this.val(),
			select = $( '#edd-add-order-form select#edd_order_address_country' ),
			address = CUSTOMER_SEARCH_RESULTS.addresses[ val ];

		$( '#edd-add-order-form input[name="edd_order_address[address]"]' ).val( address.address );
		$( '#edd-add-order-form input[name="edd_order_address[address2]"]' ).val( address.address2 );
		$( '#edd-add-order-form input[name="edd_order_address[postal_code]"]' ).val( address.postal_code );
		$( '#edd-add-order-form input[name="edd_order_address[city]"]' ).val( address.city );
		select.val( address.country ).trigger( 'chosen:updated' );
		$( '#edd-add-order-form input[name="edd_order_address[address_id]"]' ).val( val );

		const data = {
			action: 'edd_get_shop_states',
			country: select.val(),
			nonce: $( '.add-order-customer-address-select' ).data( 'nonce' ),
			field_name: 'edd_order_address_region',
		};

		$.post( ajaxurl, data, function( response ) {
			$( 'select#edd_order_address_region' ).find( 'option:gt(0)' ).remove();

			if ( 'nostates' !== response ) {
				$( response ).find( 'option:gt(0)' ).appendTo( 'select#edd_order_address_region' );
			}

			$( 'select#edd_order_address_region' ).trigger( 'chosen:updated' );
			$( 'select#edd_order_address_region' ).val( address.region ).trigger( 'chosen:updated' );
		} );

		return false;
	} );

	// Country change.
	$( '.edd-order-address-country' ).on( 'change', function() {
		const select = $( this ),
			data = {
				action: 'edd_get_shop_states',
				country: select.val(),
				nonce: select.data( 'nonce' ),
				field_name: 'edd-order-address-country',
			};

		$.post( ajaxurl, data, function( response ) {
			$( 'select.edd-order-address-region' ).find( 'option:gt(0)' ).remove();

			if ( 'nostates' !== response ) {
				$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd-order-address-region' );
			}

			$( 'select.edd-order-address-region' ).trigger( 'chosen:updated' );
		} );
	} );

} );
