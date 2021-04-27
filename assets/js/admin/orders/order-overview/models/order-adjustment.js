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
		type: '',
		description: '',
		subtotal: 0,
		tax: 0,
		total: 0,
		dateCreated: '',
		dateModified: '',
		uuid: '',
	},

	/**
	 * Returns the `OrderAdjustment` amount.
	 *
	 * Separate from subtotal or total calculation so `OrderAdjustmentDiscount`
	 * can be calculated independently.
	 *
	 * @see OrderAdjustmentDiscount.prototype.getAmount()
	 *
	 * @since 3.0
	 */
	getAmount() {
		return this.get( 'subtotal' );
	},

	/**
	 * Returns the `OrderAdjustment` total.
	 *
	 * @since 3.0
	 */
	getTotal() {
		return this.get( 'subtotal' ) + this.get( 'tax' );
	},
} );
