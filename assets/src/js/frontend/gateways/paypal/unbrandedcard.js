/* global eddPayPalVars, paypal */

import { showPayPalError, clearPayPalErrors } from './errors.js';
import { collectCheckoutFields } from './base/checkout-fields.js';

// EDD PayPal Unbranded Card (Advanced Card Processing) — on-checkout card
// fields via the PayPal CardFields SDK component, with 3-D Secure.

/**
 * EDD PayPal Unbranded Card integration.
 *
 * Renders the PayPal CardFields component (number / expiry / cvv) directly on
 * the checkout page. On submit the SDK calls `createOrder` (our REST endpoint
 * builds the EDD + PayPal order with 3DS forced), runs the 3-D Secure
 * challenge, then `onApprove` posts to our capture endpoint which verifies
 * liability shifted before taking payment.
 */
const EDDPayPalUnbrandedCard = {
	cardField: null,
	eddOrderId: 0,
	isProcessing: false,
	isReloading: false,

	/**
	 * Bootstraps the card fields if eligible.
	 */
	init: async function() {
		if ( ! eddPayPalVars.unbrandedCard || ! eddPayPalVars.clientToken ) {
			return;
		}
		if ( typeof paypal === 'undefined' || typeof paypal.CardFields !== 'function' ) {
			return;
		}

		const container = document.getElementById( 'edd-paypal-unbranded-card-container' );
		if ( ! container ) {
			return;
		}

		try {
			this.cardField = paypal.CardFields( {
				createOrder: this.createOrder.bind( this ),
				onApprove: this.onApprove.bind( this ),
				onError: this.onError.bind( this ),
			} );

			if ( ! this.cardField.isEligible() ) {
				return;
			}

			this.cardField.NumberField().render( '#edd-paypal-card-number' );
			this.cardField.ExpiryField().render( '#edd-paypal-card-expiry' );
			this.cardField.CVVField().render( '#edd-paypal-card-cvv' );

			this.bindSubmit();
			container.style.display = '';

			const divider = document.getElementById( 'edd-paypal-unbranded-card-divider' );
			if ( divider ) {
				divider.style.display = '';
			}
		} catch ( e ) {
			console.warn( 'EDD PayPal Unbranded Card unavailable:', e );
		}
	},

	/**
	 * Binds the submit handler to the card-fields pay button.
	 */
	bindSubmit: function() {
		const submitBtn = document.getElementById( 'edd-paypal-unbranded-card-submit' );
		if ( ! submitBtn ) {
			return;
		}

		submitBtn.addEventListener( 'click', async ( e ) => {
			e.preventDefault();

			if ( this.isProcessing ) {
				return;
			}

			const form = document.getElementById( 'edd_purchase_form' );
			if ( form && ! form.reportValidity() ) {
				return;
			}

			this.isProcessing = true;
			this.clearErrors();
			this.showSpinner();

			try {
				// submit() triggers createOrder, attaches the card, runs the
				// 3DS challenge, then fires onApprove (which redirects).
				await this.cardField.submit();
			} catch ( err ) {
				// createOrder/onApprove reject with the server's message (e.g.
				// "could not verify your card"); surface that, not the generic
				// fallback. SDK-side rejections may have no useful message.
				console.error( 'Unbranded card submit failed:', err );
				if ( this.isReloading ) {
					return;
				}
				this.showError( err?.message || eddPayPalVars.defaultError );
				this.isProcessing = false;
				this.hideSpinner();
			}
		} );
	},

	/**
	 * createOrder callback — builds the EDD + PayPal order server-side.
	 *
	 * @returns {Promise<string>} PayPal order ID.
	 */
	createOrder: async function() {
		const result = await this.postRest( eddPayPalVars.unbrandedCard.createUrl, {
			form_data: this.collectFormData(),
		} );

		if ( ! result || ! result.id ) {
			throw new Error( 'No PayPal order ID returned.' );
		}

		this.eddOrderId = result.edd_order_id || 0;
		return result.id;
	},

	/**
	 * onApprove callback — verifies liability + captures server-side.
	 *
	 * @param {Object} data PayPal approval data ({ orderID }).
	 * @returns {Promise<void>}
	 */
	onApprove: async function( data ) {
		const result = await this.postRest( eddPayPalVars.unbrandedCard.captureUrl, {
			paypal_order_id: data.orderID,
			edd_order_id: this.eddOrderId,
		} );

		if ( result && result.redirect_url ) {
			globalThis.location = result.redirect_url;
			return;
		}

		// A 2xx with no redirect is the PENDING / on-hold case (HTTP 202) —
		// surface the "payment is being reviewed" message rather than leaving
		// the buyer with no feedback.
		if ( result && result.message ) {
			this.showError( result.message );
		}
		this.isProcessing = false;
		this.hideSpinner();
	},

	/**
	 * onError callback for the CardFields component.
	 *
	 * @param {Error} err The error.
	 */
	onError: function( err ) {
		console.error( 'Unbranded card error:', err );
		if ( this.isReloading ) {
			return;
		}
		this.showError( eddPayPalVars.defaultError );
		this.isProcessing = false;
		this.hideSpinner();
	},

	/**
	 * POSTs JSON to an EDD REST endpoint with the cart-token headers.
	 *
	 * Throws on a non-OK response (so createOrder/onApprove reject and the SDK
	 * aborts). The thrown Error carries the server message; the single display
	 * point is the submit() catch in bindSubmit, so we don't show it here.
	 *
	 * @param {string} url  REST URL.
	 * @param {Object} body Request body.
	 * @returns {Promise<Object>}
	 */
	postRest: async function( url, body ) {
		const response = await fetch( url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-EDD-Cart-Token': eddPayPalVars.cartToken,
				'X-EDD-Cart-Timestamp': eddPayPalVars.cartTimestamp,
			},
			body: JSON.stringify( body ),
		} );

		let json;
		try {
			json = await response.json();
		} catch ( e ) {
			throw new Error( eddPayPalVars.defaultError );
		}

		if ( ! response.ok ) {
			// A removed discount changes the total, so reload to show it with the message.
			if ( json?.data?.refresh ) {
				this.isReloading = true;
				globalThis.location.reload();
			}
			throw new Error( json && json.message ? json.message : eddPayPalVars.defaultError );
		}

		return json;
	},

	/**
	 * Collects checkout form fields for the REST request.
	 *
	 * @returns {Object}
	 */
	collectFormData: function() {
		return collectCheckoutFields();
	},

	/**
	 * Shows the processing spinner and disables the submit button.
	 *
	 * @returns {void}
	 */
	showSpinner: function() {
		const spinner = document.getElementById( 'edd-paypal-unbranded-card-spinner' );
		const submitBtn = document.getElementById( 'edd-paypal-unbranded-card-submit' );
		if ( spinner ) {
			spinner.style.display = '';
		}
		if ( submitBtn ) {
			submitBtn.disabled = true;
		}
	},

	/**
	 * Hides the processing spinner and re-enables the submit button.
	 *
	 * @returns {void}
	 */
	hideSpinner: function() {
		const spinner = document.getElementById( 'edd-paypal-unbranded-card-spinner' );
		const submitBtn = document.getElementById( 'edd-paypal-unbranded-card-submit' );
		if ( spinner ) {
			spinner.style.display = 'none';
		}
		if ( submitBtn ) {
			submitBtn.disabled = false;
		}
	},

	/**
	 * Displays an error in the standard PayPal errors wrapper.
	 *
	 * @param {string} message Error message or HTML.
	 * @returns {void}
	 */
	showError: function( message ) {
		showPayPalError( message );
	},

	/**
	 * Clears displayed errors.
	 *
	 * @returns {void}
	 */
	clearErrors: function() {
		clearPayPalErrors();
	},
};

export default EDDPayPalUnbrandedCard;
