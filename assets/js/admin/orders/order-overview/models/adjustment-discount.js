/* global Backbone */

/**
 * Internal dependencies
 */
import {
	Adjustment,
} from './';

/**
 * AdjustmentDiscount
 *
 * A single Order Discount.
 *
 * @since 3.0
 *
 * @class AdjustmentDiscount
 * @augments Adjustment
 */
export const AdjustmentDiscount = Adjustment.extend( /** Lends AdjustmentDiscount.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		...Adjustment.prototype.defaults,

		parent: 0,
		type: 'discount',

		productRequirements: [],
		productExclusions: [],
		productCondition: '',
	},

} );
