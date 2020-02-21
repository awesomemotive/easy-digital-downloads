/* global Backbone */

/**
 * OrderAdjustment
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments Backbone.Model
 */
export const OrderAdjustment = Backbone.Model.extend( {
	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderAdjustment
	 */
	defaults: {
		id: '',
		objectId: '',
		objectType: '',
		type: 'order',
		typeId: 0,
		description: '',
		subtotal: 1,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',
	},

	/**
	 * @since 3.0
	 */
	idAttribute: 'typeId',

	/**
	 * Returns the `OrderAdjustment` total.
	 *
	 * @since 3.0
	 */
	getTotal() {
		return this.get( 'total' );
	},
} );
