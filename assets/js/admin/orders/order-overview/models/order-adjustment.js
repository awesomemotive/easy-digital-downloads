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
		id: 0,
		objectId: 0,
		objectType: '',
		typeId: 0,
		type: 'order',
		description: '',
		subtotal: 0,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',
		uuid: '',
	},

	/**
	 * Returns the `OrderAdjustment` total.
	 *
	 * @since 3.0
	 */
	getTotal() {
		return this.get( 'total' );
	},
} );
