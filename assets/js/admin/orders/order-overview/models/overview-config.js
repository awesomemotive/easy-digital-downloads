/* global Backbone */

/**
 * Overview configuration.
 *
 * Mainly used for high-level state management.
 *
 * @since 3.0
 *
 * @class OverviewConfig
 * @augments Backbone.Model
 */
export const OverviewConfig = Backbone.Model.extend( /** Lends OverviewConfig.prototype */ {

	/**
	 * @since 3.0
	 */
	defaults: {
		isAdding: false,
		hasQuantity: false,
		hasTax: false,
		items: [],
	},

} );
