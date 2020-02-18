/* global Backbone, _ */

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

	/**
	 * @since 3.0
	 */
	getTaxCountry() {
		return false !== this.get( 'hasTax' )
			? this.get( 'hasTax' ).country
			: '';
	},

	/**
	 * @since 3.0
	 */
	getTaxRegion() {
		return false !== this.get( 'hasTax' )
			? this.get( 'hasTax' ).region
			: '';
	},

} );
