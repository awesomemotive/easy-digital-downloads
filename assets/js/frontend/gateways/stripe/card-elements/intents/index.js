/* global jQuery */

/**
 * Internal dependencies
 */
import { apiRequest } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * Retrieve a PaymentIntent.
 *
 * @param {string} intentId Intent ID.
 * @param {string} intentType Intent type. payment_intent or setup_intent.
 * @return {Promise} jQuery Promise.
 */
export function retrieve( intentId, intentType = 'payment_intent' ) {
	const form = $( window.eddStripe.cardElement._parent ).closest( 'form' ),
		tokenInput = $( '#edd-process-stripe-token' );

	return apiRequest( 'edds_get_intent', {
		intent_id: intentId,
		intent_type: intentType,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		form_data: form.serialize(),
		elements_mode: 'card-elements',
	} )
		// Returns just the PaymentIntent object.
		.then( function( response ) {
			return response.intent;
		} );
}

/**
 * Confirm a PaymentIntent.
 *
 * @param {Object} intent Stripe PaymentIntent or SetupIntent.
 * @return {Promise} jQuery Promise.
 */
export function confirm( intent ) {
	const form = $( window.eddStripe.cardElement._parent ).closest( 'form' ),
		tokenInput = $( '#edd-process-stripe-token' );

	return apiRequest( 'edds_confirm_intent', {
		intent_id: intent.id,
		intent_type: intent.object,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		form_data: form.serialize(),
		elements_mode: 'card-elements',
	} )
		// Returns just the PaymentIntent object for easier reprocessing.
		.then( function( response ) {
			return response.intent;
		} );
}

/**
 * Capture a PaymentIntent.
 *
 * @param {Object} intent Stripe PaymentIntent or SetupIntent.
 * @param {Object} data Extra data to pass to the intent action.
 * @param {string} refreshedNonce A refreshed nonce that might be needed if the
 *                                user logged in.
 * @return {Promise} jQuery Promise.
 */
export function capture( intent, data, refreshedNonce ) {
	const form = $( window.eddStripe.cardElement._parent ).closest( 'form' );

	if ( 'requires_capture' !== intent.status ) {
		return Promise.resolve( intent );
	}

	let formData = form.serialize(),
		tokenInput = $( '#edd-process-stripe-token' );

	// Add the refreshed nonce if available.
	if ( refreshedNonce ) {
		formData += `&edd-process-checkout-nonce=${ refreshedNonce }`;
	}

	return apiRequest( 'edds_capture_intent', {
		intent_id: intent.id,
		intent_type: intent.object,
		form_data: formData,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		elements_mode: 'card-elements',
		...data,
	} )
		// Returns just the PaymentIntent object for easier reprocessing.
		.then( function( response ) {
			return response.intent;
		} );
}

/**
 * Update a PaymentIntent.
 *
 * @param {Object} intent Stripe PaymentIntent or SetupIntent.
 * @param {Object} data PaymentIntent data to update.
 * @return {Promise} jQuery Promise.
 */
export function update( intent, data ) {
	const form = $( window.eddStripe.cardElement._parent ).closest( 'form' ),
		tokenInput = $( '#edd-process-stripe-token' );

	return apiRequest( 'edds_update_intent', {
		intent_id: intent.id,
		intent_type: intent.object,
		timestamp: tokenInput.length ? tokenInput.data( 'timestamp' ) : '',
		token: tokenInput.length ? tokenInput.data( 'token' ) : '',
		form_data: form.serialize(),
		elements_mode: 'card-elements',
		...data,
	} )
		// Returns just the PaymentIntent object for easier reprocessing.
		.then( function( response ) {
			return response.intent;
		} );
}

/**
 * Determines if the PaymentIntent requires further action.
 *
 * @link https://stripe.com/docs/stripe-js/reference
 *
 * @param {Object} intent Stripe PaymentIntent or SetupIntent.
 * @param {Object} data Extra data to pass to the intent action.
 */
export async function handle( intent, data ) {
	// requires_confirmation
	if ( 'requires_confirmation' === intent.status ) {
		// Attempt to capture.
		const confirmedIntent = await confirm( intent );

		// Run through again.
		return await handle( confirmedIntent );
	}

	// requires_payment_method
	// @link https://stripe.com/docs/payments/intents#intent-statuses
	if (
		'requires_payment_method' === intent.status ||
		'requires_source' === intent.status
	) {
		// Attempt to update.
		const updatedIntent = await update( intent, data );

		// Run through again.
		return await handle( updatedIntent, data );
	}

	// requires_action
	// @link https://stripe.com/docs/payments/intents#intent-statuses
	if (
		( 'requires_action' === intent.status && 'use_stripe_sdk' === intent.next_action.type ) ||
		( 'requires_source_action' === intent.status && 'use_stripe_sdk' === intent.next_action.type )
	) {
		let cardHandler = 'setup_intent' === intent.object ? 'handleCardSetup' : 'handleCardAction';

		if ( 'automatic' === intent.confirmation_method ) {
			cardHandler = 'handleCardPayment';
		}

		return window.eddStripe[ cardHandler ]( intent.client_secret )
			.then( async ( result ) => {
				if ( result.error ) {
					throw result.error;
				}

				const {
					setupIntent,
					paymentIntent,
				} = result;

				// Run through again.
				return await handle( setupIntent || paymentIntent );
			} );
	}

	// Nothing done, return Intent.
	return intent;
}
