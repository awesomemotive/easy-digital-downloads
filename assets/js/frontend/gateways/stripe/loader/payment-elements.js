/* global Stripe, edd_scripts, edd_stripe_vars */

/**
 * Internal dependencies
 */
import { domReady, apiRequest, generateNotice } from 'utils';

import {
	setupCheckout,
	setupBuyNow,
} from '../payment-elements/contexts';

import {
	createAndMountElement,
	getBillingDetails,
} from '../payment-elements';

// eslint-enable @wordpress/dependency-group

( () => {
	try {
		window.eddStripe = new Stripe( edd_stripe_vars.publishable_key,{
			betas: ['elements_enable_deferred_intent_beta_1'],
		} );

		// Alias some functionality for external plugins.
		window.eddStripe._plugin = {
			domReady,
			apiRequest,
			generateNotice,
			createAndMountElement,
			getBillingDetails,
		};

		// Setup frontend components when DOM is ready.
		domReady(
			setupCheckout,
			setupBuyNow,
		);

	} catch ( error ) {
		alert( error.message );
	}
} )();
