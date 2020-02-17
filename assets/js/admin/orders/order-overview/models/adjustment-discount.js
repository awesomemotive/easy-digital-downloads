/* global Backbone */

/**
 * Internal dependencies
 */
import {
	Adjustment,
} from './';

/**
 * Discount
 *
 * A single Order Discount.
 *
 * @since 3.0
 *
 * @class Discount
 * @augments Backbone.Model
 */
export const Discount = Adjustment.extend( /** Lends Adjustment.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		...Adjustment.prototype.defaults,

		productRequirements: [],
		productExclusions: [],
		productCondition: '',
	},

} );
