/* global Backbone */

/**
 * Internal dependencies
 */
import {
	OrderAdjustment,
	AdjustmentDiscount,
} from './';

/**
 * OrderAdjustmentDiscount
 *
 * A single Order Adjustment Discount.
 *
 * @since 3.0
 *
 * @class OrderAdjustmentDiscount
 * @augments Backbone.Model
 */
export const OrderAdjustmentDiscount = OrderAdjustment.extend( /** Lends Adjustment.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		...OrderAdjustment.prototype.defaults,

		objectType: 'order',
		type: 'discount',

		adjustment: new AdjustmentDiscount(),
	},

	/**
	 * @since 3.0
	 */
	idAttribute: 'typeId',

} );
