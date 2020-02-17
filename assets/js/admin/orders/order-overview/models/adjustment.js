/* global Backbone */

/**
 * Adjustment
 *
 * A single Order Adjustment.
 *
 * @since 3.0
 *
 * @class Adjustment
 * @augments Backbone.Model
 */
export const Adjustment = Backbone.Model.extend( /** Lends Item.prototype */ {

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
	 * Sets the unique identifer to the `eddUid` attribute.
	 *
	 * @since 3.0
	 */
	idAttribute: 'objectId',

} );
