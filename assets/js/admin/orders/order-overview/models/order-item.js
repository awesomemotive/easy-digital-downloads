/* global Backbone */

/**
 * OrderItem
 *
 * A single Order Item.
 *
 * @since 3.0
 *
 * @class OrderItem
 * @augments Backbone.Model
 */
export const OrderItem = Backbone.Model.extend( /** Lends Item.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		id: '',
		name: '',
		priceId: 0,
		status: '',
		quantity: 1,
		amount: 0,
		subtotal: 0,
		discount: 0,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',

		// Unique identifier that for each item to allow the same
		// Download with different price options to be added to the Collection.
		//
		// Not present in the the database.
		//
		// @example 9_1
		eddUid: '',

		// Determines if this Order Item has manually set amounts.
		isAdjustingManually: false,
	},

	/**
	 * Sets the unique identifer to the `eddUid` attribute.
	 *
	 * @since 3.0
	 */
	idAttribute: 'eddUid',

} );
