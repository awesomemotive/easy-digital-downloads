/* global Backbone */

/**
 * State management.
 *
 * @since 3.0
 *
 * @class State
 * @augments Backbone.Model
 */
export const State = Backbone.Model.extend( /** Lends State.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		isAdding: false,
		hasQuantity: false,
		hasTax: false,
		items: [],
		adjustments: [],
	},

} );
