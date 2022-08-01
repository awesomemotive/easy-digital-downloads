/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustments } from './order-adjustments.js';

/**
 * OrderCredits
 *
 * @since 3.0
 *
 * @class OrderCredits
 * @augments wp.Backbone.View
 */
export const OrderCredits = OrderAdjustments.extend( {
	/**
	 * Returns Credit adjustments.
	 *
	 * @since 3.0.0
	 */
	getAdjustments() {
		const { state } = this.options;

		return state.get( 'adjustments' ).getByType( 'credit' );
	},
} );
