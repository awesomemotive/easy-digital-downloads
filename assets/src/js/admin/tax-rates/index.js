/* global eddTaxRates */
import { createRoot } from '@wordpress/element';
import TaxRatesApp from './TaxRatesApp.js';

document.addEventListener( 'DOMContentLoaded', () => {
	const root = document.getElementById( 'edd-admin-tax-rates' );
	if ( ! root ) {
		return;
	}

	// Show notice if taxes are not enabled.
	const noticeEl = document.getElementById( 'edd-tax-disabled-notice' );
	if ( noticeEl ) {
		noticeEl.classList.add( 'notice', 'notice-warning' );
	}

	// Normalize rate data from PHP into the shape the React app expects.
	const initialRates = ( eddTaxRates.rates ?? [] ).map( ( rate ) => ( {
		id:      String( rate.id ),
		country: rate.country ?? '',
		region:  rate.state ?? '',
		global:  'country' === rate.scope,
		amount:  rate.amount,
		status:  rate.status,
		unsaved: false,
	} ) );

	createRoot( root ).render( <TaxRatesApp initialRates={ initialRates } /> );
} );
