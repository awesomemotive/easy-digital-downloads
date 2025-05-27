/* global Stripe, edd_stripe_vars, wp */

/**
 * Internal dependencies
 */
import { apiRequest, generateNotice } from 'utils';

import {
	setupCheckout,
	setupProfile,
	setupPaymentHistory,
	setupBuyNow,
	setupDownloadPRB,
	setupCheckoutPRB,
} from '../card-elements/payment-forms';

import {
	paymentMethods,
} from '../card-elements/payment-methods';

import {
	mountCardElement,
	createPaymentForm as createElementsPaymentForm,
	getBillingDetails,
	getPaymentMethod,
} from '../card-elements';

import {
	confirm as confirmIntent,
	handle as handleIntent,
	retrieve as retrieveIntent,
} from '../card-elements/intents';
// eslint-enable @wordpress/dependency-group

( () => {
	try {
		window.eddStripe = new Stripe( edd_stripe_vars.publishable_key );

		// Alias some functionality for external plugins.
		window.eddStripe._plugin = {
			apiRequest,
			generateNotice,
			mountCardElement,
			createElementsPaymentForm,
			getBillingDetails,
			getPaymentMethod,
			confirmIntent,
			handleIntent,
			retrieveIntent,
			paymentMethods,
			// The domReady function is kept here for backward compatibility with EDD Recurring.
			domReady: wp.domReady,
		};

		// Setup frontend components when DOM is ready.
		wp.domReady( setupCheckout );
		wp.domReady( setupProfile );
		wp.domReady( setupPaymentHistory );
		wp.domReady( setupBuyNow );
		wp.domReady( setupDownloadPRB );
		wp.domReady( setupCheckoutPRB );
	} catch ( error ) {
		alert( error.message );
	}
} )();
