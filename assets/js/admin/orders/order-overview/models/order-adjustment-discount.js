/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustment } from './';

/**
 * OrderAdjustmentDiscount
 *
 * @since 3.0
 *
 * @class OrderAdjustmentDiscount
 * @augments Backbone.Model
 */
export const OrderAdjustmentDiscount = OrderAdjustment.extend( {
	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderAdjustmentDiscount
	 */
	defaults: {
		...OrderAdjustment.prototype.defaults,
		type: 'discount',
	},

	/**
	 * Returns the `OrderAdjustmentDiscount`'s total based on the current values
	 * of all `OrderItems` discounts.
	 *
	 * @since 3.0
	 *
	 * @return {number} `OrderAdjustmentDiscount` total.
	 */
	getTotal() {
		let total = 0;

		const state = this.get( 'state' );
		const { models: items } = state.get( 'items' );

		items.forEach( ( item ) => {
			const adjustments = item.get( 'adjustments' );
			const discounts = _.filter( adjustments, ( adjustment ) => {
				return adjustment.description === this.get( 'description' );
			} );

			discounts.forEach( ( discount ) => {
				total += +discount.total;
			} );
		} );

		return total;
	},
} );
