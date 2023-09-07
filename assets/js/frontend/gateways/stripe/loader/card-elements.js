/* global Stripe, edd_stripe_vars */

/**
 * Internal dependencies
 */
import { domReady, apiRequest, generateNotice } from 'utils';

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
			domReady,
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
		};

		// Setup frontend components when DOM is ready.
		domReady(
			setupCheckout,
			setupProfile,
			setupPaymentHistory,
			setupBuyNow,
			setupDownloadPRB,
			setupCheckoutPRB,
		);
	} catch ( error ) {
		alert( error.message );
	}
} )();
