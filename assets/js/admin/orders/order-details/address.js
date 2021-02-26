/* global $, ajaxurl, _ */

/**
 * Internal dependencies
 */
import { jQueryReady, getChosenVars } from '@easydigitaldownloads/utils';
import OrderOverview from './../order-overview';

// Store customer search results to help prefill address data.
let CUSTOMER_SEARCH_RESULTS = {
	addresses: {
		'0': {
			address: '',
			address2: '',
			city: '',
			region: '',
			postal_code: '',
			country: '',
		},
	},
};

jQueryReady( () => {

	/**
	 * Adjusts Overview tax configuration when the Customer's address changes.
	 *
	 * @since 3.0
	 */
	( () => {
		const { state: overviewState } = OrderOverview.options;

		// No tax, do nothing.
		if ( false === overviewState.get( 'hasTax' ) ) {
			return;
		}

		// Editing, do nothing.
		if ( false === overviewState.get( 'isAdding' ) ) {
			return;
		}

		const countryInput = document.getElementById(
			'edd_order_address_country'
		);
		const regionInput = document.getElementById(
			'edd_order_address_region'
		);

		if ( ! ( countryInput && regionInput ) ) {
			return;
		}

		/**
		 * Retrieves a tax rate based on the currently selected Address.
		 *
		 * @since 3.0
		 */
		function getTaxRate() {
			const country = $( '#edd_order_address_country' ).val();
			const region = $( '#edd_order_address_region' ).val();

			const nonce = document.getElementById( 'edd_get_tax_rate_nonce' )
				.value;

			wp.ajax.send( 'edd_get_tax_rate', {
				data: {
					nonce,
					country,
					region,
				},
				/**
				 * Updates the Overview's tax configuration on successful retrieval.
				 *
				 * @since 3.0
				 *
				 * @param {Object} response AJAX response.
				 */
				success( response ) {
					let { tax_rate: rate } = response;

					// Make a percentage.
					rate = rate * 100;

					overviewState.set( 'hasTax', {
						country,
						region,
						rate,
					} );
				},
				/*
				 * Updates the Overview's tax configuration on failed retrieval.
				 *
				 * @since 3.0
				 */
				error() {
					overviewState.set( 'hasTax', 'none' );
				},
			} );
		}

		// Update rate on Address change.
		//
		// Wait for Region field to be replaced when Country changes.
		// Wait for typing when Regino field changes.
		// jQuery listeners for Chosen compatibility.
		$( '#edd_order_address_country' ).on( 'change', _.debounce( getTaxRate, 250 ) );

		$( '#edd-order-address' ).on( 'change', '#edd_order_address_region', getTaxRate );
		$( '#edd-order-address' ).on( 'keyup', '#edd_order_address_region', _.debounce( getTaxRate, 250 ) );
	} )();

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
				$( '.customer-address-select-wrap' ).hide();

				return;
			}

			// Store response for later use.
			CUSTOMER_SEARCH_RESULTS = {
				...CUSTOMER_SEARCH_RESULTS,
				...data,
				addresses: {
					...CUSTOMER_SEARCH_RESULTS.addresses,
					...data.addresses,
				},
			};

			if ( data.html ) {
				$( '.customer-address-select-wrap' ).show();
				$( '.customer-address-select-wrap .edd-form-group__control' ).html( data.html );
			} else {
				$( '.customer-address-select-wrap' ).hide();
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
	function getStates( countryEl, fieldName, fieldId ) {
		const data = {
			action: 'edd_get_shop_states',
			country: countryEl.val(),
			nonce: countryEl.data( 'nonce' ),
			field_name: fieldName,
			field_id: fieldId,
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
		const state_wrapper = $( '#edd_order_address_region' );

		$( '#edd_order_address_region_chosen' ).remove();

		if ( 'nostates' === regions ) {
			state_wrapper
				.replaceWith( '<input type="text" name="edd_order_address[region]" id="edd_order_address_region" value="" class="wide-fat" style="max-width: none; width: 100%;" />' );
		} else {
			state_wrapper
				.replaceWith( regions );

			$( '#edd_order_address_region' ).chosen( getChosenVars( $( '#edd_order_address_region' ) ) );
		}
	}

	/**
	 * Handles replacing a Region field when a Country field changes.
	 *
	 * @since 3.0
	 */
	function updateRegionFieldOnChange() {
		getStates(
			$( this ),
			'edd_order_address[region]',
			'edd_order_address_region'
		)
			.done( replaceRegionField );
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
		$( '#edd_order_address_country' ).off( 'change', updateRegionFieldOnChange );

		// Set Country.
		$( '#edd_order_address_country' )
			.val( address.country )
			.trigger( 'change' )
			.trigger( 'chosen:updated' );

		// Set Region.
		getStates(
			$( '#edd_order_address_country' ),
			'edd_order_address[region]',
			'edd_order_address_region'
		)
			.done( replaceRegionField )
			.done( ( response ) => {
				$( '#edd_order_address_region' )
					.val( address.region )
					.trigger( 'change' )
					.trigger( 'chosen:updated' );
			} );

		// Add back global `change` event handling.
		$( '#edd_order_address_country' ).on( 'change', updateRegionFieldOnChange );

		return false;
	} );

	// Country change.
	$( '#edd_order_address_country' ).on( 'change', updateRegionFieldOnChange );

} );
