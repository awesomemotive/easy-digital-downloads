/* global Stripe, edd_scripts, edd_stripe_vars */

/**
 * Internal dependencies
 */
import { paymentForm } from './checkout/form.js';

import { setGlobal } from 'utils/globals.js';

export * from './checkout/form.js';

export function setup() {
	// Set the target element we want to use for Checkout Payment Elements.
	setGlobal( 'elementsTarget', '#edd-stripe-payment-element' );

	if ( '1' !== edd_scripts.is_checkout ) {
		return;
	}

	// Initial load for single gateway.
	let hasSingleGateway = document.querySelector( 'input[name="edd-gateway"]' );
	window.eddStripe.isBuyNow = false;

	if ( hasSingleGateway && 'stripe' === hasSingleGateway.value ) {
		setGlobal( 'singleGateway', true );

		paymentForm();
	} else {
		setGlobal( 'singleGateway', false );

		// Gateway switch.
		$( document.body ).on( 'edd_gateway_loaded', ( e, gateway ) => {
			if ( 'stripe' !== gateway ) {
				return;
			}

			paymentForm();
		} );
	}

}
