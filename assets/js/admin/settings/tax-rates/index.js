/* global _, eddTaxRates */

/**
 * Internal dependencies.
 */
import TaxRate from './models/tax-rate.js';
import TaxRates from './collections/tax-rates.js';
import Manager from './views/manager.js';
import { jQueryReady } from 'utils/jquery.js';

/**
 * DOM ready.
 */
jQueryReady( () => {
	// Show notice if taxes are not enabled.
	const noticeEl = document.getElementById( 'edd-tax-disabled-notice' );

	if ( noticeEl ) {
		noticeEl.classList.add( 'notice' );
		noticeEl.classList.add( 'notice-warning' );
	}

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
