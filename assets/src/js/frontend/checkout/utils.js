/* global edd_global_vars */

/**
 * Generate markup for a credit card icon based on a passed type.
 *
 * @param {string} type Credit card type.
 * @return HTML markup.
 */
export const getCreditCardIcon = ( type ) => {
	let width;
	let name = type;

	switch ( type ) {
		case 'amex':
			name = 'americanexpress';
			width = 32;
			break;
		default:
			width = 50;
			break;
	}

	return `
    <svg
      width=${ width }
      height=${ 32 }
      class="payment-icon icon-${ name }"
      role="img"
    >
      <use
        href="#icon-${ name }"
        xlink:href="#icon-${ name }">
      </use>
    </svg>`;
};

let ajax_tax_count = 0;

/**
 * Recalulate taxes.
 *
 * @param {string} state State to calculate taxes for.
 * @return {Promise}
 */
export function recalculateTaxes( state ) {
	if ( '1' != edd_global_vars.taxes_enabled ) {
		return;
	} // Taxes not enabled

	const cart = document.getElementById( 'edd_checkout_cart' );
	if ( ! cart ) {
		return;
	}

	let tax_amount_row = cart.getElementsByClassName( 'edd_cart_tax' );
	let current_tax_amount_raw = 0;

	// Capture current cart total for error restoration
	const cart_total_element = document.querySelector( '.edd_cart_amount' );
	const current_cart_total = cart_total_element ? cart_total_element.dataset.total || cart_total_element.textContent : 0;

	// See if the tax_amount_row has an edd-loading-ajax child before adding another one.
	if ( tax_amount_row.length > 0 && ! tax_amount_row[0].querySelector( '.edd-recalculate-taxes-loading' ) ) {
		tax_amount_row = tax_amount_row[0];
		const taxes_loading = document.createElement('span');
		const current_tax_amount = tax_amount_row.getElementsByClassName( 'edd_cart_tax_amount' );

		// Capture tax value from last element before hiding (maintains original behavior)
		for ( let i = 0; i < current_tax_amount.length; i++ ) {
			current_tax_amount_raw = current_tax_amount[ i ].dataset.tax;
			current_tax_amount[ i ].style.display = 'none';
		}

		taxes_loading.classList.add( 'edd-loading-ajax', 'edd-recalculate-taxes-loading', 'edd-loading' );
		tax_amount_row.appendChild( taxes_loading );
	}

	const $edd_cc_address = jQuery( '#edd_cc_address' );

	const billing_country = $edd_cc_address.find( '#billing_country' ).val(),
		card_address = $edd_cc_address.find( '#card_address' ).val(),
		card_address_2 = $edd_cc_address.find( '#card_address_2' ).val(),
		card_city = $edd_cc_address.find( '#card_city' ).val(),
		card_state = $edd_cc_address.find( '#card_state' ).val(),
		card_zip = $edd_cc_address.find( '#card_zip' ).val();

	if ( ! state ) {
		state = card_state;
	}

	const postData = {
		action: 'edd_recalculate_taxes',
		card_address: card_address,
		card_address_2: card_address_2,
		card_city: card_city,
		card_zip: card_zip,
		state: state,
		billing_country: billing_country,
		nonce: jQuery( '#edd-checkout-address-fields-nonce' ).val(),
		current_page: edd_global_vars.current_page,
		current_tax_amount: current_tax_amount_raw,
	};

	const current_ajax_count = ++ajax_tax_count;

	// Initialize tax_data object that will be accessible to both success and fail callbacks
	const tax_data = new Object();
	tax_data.postdata = postData;

	return jQuery.ajax( {
		type: 'POST',
		data: postData,
		dataType: 'json',
		url: edd_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true,
		},
		success: function( tax_response ) {
			// Only update tax info if this response is the most recent ajax call.
			// Avoids bug with form autocomplete firing multiple ajax calls at the same time and not
			// being able to predict the call response order.
			if ( current_ajax_count === ajax_tax_count ) {
				if ( tax_response.html ) {
					jQuery( '#edd_checkout_cart_form' ).replaceWith( tax_response.html );
				}
				jQuery( '.edd_cart_amount' ).html( tax_response.total );
				tax_data.response = tax_response;
				jQuery( 'body' ).trigger( 'edd_taxes_recalculated', [ tax_data ] );
			}
			jQuery( '.edd-recalculate-taxes-loading' ).remove();
		},
	} ).fail( function( data ) {
		console.log( 'Tax recalculation failed:', data );

		if ( current_ajax_count === ajax_tax_count ) {
			// Create standardized error response structure to maintain consistency
			// with success callback and prevent issues in event listeners
			tax_data.response = {
				success: false,
				error: true,
				tax_raw: tax_data.postdata.current_tax_amount || 0,
				total_raw: current_cart_total,
				debug_data: data
			};
			jQuery( 'body' ).trigger( 'edd_taxes_recalculated', [ tax_data ] );
		}

		// Remove loading spinner on error
		jQuery( '.edd-recalculate-taxes-loading' ).remove();

		// Restore original tax display by showing hidden elements
		const tax_amount_elements = document.querySelectorAll( '.edd_cart_tax .edd_cart_tax_amount' );
		tax_amount_elements.forEach( function( element ) {
			element.style.display = '';
		} );
	} );
}
