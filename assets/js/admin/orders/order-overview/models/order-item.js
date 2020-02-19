/* global Backbone, _ */

/**
 * OrderItem
 *
 * @since 3.0
 *
 * @class OrderItem
 * @augments Backbone.Model
 */
export const OrderItem = Backbone.Model.extend( /** Lends OrderItem.prototype */ {

	/**
	 * @since 3.0
	 *
	 * @typedef {Object} OrderItem
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

		// Determines if this `OrderItem` has manually set amounts.
		isAdjustingManually: false,
	},

	/**
	 * Sets the Model's unique identifer to the `eddUid` attribute.
	 *
	 * @since 3.0
	 */
	idAttribute: 'eddUid',

	/**
	 * Retrieves amounts for the `OrderItem` based on other `OrderItem`s and `OrderAdjustment`s.
	 *
	 * @since 3.0
	 *
	 * @param {Object} args Arguments to pass as data in the XHR request.
	 * @param {string} args.country Country code to determine tax rate.
	 * @param {string} args.region Region to determine tax rate.
	 * @param {Array} args.itemIds List of current `OrderItems`s.
	 * @param {Array} args.discountIds List of `OrderAdjustmentDiscount`s to calculate amounts against.
	 * @return {$.promise} A jQuery promise that represents the request.
	 */
	getAmounts( {
		country = '',
		region = '',
		itemIds = [],
		discountIds = [],
	} ) {
		const id = this.get( 'id' );
		const priceId = this.get( 'priceId' );
		const quantity = this.get( 'quantity' );

		const ids = [ id, ...itemIds ];
		const discounts = discountIds;
		
		return wp.ajax.send(
			'edd-admin-order-get-item-amounts', 
			{
				data: {
					id,
					ids,
					priceId,
					quantity,
					country,
					region,
					discounts,
				},
			},
		);
	},
} );
