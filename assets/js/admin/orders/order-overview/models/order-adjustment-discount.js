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
export const OrderAdjustmentDiscount = OrderAdjustment.extend(
	/** Lends OrderAdjustmentDiscount.prototype */ {
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
		 * of all `OrderItems`.
		 *
		 * @since 3.0
		 *
		 * @return {number} `OrderAdjustmentDiscount` total.
		 */
		getTotal() {
			let total = 0;

			const { state } = this.get( 'options' );

			const items = state.get( 'items' );

			items.models.forEach( ( item ) => {
				total += +item.get( 'discount' );
			} );

			return total;
		},
	}
);
