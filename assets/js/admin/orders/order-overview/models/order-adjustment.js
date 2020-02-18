/* global Backbone */

/**
 * OrderAdjustment
 *
 * A single Order Adjustment.
 *
 * @since 3.0
 *
 * @class OrderAdjustment
 * @augments Backbone.Model
 */
export const OrderAdjustment = Backbone.Model.extend( /** Lends Adjustment.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		id: '',
		objectId: '',
		objectType: '',
		type: '',
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
	 * @since 3.0
	 */
	getTotal() {
		return this.get( 'total' );
	}

} );
