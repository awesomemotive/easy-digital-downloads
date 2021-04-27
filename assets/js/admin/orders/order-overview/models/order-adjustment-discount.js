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

	/**
	 * @since 3.0
	 */
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

		// Return stored amount if viewing an existing Order.
		if ( false === state.get( 'isAdding' ) ) {
			return OrderAdjustment.prototype.getAmount.apply( this, arguments );
		}

		const { models: items } = state.get( 'items' );
		const { number } = state.get( 'formatters' );

		items.forEach( ( item ) => {
			const discount = item.get( 'adjustments' ).findWhere( {
				typeId: this.get( 'typeId' ),
			} );

			if ( undefined !== discount ) {
				amount += +number.format( discount.get( 'subtotal' ) );
			}
		} );

		return amount;
	},

	/**
	 * Returns the `OrderAdjustment` total.
	 *
	 * @since 3.0
	 */
	getTotal() {
		return this.getAmount();
	},
} );
