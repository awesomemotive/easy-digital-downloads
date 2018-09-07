/* global Backbone */

/**
 * Internal dependencies.
 */
import TaxRate from './../models/tax-rate.js';

/**
 * A collection of multiple tax rates.
 */
const TaxRates = Backbone.Collection.extend( {
	// Map the model.
	model: TaxRate,

	/**
	 * Set initial state.
	 */
	initialize: function() {
		this.showAll = false;
		this.selected = [];
	},
} );

export default TaxRates;
