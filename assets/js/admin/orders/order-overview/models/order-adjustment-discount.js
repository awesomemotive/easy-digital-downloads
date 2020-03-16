/* global _ */

/**
 * Internal dependencies
 */
import { OrderAdjustment } from './order-adjustment.js';

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

	idAttribute: 'typeId',

	/**
	 * Returns the `OrderAdjustmentDiscount`'s amount based on the current values
	 * of all `OrderItems` discounts.
	 *
	 * @since 3.0
	 *
	 * @return {number} `OrderAdjustmentDiscount` amount.
	 */
	getAmount() {
		let amount = 0;

		const state = this.get( 'state' );
		const { models: items } = state.get( 'items' );

		items.forEach( ( item ) => {
			const discount = item.get( 'adjustments' ).findWhere( {
				typeId: this.get( 'typeId' ),
			} );

			if ( undefined !== discount ) {
				amount += +discount.get( 'subtotal' );
			}
		} );

		return amount;
	},
} );
