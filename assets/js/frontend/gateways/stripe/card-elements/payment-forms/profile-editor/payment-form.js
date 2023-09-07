/* global edd_stripe_vars, location */

/**
 * Internal dependencies.
 */
import {
	createPaymentForm as createElementsPaymentForm,
	getBillingDetails
} from '../..';

import {
	apiRequest,
	hasValidInputs,
	triggerBrowserValidation,
	generateNotice,
	forEach
} from 'utils';

/**
 * Binds events and sets up "Add New" form.
 */
export function paymentForm() {
	// Mount Elements.
	createElementsPaymentForm( window.eddStripe.elements() );

	// Toggles and submission.
	document.querySelector( '.edd-stripe-add-new' ).addEventListener( 'click', onToggleForm );
	document.getElementById( 'edd-stripe-add-new-cancel' ).addEventListener( 'click', onToggleForm );
	document.getElementById( 'edd-stripe-add-new-card' ).addEventListener( 'submit', onAddPaymentMethod );

	// Set "Card Name" field as required by HTML5
	document.getElementById( 'card_name' ).required = true;
}

/**
 * Handles toggling of "Add New" form button and submission.
 *
 * @param {Event} e click event.
 */
function onToggleForm( e ) {
	e.preventDefault();

	const form = document.getElementById( 'edd-stripe-add-new-card' );
	const formFields = form.querySelector( '.edd-stripe-add-new-card' );
	const isFormVisible = 'block' === formFields.style.display;

	const cancelButton = form.querySelector( '#edd-stripe-add-new-cancel' );

	// Trigger a `submit` event.
	if ( isFormVisible && cancelButton !== e.target ) {
		const submitEvent = document.createEvent( 'Event' );

		submitEvent.initEvent( 'submit', true, true );
		form.dispatchEvent( submitEvent );
	// Toggle form.
	} else {
		formFields.style.display = ! isFormVisible ? 'block' : 'none';
		cancelButton.style.display = ! isFormVisible ? 'inline-block' : 'none';
	}
}

/**
 * Adds a new Source to the Customer.
 *
 * @param {Event} e submit event.
 */
function onAddPaymentMethod( e ) {
	e.preventDefault();

	const form = e.target;

	if ( ! hasValidInputs( form ) ) {
		triggerBrowserValidation( form );
	} else {
		try {
			disableForm();

			createPaymentMethod( form )
				.then( addPaymentMethod )
				.catch( ( error ) => {
					handleNotice( error );
					enableForm();
				} );
		} catch ( error ) {
			handleNotice( error );
			enableForm();
		}
	}
}

/**
 * Add a PaymentMethod.
 *
 * @param {Object} paymentMethod
 */
export function addPaymentMethod( paymentMethod ) {
	var tokenInput = document.getElementById( '#edd-process-stripe-token' );

	apiRequest( 'edds_add_payment_method', {
		payment_method_id: paymentMethod.id,
		nonce: document.getElementById( 'edd-stripe-add-card-nonce' ).value,
		timestamp: tokenInput ? tokenInput.dataset.timestamp : '',
		token: tokenInput ? tokenInput.dataset.token : '',
	} )
		/**
		 * Shows an error when the API request fails.
		 *
		 * @param {Object} response API Request response.
		 */
		.fail( handleNotice )
		/**
		 * Shows a success notice and automatically redirect.
		 *
		 * @param {Object} response API Request response.
		 */
		.done( function( response ) {
			handleNotice( response, 'success' );

			// Automatically redirect on success.
			setTimeout( function() {
				location.reload();
			}, 1500 );
		} );
}

/**
 * Creates a PaymentMethod from a card and billing form.
 *
 * @param {HTMLElement} billingForm Form with billing fields to retrieve data from.
 * @return {Object} Stripe PaymentMethod.
 */
function createPaymentMethod( billingForm ) {
	return window.eddStripe
		// Create a PaymentMethod with stripe.js
		.createPaymentMethod(
			'card',
			window.eddStripe.cardElement,
			{
				billing_details: getBillingDetails( billingForm ),
			}
		)
		/**
		 * Handles PaymentMethod creation response.
		 *
		 * @param {Object} result PaymentMethod creation result.
		 */
		.then( function( result ) {
			if ( result.error ) {
				throw result.error;
			}

			return result.paymentMethod;
		} );
}

/**
 * Disables "Add New" form.
 */
function disableForm() {
	const submit = document.querySelector( '.edd-stripe-add-new' );

	submit.value = submit.dataset.loading;
	submit.disabled = true;
}

/**
 * Enables "Add New" form.
 */
function enableForm() {
	const submit = document.querySelector( '.edd-stripe-add-new' );

	submit.value = submit.dataset.submit;
	submit.disabled = false;
}

/**
 * Handles a notice (success or error) for card actions.
 *
 * @param {Object} error Error with message to output.
 * @param {string} type Notice type.
 */
export function handleNotice( error, type = 'error' ) {
	// Create the new notice.
	const notice = generateNotice(
		( error && error.message ) ? error.message : edd_stripe_vars.generic_error,
		type
	);

	// Hide previous notices.
	forEach( document.querySelectorAll( '.edd-stripe-alert' ), function( alert ) {
		alert.remove();
	} );

	// Show new notice.
	document.querySelector( '.edd-stripe-add-card-actions' )
		.insertBefore( notice, document.querySelector( '.edd-stripe-add-new' ) );
}
