/* global $, edd_stripe_vars, edd_global_vars */

/**
 * Internal dependencies
 */
import {
	createPaymentForm as createElementsPaymentForm,
	getPaymentMethod,
} from '../..'; // eslint-disable-line @wordpress/dependency-group

import {
	capture as captureIntent,
	handle as handleIntent,
} from '../../intents';

import { apiRequest, generateNotice } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * Binds Payment submission functionality.
 *
 * Resets before rebinding to avoid duplicate events
 * during gateway switching.
 */
export function paymentForm() {
	// Mount Elements.
	createElementsPaymentForm( window.eddStripe.elements() );

	// Bind form submission.
	// Needs to be jQuery since that is what core submits against.
	$( '#edd_purchase_form' ).off( 'submit', onSubmit );
	$( '#edd_purchase_form' ).on( 'submit', onSubmit );

	// SUPER ghetto way to watch for core form validation because no events are in place.
	// Called after the purchase form is submitted (via `click` or `submit`)
	$( document ).off( 'ajaxSuccess', watchInitialValidation );
	$( document ).on( 'ajaxSuccess', watchInitialValidation );
}

/**
 * Processes Stripe gateway-specific functionality after core AJAX validation has run.
 */
async function onSubmitDelay() {
	try {
		// Form data to send to intent requests.
		let formData = $( '#edd_purchase_form' ).serialize(),
			tokenInput = $( '#edd-process-stripe-token' );

		// Retrieve or create a PaymentMethod.
		const paymentMethod = await getPaymentMethod( document.getElementById( 'edd_purchase_form' ), window.eddStripe.cardElement );

		// Run the modified `_edds_process_purchase_form` and create an Intent.
		const {
			intent: initialIntent,
			nonce: refreshedNonce
		} = await processForm( paymentMethod.id, paymentMethod.exists );

		// Update existing nonce value in DOM form data in case data is retrieved
		// again directly from the DOM.
		$( '#edd-process-checkout-nonce' ).val( refreshedNonce );

		// Handle any actions required by the Intent State Machine (3D Secure, etc).
		const handledIntent = await handleIntent(
			initialIntent,
			{
				form_data: formData += `&edd-process-checkout-nonce=${ refreshedNonce }`,
				timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
				token: tokenInput.length ? tokenInput.data( 'token' ) : '',
			}
		);

		// Create an EDD payment record.
		const { intent, nonce } = await createPayment( handledIntent );

		// Capture any unpcaptured intents.
		const finalIntent = await captureIntent(
			intent,
			{},
			nonce
		);

		// Attempt to transition payment status and redirect.
		// @todo Maybe confirm payment status as well? Would need to generate a custom
		// response because the private EDD_Payment properties are not available.
		if (
			( 'succeeded' === finalIntent.status ) ||
			( 'canceled' === finalIntent.status && 'abandoned' === finalIntent.cancellation_reason )
		) {
			await completePayment( finalIntent, nonce );

			window.location.replace( edd_stripe_vars.successPageUri );
		} else {
			window.location.replace( edd_stripe_vars.failurePageUri );
		}
	} catch ( error ) {
		handleException( error );
		enableForm();
	}
}

/**
 * Processes the purchase form.
 *
 * Generates purchase data for the current session and
 * uses the PaymentMethod to generate an Intent based on data.
 *
 * @param {string} paymentMethodId PaymentMethod ID.
 * @param {Bool} paymentMethodExists If the PaymentMethod has already been attached to a customer.
 * @return {Promise} jQuery Promise.
 */
export function processForm( paymentMethodId, paymentMethodExists ) {
	let tokenInput = $( '#edd-process-stripe-token' );

	return apiRequest( 'edds_process_purchase_form', {
		// Send available form data.
		form_data: $( '#edd_purchase_form' ).serialize(),
		payment_method_id: paymentMethodId,
		payment_method_exists: paymentMethodExists,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
	} );
}

/**
 * Complete a Payment in EDD.
 *
 * @param {object} intent Intent.
 * @return {Promise} jQuery Promise.
 */
export function createPayment( intent ) {
	const paymentForm = $( '#edd_purchase_form' ),
		tokenInput = $( '#edd-process-stripe-token' );
	let formData = paymentForm.serialize();

	// Attempt to find the Checkout nonce directly.
	if ( paymentForm.length === 0 ) {
		const nonce = $( '#edd-process-checkout-nonce' ).val();
		formData = `edd-process-checkout-nonce=${ nonce }`
	}

	return apiRequest( 'edds_create_payment', {
		form_data: formData,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		intent,
	} );
}

/**
 * Complete a Payment in EDD.
 *
 * @param {object} intent Intent.
 * @param {string} refreshedNonce A refreshed nonce that might be needed if the
 *                                user logged in.
 * @return {Promise} jQuery Promise.
 */
export function completePayment( intent, refreshedNonce ) {
	const paymentForm = $( '#edd_purchase_form' );
	let formData = paymentForm.serialize(),
		tokenInput = $( '#edd-process-stripe-token' );

	// Attempt to find the Checkout nonce directly.
	if ( paymentForm.length === 0 ) {
		const nonce = $( '#edd-process-checkout-nonce' ).val();
		formData = `edd-process-checkout-nonce=${ nonce }`;
	}

	// Add the refreshed nonce if available.
	if ( refreshedNonce ) {
		formData += `&edd-process-checkout-nonce=${ refreshedNonce }`;
	}

	return apiRequest( 'edds_complete_payment', {
		form_data: formData,
		intent,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
	} );
}


/**
 * Listen for initial EDD core validation.
 *
 * @param {Object} event Event.
 * @param {Object} xhr AJAX request.
 * @param {Object} options Request options.
 */
function watchInitialValidation( event, xhr, options ) {
	if ( ! options || ! options.data || ! xhr ) {
		return;
	}

	if (
		options.data.includes( 'action=edd_process_checkout' ) &&
		options.data.includes( 'edd-gateway=stripe' ) &&
		( xhr.responseText && 'success' === xhr.responseText.trim() )
	) {
		return onSubmitDelay();
	}
};

/**
 * EDD core listens to a a `click` event on the Checkout form submit button.
 *
 * This submit event handler captures true submissions and triggers a `click`
 * event so EDD core can take over as normoal.
 *
 * @param {Object} event submit Event.
 */
function onSubmit( event ) {
	// Ensure we are dealing with the Stripe gateway.
	if ( ! (
		// Stripe is selected gateway and total is larger than 0.
		$( 'input[name="edd-gateway"]' ).val() === 'stripe'	&&
		$( '.edd_cart_total .edd_cart_amount' ).data( 'total' ) > 0
	) ) {
		return;
	}

	// While this function is tied to the submit event, block submission.
	event.preventDefault();

	// Simulate a mouse click on the Submit button.
	//
	// If the form is submitted via the "Enter" key we need to ensure the core
	// validation is run.
	//
	// When that is run and then the form is resubmitted
	// the click event won't do anything because the button will be disabled.
	$( '#edd_purchase_form #edd_purchase_submit [type=submit]' ).trigger( 'click' );
}

/**
 * Enables the Checkout form for further submissions.
 */
function enableForm() {
	// Update button text.
	document.querySelector( '#edd_purchase_form #edd_purchase_submit [type=submit]' ).value = edd_global_vars.complete_purchase;

	// Enable form.
	$( '.edd-loading-ajax' ).remove();
	$( '.edd_errors.edd-alert-error' ).remove();
	$( '.edd-error' ).hide();
	$( '#edd-purchase-button' ).attr( 'disabled', false );
}

/**
 * Handles error output for stripe.js promises, or jQuery AJAX promises.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/assets/js/edd-ajax.js#L390
 *
 * @param {Object} error Error data.
 */
function handleException( error ) {
	let { code, message } = error;
	const { elementsOptions: { i18n: { errorMessages } } } = window.edd_stripe_vars;

	if ( ! message ) {
		message = edd_stripe_vars.generic_error;
	}

	const localizedMessage = code && errorMessages[code] ? errorMessages[code] : message;

	const notice = generateNotice( localizedMessage );

	// Hide previous messages.
	// @todo These should all be in a container, but that's not how core works.
	$( '.edd-stripe-alert' ).remove();
	$( edd_global_vars.checkout_error_anchor ).before( notice );
	$( document.body ).trigger( 'edd_checkout_error', [ error ] );

	if ( window.console && error.responseText ) {
		window.console.error( error.responseText );
	}
}
