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
			// Find all `OrderItem` internall tracked Discounts that match this `OrderAdjustment`
			const _discounts = item.get( '_discounts' ).filter( ( _discount ) => {
				return _discount.code === this.get( 'description' );
			}, 0 );

			// Sum all amounts.
			_.each( _discounts, ( _discount ) => {
				total += +_discount.amount;
			} );
		} );

		return total;
	},
} );
