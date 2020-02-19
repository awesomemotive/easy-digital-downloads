/* global Backbone */

/**
 * State
 *
 * Leverages `Backbone.Model` and subsequently `Backbone.Events`
 * to easily track changes to top level state changes.
 *
 * @since 3.0
 *
 * @class State
 * @augments Backbone.Model
 */
export const State = Backbone.Model.extend( /** Lends State.prototype */ {

	/**
	 * @since 3.0
	 *
	 * @typedef {Object} State
	 */
	defaults: {
		isAdding: false,
		hasQuantity: false,
		hasTax: false,
		items: [],
		adjustments: [],
	},

	/**
	 * Returns the current tax rate's country code.
	 *
	 * @since 3.0
	 *
	 * @return {string} Tax rate country code.
	 */
	getTaxCountry() {
		return false !== this.get( 'hasTax' )
			? this.get( 'hasTax' ).country
			: '';
	},

	/**
	 * Returns the current tax rate's region.
	 *
	 * @since 3.0
	 *
	 * @return {string} Tax rate region.
	 */
	getTaxRegion() {
		return false !== this.get( 'hasTax' )
			? this.get( 'hasTax' ).region
			: '';
	},
} );
