/* global _, eddTaxRates */

/**
 * Internal dependencies.
 */
import TaxRate from './models/tax-rate.js';
import TaxRates from './collections/tax-rates.js';
import Manager from './views/manager.js';

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	// Start manager with a blank collection.
	const manager = new Manager( {
		collection: new TaxRates(),
	} );

	const rates = [];

	// Normalize rate data.
	_.each( eddTaxRates.rates, ( rate ) => rates.push( {
		id: rate.id,
		country: rate.name,
		region: rate.description,
		global: 'country' === rate.scope,
		amount: rate.amount,
		status: rate.status,
	} ) );

	// Add initial rates.
	manager.collection.set( rates, {
		silent: true,
	} );

	// Render manager.
	manager.render();
} );
