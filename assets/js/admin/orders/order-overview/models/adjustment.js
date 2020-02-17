/* global Backbone */

/**
 * Adjustment
 *
 * A single Adjustment.
 *
 * @since 3.0
 *
 * @class Adjustment
 * @augments Backbone.Model
 */
export const Adjustment = Backbone.Model.extend( /** Lends Adjustment.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		id: '',
		parent: '',
		name: '',
		code: '',
		status: '',
		type: '',
		scope: '',
		amountType: '',
		amount: 0,
		description: '',
	},

} );
