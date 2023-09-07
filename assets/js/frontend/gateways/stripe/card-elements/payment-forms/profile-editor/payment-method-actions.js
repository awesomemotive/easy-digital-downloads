/* global edd_stripe_vars, location */

/**
 * Internal dependencies
 */
import { apiRequest, generateNotice, fieldValueOrNull, forEach } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * Binds events for card actions.
 */
export function paymentMethodActions() {
	// Update.
	forEach( document.querySelectorAll( '.edd-stripe-update-card' ), function( updateButton ) {
		updateButton.addEventListener( 'click', onToggleUpdateForm );
	} );

	forEach( document.querySelectorAll( '.edd-stripe-cancel-update' ), function( cancelButton ) {
		cancelButton.addEventListener( 'click', onToggleUpdateForm );
	} );

	forEach( document.querySelectorAll( '.card-update-form' ), function( updateButton ) {
		updateButton.addEventListener( 'submit', onUpdatePaymentMethod );
	} );

	// Delete.
	forEach( document.querySelectorAll( '.edd-stripe-delete-card' ), function( deleteButton ) {
		deleteButton.addEventListener( 'click', onDeletePaymentMethod );
	} );

	// Set Default.
	forEach( document.querySelectorAll( '.edd-stripe-default-card' ), function( setDefaultButton ) {
		setDefaultButton.addEventListener( 'click', onSetDefaultPaymentMethod );
	} );
}

/**
 * Handle a generic Payment Method action (set default, update, delete).
 *
 * @param {string} action Payment action.
 * @param {string} paymentMethodId PaymentMethod ID.
 * @param {null|Object} data Additional AJAX data.
 * @return {Promise} jQuery Promise.
 */
function paymentMethodAction( action, paymentMethodId, data = {} ) {
	var tokenInput = document.getElementById( 'edd-process-stripe-token-' + paymentMethodId );
	data.timestamp = tokenInput ? tokenInput.dataset.timestamp : '';
	data.token = tokenInput ? tokenInput.dataset.token : '';

	return apiRequest( action, {
		payment_method: paymentMethodId,
		nonce: document.getElementById( 'card_update_nonce_' + paymentMethodId ).value,
		...data,
	} )
		/**
		 * Shows an error when the API request fails.
		 *
		 * @param {Object} response API Request response.
		 */
		.fail( function( response ) {
			handleNotice( paymentMethodId, response );
		} )
		/**
		 * Shows a success notice and automatically redirect.
		 *
		 * @param {Object} response API Request response.
		 */
		.done( function( response ) {
			handleNotice( paymentMethodId, response, 'success' );

			// Automatically redirect on success.
			setTimeout( function() {
				location.reload();
			}, 1500 );
		} );
}

/**
 *
 * @param {Event} e
 */
function onToggleUpdateForm( e ) {
	e.preventDefault();

	const source = e.target.dataset.source;

	const form = document.getElementById( source + '-update-form' );
	const cardActionsEl = document.getElementById( source + '-card-actions' );
	const isFormVisible = 'block' === form.style.display;

	form.style.display = ! isFormVisible ? 'block' : 'none';
	cardActionsEl.style.display = ! isFormVisible ? 'none' : 'block';
}

/**
 *
 * @param {Event} e
 */
function onUpdatePaymentMethod( e ) {
	e.preventDefault();

	const form = e.target;
	const data = {};

	// Gather form data.
	const updateFields = [
		'address_city',
		'address_country',
		'address_line1',
		'address_line2',
		'address_zip',
		'address_state',
		'exp_month',
		'exp_year',
	];

	updateFields.forEach( function( fieldName ) {
		const field = form.querySelector( '[name="' + fieldName + '"]' );
		data[ fieldName ] = fieldValueOrNull( field );
	} );

	const submitButton = form.querySelector( 'input[type="submit"]' );

	submitButton.disabled = true;
	submitButton.value = submitButton.dataset.loading;

	paymentMethodAction( 'edds_update_payment_method', e.target.dataset.source, data )
		.fail( function( response ) {
			submitButton.disabled = false;
			submitButton.value = submitButton.dataset.submit;
		} );
}

/**
 *
 * @param {Event} e
 */
function onDeletePaymentMethod( e ) {
	e.preventDefault();
	const loading = '<span class="edd-loading-ajax edd-loading"></span>';
	const linkText = e.target.innerText;
	e.target.innerHTML = loading;

	paymentMethodAction( 'edds_delete_payment_method', e.target.dataset.source )
		.fail( function( response ) {
			e.target.innerText = linkText;
		} );
}

/**
 *
 * @param {Event} e
 */
function onSetDefaultPaymentMethod( e ) {
	e.preventDefault();
	const loading = '<span class="edd-loading-ajax edd-loading"></span>';
	const linkText = e.target.innerText;
	e.target.innerHTML = loading;

	paymentMethodAction( 'edds_set_payment_method_default', e.target.dataset.source )
		.fail( function( response ) {
			e.target.innerText = linkText;
		} );
}

/**
 * Handles a notice (success or error) for card actions.
 *
 * @param {string} paymentMethodId
 * @param {Object} error Error with message to output.
 * @param {string} type Notice type.
 */
export function handleNotice( paymentMethodId, error, type = 'error' ) {
	// Create the new notice.
	const notice = generateNotice(
		( error && error.message ) ? error.message : edd_stripe_vars.generic_error,
		type
	);

	// Hide previous notices.
	forEach( document.querySelectorAll( '.edd-stripe-alert' ), function( alert ) {
		alert.remove();
	} );

	const item = document.getElementById( paymentMethodId + '_card_item' );

	// Show new notice.
	item.insertBefore( notice, item.querySelector( '.card-details' ) );
}
