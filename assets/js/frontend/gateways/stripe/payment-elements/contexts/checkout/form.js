/* global $, edd_stripe_vars, edd_global_vars, eddStripe */

/**
 * Internal dependencies
 */
import {
	createAndMountElement,
	getBillingDetails,
} from '../..'; // eslint-disable-line @wordpress/dependency-group

import { apiRequest, generateNotice } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * Binds Payment submission functionality.
 *
 * Resets before rebinding to avoid duplicate events
 * during gateway switching.
 */
export function paymentForm() {
	// Mount Elements.
	createAndMountElement();

	if ( false === window.eddStripe.elementMounted ) {
		return;
	}

	// Set some default intent-based values so we can store and use them later.
	window.eddStripe.intentFingerprint = '';
	window.eddStripe.intentType = '';
	window.eddStripe.intentId = '';

	disableForm();

	// Try and prevent any other listeners from happening first. This has to be jQuery.
	$( document ).off( 'click', '#edd_purchase_form #edd_purchase_submit [type=submit]' );

	const purchaseButton = document.getElementById( 'edd-purchase-button' );

	purchaseButton.addEventListener( 'click', async( event ) => {
		// Ensure we are dealing with the Stripe gateway.
		if ( !isStripeSelectedGateway() ) {
			return;
		}

		if ( ! purchaseformValid() ) {
			return false;
		}

		event.preventDefault();
		updateForm( false );

		try{
			// Create a payment method.
			let billingDetails = getBillingDetails( document.getElementById( 'edd_purchase_form' ) );
			const { paymentMethod: paymentMethod } = await getPaymentMethod( billingDetails );

			// If we don't have a payment method, we can't continue.
			if ( ! paymentMethod ) {
				// The getPaymentMethod handles the error output, so we can just return here.
				return false;
			}

			/**
			 * Run the modified `_edds_process_purchase_form` and create an Intent.
			 *
			 * We should always create intents at the server, to prevent modification client-side.
			 */
			let {
				token: refreshedNonce,
				client_secret: clientSecret,
				intent_type: intentType,
				intent_fingerprint: intentFingerprint,
				intent_id: intentId,
			} = await processForm( paymentMethod );

			// Store these so we can use them later to not create different payment intents.
			window.eddStripe.intentType = intentType;
			window.eddStripe.intentFingerprint = intentFingerprint;
			window.eddStripe.intentId = intentId;

			const nonceFeld = document.getElementById( 'edd-process-checkout-nonce' );
			nonceFeld.value = refreshedNonce;

			/**
			 * Our last action of processing the form returned us a Payment Intent, which
			 * gave us a client secret. We can update the payment element with this now.
			 */
			const confirmFunc = 'PaymentIntent' === intentType ? 'confirmPayment' : 'confirmSetup';

			const confirmArgs = {
				clientSecret: clientSecret,
				confirmParams: {
					return_url: edd_stripe_vars.successPageUri,
				},
				redirect: 'if_required',
			}

			confirmArgs.confirmParams.payment_method = paymentMethod.id;

			/**
			 * Now confirm the payment.
			 */
			const { error } = await window.eddStripe[confirmFunc]( confirmArgs );

			if ( error ) {
				handleException( error );
				enableForm();
				return false;
			}

			/**
			 * At this point, we've already verified the intent, as we marked it as automatic capturing,
			 * so let's move forwarwd with creating the payment in EDD.
			 */
			const { intent, nonce } = await createAndCompleteOrder();

			// Our nonce may have changed now that user is logged in, so we need to ensure we update that.
			nonceFeld.value = nonce;

			// Attempt to transition payment status and redirect.
			// @todo Maybe confirm payment status as well? Would need to generate a custom
			// response because the private EDD_Payment properties are not available.
			if ( ( 'succeeded' === intent.status ) ) {
				window.location.replace( edd_stripe_vars.successPageUri );
			} else {
				window.location.replace( edd_stripe_vars.failurePageUri );
			}
		} catch ( error ) {
			handleException( error );
			enableForm();
			return false;
		}
	} );

}

/**
 * Processes the purchase form.
 *
 * Generates purchase data for the current session and
 * uses the PaymentMethod to generate an Intent based on data.
 *
 * @return {Promise} jQuery Promise.
 */
export async function processForm( paymentMethod ) {
	let tokenInput = $( '#edd-process-stripe-token' );

	return apiRequest( 'edds_process_purchase_form', {
		// Send available form data.
		form_data: $( '#edd_purchase_form' ).serialize(),
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		intent_type: window.eddStripe.intentType,
		intent_id: window.eddStripe.intentId,
		intent_fingerprint: window.eddStripe.intentFingerprint,
		payment_method: paymentMethod,
	} );
}

/**
 * Complete a Payment in EDD.
 *
 * @param {object} intent Intent.
 * @return {Promise} jQuery Promise.
 */
export function createAndCompleteOrder() {
	let paymentForm = $( '#edd_purchase_form' ),
		tokenInput = $( '#edd-process-stripe-token' );

	let formData = paymentForm.serialize();

	// Attempt to find the Checkout nonce directly.
	if ( paymentForm.length === 0 ) {
		let nonce = $( '#edd-process-checkout-nonce' ).val();
		formData = `edd-process-checkout-nonce=${ nonce }`
	}

	return apiRequest( 'edds_create_and_complete_order', {
		form_data: formData,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		intent_id: window.eddStripe.intentId,
		intent_type: window.eddStripe.intentType,
	} );
}

export function updateForm ( remove_spinner = true ) {
	window.eddStripe.paymentElement.update( {
		readOnly: true,
	} );

	maybeAddSpinner();

	let purchaseButton = $('#edd-purchase-button');
	purchaseButton.attr( 'data-edd-button-state', 'updating' );

	purchaseButton.prop( 'disabled', 'disabled' );
	purchaseButton.prop( 'readonly', 'readonly' );

	$( '.edd_errors.edd-alert-error' ).remove();
	$( '.edd-error' ).hide();
	$( '#edd-stripe-payment-errors' ).remove();
	$( '.edd-stripe-alert' ).remove();

	if ( remove_spinner ) {
		purchaseButton.parent().find( '.edd-loading-ajax' ).remove();
	}
}

export function disableForm ( hide_errors = false ) {

	window.eddStripe.paymentElement.update( {
		readOnly: false,
	} );

	let purchaseButton = $('#edd-purchase-button');

	purchaseButton.attr( 'data-edd-button-state', 'disabled' );

	purchaseButton.prop( 'disabled', 'disabled' );
	purchaseButton.prop( 'readonly', 'readonly' );

	purchaseButton.parent().find('.edd-loading-ajax').remove();

	if ( hide_errors ) {
		$( '.edd_errors.edd-alert-error' ).remove();
		$( '.edd-error' ).hide();
		$( '#edd-stripe-payment-errors' ).remove();
		$( '.edd-stripe-alert' ).remove();
	}
}

/**
 * Enables the Checkout form for further submissions.
 */
export function enableForm() {
	/**
	 * If we're at the failure limit, we don't want to enable the form.
	 */
	if ( window.eddStripe.isAtFailureLimit ) {
		return;
	}

	window.eddStripe.paymentElement.update( {
		readOnly: false,
	} );

	let purchaseButton = $('#edd-purchase-button');

	purchaseButton.attr( 'data-edd-button-state', 'ready' );

	purchaseButton.prop( 'disabled', '' );
	purchaseButton.prop( 'readonly', '' );

	// Enable form.
	purchaseButton.parent().find('.edd-loading-ajax').remove();
	$( '.edd_errors.edd-alert-error' ).remove();
	$( '.edd-error' ).hide();
}

function maybeAddSpinner() {
	let purchaseButtonParent = $('#edd-purchase-button').parent();

	if ( purchaseButtonParent.find('.edd-loading-ajax').length === 0 ) {
		purchaseButtonParent.append( '<span class="edd-loading-ajax edd-loading"></span>' );
	}
}

/**
 * Handles error output for stripe.js promises, or jQuery AJAX promises.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/assets/js/edd-ajax.js#L390
 *
 * @param {Object} error Error data.
 */
async function handleException( error ) {
	console.log(error);
	let { code, message, type } = error;
	const { elementsCustomizations: { i18n: { errorMessages } } } = window.edd_stripe_vars;

	if ( ! message ) {
		message = edd_stripe_vars.generic_error;
	}

	const localizedMessage = code && errorMessages[code] ? errorMessages[code] : message,
		  notice = generateNotice( localizedMessage );


	/**
	 * We only want to tick the error handler if we got an failed payment attempt.
	 * Errors like the currency being incorrect shouldn't tick a user's counter.
	 *
	 * With non-card payments there is also an incomplete code that we need to account for, that shouln't tick.
	 */
	if ( code && 'incomplete' !== code ) {
		let { is_at_limit: isAtLimit, message: errorMessage } = await apiRequest( 'edds_payment_elements_rate_limit_tick' );

		// If we're at the limit, disable the form.
		if ( isAtLimit ) {
			formErrorLimitReached( errorMessage );
			return;
		}
	}

	// Hide previous messages.
	// @todo These should all be in a container, but that's not how core works.
	$( '.edd-stripe-alert' ).remove();
	$( edd_global_vars.checkout_error_anchor ).before( notice );
	$( document.body ).trigger( 'edd_checkout_error', [ error ] );

	// If this is an incomplete code, make it a warning, not an error.
	if ( 'incomplete' === code ) {
		$( '.edd-stripe-alert' ).removeClass( 'edd-alert-error' ).addClass( 'edd-alert-warn' );
	}

	if ( window.console && error.responseText ) {
		window.console.error( error.responseText );
	}
}

export function purchaseformValid() {
	let formIsValid = true;
	const eddPurchaseForm = document.getElementById( 'edd_purchase_form' );
	const requiredInputs = eddPurchaseForm.querySelectorAll( '[required]' );

	// Loop through the targtedInputs
	requiredInputs.forEach( ( input ) => {
		// And add an event listener to run the udpateElementBillingDetails function.
		if ( false === input.checkValidity() ) {
			input.reportValidity();
			formIsValid = false;
		}
	} );

	return formIsValid;
}

function getPaymentMethod( billingDetails ) {
	// Create a PaymentMethod using the Element data.
	return window.eddStripe
		.createPaymentMethod(
			{
				elements: window.eddStripe.configuredElement,
				params: {
					billing_details: billingDetails,
				}
			}
		)
		.then( function ( {
			error: error,
			paymentMethod: paymentMethod,
		} ) {
			if ( error ) {
				handleException( error );
				enableForm();
				return false;
			}

			return { paymentMethod: paymentMethod };
		} )
		.catch( ( error ) => {
			handleException( error );
			enableForm();
			return false;
		} );
}

function formErrorLimitReached( errorMessage = '') {
	window.eddStripe.isAtFailureLimit = true;

	/**
	 * While we could just set the readOnly property to true, we need to unmount the element
	 * for the safest approach.
	 */
	window.eddStripe.paymentElement.unmount();

	/**
	 * Now we can just remove the entire wrapper for the card fields,
	 */
	$( '#edd_cc_fields' ).slideUp().remove();

	let purchaseButton = $('#edd-purchase-button');
	purchaseButton.remove();

	let notice = generateNotice( errorMessage, 'error' );

	// Hide previous messages.
	// @todo These should all be in a container, but that's not how core works.
	$( '.edd-stripe-alert' ).remove();
	$( edd_global_vars.checkout_error_anchor ).before( notice );
}

export function isStripeSelectedGateway () {
	let StripeGateway = document.getElementById( 'edd-process-stripe-token' );

	if ( ! StripeGateway ) {
		StripeGateway = document.getElementById( 'edd-stripe-payment-element' );
		if ( StripeGateway && edd_global_vars.showStoreErrors ) {
			console.warn( 'Please update your custom checkout to use edds_get_tokenizer_input() for a more secure checkout.' );
		}
	}

	if ( ! StripeGateway ) {
		return false;
	}

	return document.querySelector( '.edd_cart_total .edd_cart_amount' ).dataset.total > 0;
}
