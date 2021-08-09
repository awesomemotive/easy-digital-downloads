/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustments } from './order-adjustments.js';

/**
 * OrderDiscountsFees
 *
 * @since 3.0
 *
 * @class OrderDiscountsFees
 * @augments wp.Backbone.View
 */
export const OrderDiscountsFees = OrderAdjustments.extend( {
	/**
	 * Returns Discount and Fee adjustments.
	 *
	 * @since 3.0.0
	 */
	getAdjustments() {
		const { state } = this.options;

		return state.get( 'adjustments' ).filter(
			( adjustment ) => {
				return [ 'discount', 'fee' ].includes( adjustment.get( 'type' ) );
			}
		);
	},
} );
