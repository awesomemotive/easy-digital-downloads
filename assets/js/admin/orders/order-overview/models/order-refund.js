/* global Backbone */

/**
 * OrderRefund
 *
 * @since 3.0
 *
 * @class OrderRefund
 * @augments Backbone.Model
 */
export const OrderRefund = Backbone.Model.extend( {
	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderAdjustment
	 */
	defaults: {
		id: 0,
		number: '',
		total: 0,
		dateCreated: '',
		dateCreatedi18n: '',
	},
} );
