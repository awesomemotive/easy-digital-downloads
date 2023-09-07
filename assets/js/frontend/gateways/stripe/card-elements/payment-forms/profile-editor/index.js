/**
 * Internal dependencies
 */
import { paymentMethodActions } from './payment-method-actions.js';
import { paymentForm } from './payment-form.js';

export function setup() {
	if ( ! document.getElementById( 'edd-stripe-manage-cards' ) ) {
		return;
	}

	paymentMethodActions();
	paymentForm();
}
