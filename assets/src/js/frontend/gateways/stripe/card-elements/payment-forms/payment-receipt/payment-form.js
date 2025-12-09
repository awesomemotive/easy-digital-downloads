/* global edd_stripe_vars */

/**
 * Internal dependencies
 */
// eslint-disable @wordpress/dependency-group
import {
	getPaymentMethod,
	createPaymentForm as createElementsPaymentForm,
} from '../..';

import {
	handle as handleIntent,
	retrieve as retrieveIntent,
} from '../../intents'

import { generateNotice, apiRequest } from 'utils';
// eslint-enable @wordpress/dependency-group

/**
 * Binds events and sets up "Update Payment Method" form.
 */
export function paymentForm() {
	// Mount Elements.
	createElementsPaymentForm( window.eddStripe.elements() );

	document.getElementById( 'edds-update-payment-method' ).addEventListener( 'submit', onAuthorizePayment );
}

/**
 * Setup PaymentMethods.
 *
 * Moves the active item to the currently authenticating PaymentMethod.
 */
function setPaymentMethod() {
	const form = document.getElementById( 'edds-update-payment-method' );
	const input = document.getElementById( form.dataset.paymentMethod );

	// Select the correct PaymentMethod after load.
	if ( input ) {
		const changeEvent = document.createEvent( 'Event' );

		changeEvent.initEvent( 'change', true, true );
		input.checked = true;
		input.dispatchEvent( changeEvent );
	}
}

/**
 * Authorize a PaymentIntent.
 *
 * @param {Event} e submtit event.
 */
async function onAuthorizePayment( e ) {
	e.preventDefault();

	const form = document.getElementById( 'edds-update-payment-method' );

	disableForm();

	try {
		const paymentMethod = await getPaymentMethod( form, window.eddStripe.cardElement );

		// Handle PaymentIntent.
		const intent = await retrieveIntent( form.dataset.paymentIntent, 'payment_method' );

		const handledIntent = await handleIntent( intent, {
			payment_method: paymentMethod.id,
		} );

		// Attempt to transition payment status and redirect.
		const authorization = await completeAuthorization( handledIntent.id );

		if ( authorization.payment ) {
			window.location.reload();
		} else {
			throw authorization;
		}
	} catch ( error ) {
		handleException( error );
		enableForm();
	}
}

/**
 * Complete a Payment after the Intent has been authorized.
 *
 * @param {string} intentId Intent ID.
 * @return {Promise} jQuery Promise.
 */
export function completeAuthorization( intentId ) {
	return apiRequest( 'edds_complete_payment_authorization', {
		intent_id: intentId,
		'edds-complete-payment-authorization': document.getElementById(
			'edds-complete-payment-authorization'
		).value
	} );
}

/**
 * Disables "Add New" form.
 */
function disableForm() {
	const submit = document.getElementById( 'edds-update-payment-method-submit' );

	submit.value = submit.dataset.loading;
	submit.disabled = true;
}

/**
 * Enables "Add New" form.
 */
function enableForm() {
	const submit = document.getElementById( 'edds-update-payment-method-submit' );

	submit.value = submit.dataset.submit;
	submit.disabled = false;
}

/**
 * Handles a notice (success or error) for authorizing a card.
 *
 * @param {Object} error Error with message to output.
 */
export function handleException( error ) {
	// Create the new notice.
	const notice = generateNotice(
		( error && error.message ) ? error.message : edd_stripe_vars.generic_error,
		'error'
	);

	const container = document.getElementById( 'edds-update-payment-method-errors' );

	container.innerHTML = '';
	container.appendChild( notice );
}
