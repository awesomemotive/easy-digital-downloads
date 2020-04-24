/* global $, ajaxurl */

/**
 * Internal dependencies
 */
import { getChosenVars } from 'utils/chosen.js';
import { jQueryReady } from 'utils/jquery.js';

// Store customer search results to help prefill address data.
let CUSTOMER_SEARCH_RESULTS = {};

jQueryReady( () => {

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

	/**
	 * Retrieves a list of states based on a Country HTML <select>.
	 *
	 * @since 3.0
	 *
	 * @param {HTMLElement} countryEl Element containing country information.
	 * @param {string} fieldName the name of the field to use in response.
	 * @return {$.promise} Region data response.
	 */
	function getStates( countryEl, fieldName ) {
		const data = {
			action: 'edd_get_shop_states',
			country: countryEl.val(),
			nonce: countryEl.data( 'nonce' ),
			field_name: fieldName,
		};

		return $.post( ajaxurl, data );
	}

	/**
	 * Replaces the Region area with the appropriate field type.
	 *
	 * @todo This is hacky and blindly picks elements from the DOM.
	 *
	 * @since 3.0
	 *
	 * @param {string} regions Regions response.
	 */
	function replaceRegionField( regions ) {
		const state_wrapper = $( '#edd-order-address-state-wrap select, #edd-order-address-state-wrap input' );

		// Remove any chosen containers here too
		$( '#edd-order-address-state-wrap .chosen-container' ).remove();

		if ( 'nostates' === regions ) {
			state_wrapper
				.replaceWith( '<input type="text" name="edd_order_address[region]" id="edd_order_address_region" value="" class="wide-fat" style="max-width: none; width: 100%;" />' );
		} else {
			state_wrapper.replaceWith( regions );

			$( '#edd-order-address-state-wrap select' ).each( function() {
				const el = $( this );
				el.chosen( getChosenVars( el ) );
			} );
		}
	}

	/**
	 * Handles replacing a Region field when a Country field changes.
	 *
	 * @since 3.0
	 */
	function updateRegionFieldOnChange() {
		getStates( $( this ), 'edd_order_address_region' ).done( replaceRegionField );
	}

	$( document.body ).on( 'change', '.customer-address-select-wrap .add-order-customer-address-select', function() {
		const $this = $( this ),
			val = $this.val(),
			address = CUSTOMER_SEARCH_RESULTS.addresses[ val ];

		$( '#edd-add-order-form input[name="edd_order_address[address]"]' ).val( address.address );
		$( '#edd-add-order-form input[name="edd_order_address[address2]"]' ).val( address.address2 );
		$( '#edd-add-order-form input[name="edd_order_address[postal_code]"]' ).val( address.postal_code );
		$( '#edd-add-order-form input[name="edd_order_address[city]"]' ).val( address.city );
		$( '#edd-add-order-form input[name="edd_order_address[address_id]"]' ).val( val );

		// Remove global `change` event handling to prevent loop.
		$( '.edd-order-address-country' ).off( 'change', updateRegionFieldOnChange );

		// Set Country.
		$( '#edd-add-order-form select#edd_order_address_country' )
			.val( address.country )
			.trigger( 'change' )
			.trigger( 'chosen:updated' );

		// Set Region.
		getStates( $( '#edd-add-order-form select#edd_order_address_country' ), 'edd_order_address[region]' )
			.done( replaceRegionField )
			.done( ( response ) => {
				$( '[name="edd_order_address[region]"]' )
					.val( address.region )
					.trigger( 'change' )
					.trigger( 'chosen:updated' );
			} );

		// Add back global `change` event handling.
		$( '.edd-order-address-country' ).on( 'change', updateRegionFieldOnChange );

		return false;
	} );

	// Country change.
	$( '.edd-order-address-country' ).on( 'change', updateRegionFieldOnChange );

} );
