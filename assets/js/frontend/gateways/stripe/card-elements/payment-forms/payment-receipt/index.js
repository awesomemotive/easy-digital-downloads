/**
 * Internal dependencies
 */
// eslint-disable @wordpress/dependency-group
import { paymentMethods } from '../../payment-methods';
// eslint-enable @wordpress/dependency-group

import { paymentForm } from './payment-form.js';

export function setup() {
	if ( ! document.getElementById( 'edds-update-payment-method' ) ) {
		return;
	}

	paymentForm();
	paymentMethods();
}
