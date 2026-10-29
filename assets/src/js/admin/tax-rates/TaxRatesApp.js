/**
 * Root component for the tax rates manager.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useReducer, useEffect } from '@wordpress/element';
import { TaxRatesContext } from './state/context.js';
import { reducer, initialState } from './state/reducer.js';
import { useBeforeUnload } from '../hooks/use-before-unload.js';
import BulkActions from './components/BulkActions.js';
import TaxRatesTable from './components/TaxRatesTable.js';

/**
 * Root component for the tax rates manager.
 *
 * Provides the context store and wires up the beforeunload dirty warning
 * and the settings-form submit clean handler.
 *
 * @param {Object}   props
 * @param {Array}    props.initialRates  Normalized rate objects from eddTaxRates.rates.
 * @return {Element}
 */
const TaxRatesApp = ( { initialRates } ) => {
	const [ state, dispatch ] = useReducer( reducer, {
		...initialState,
		rates: initialRates,
	} );

	useBeforeUnload( state.isDirty );

	// Remove the dirty warning when the settings form is submitted.
	useEffect( () => {
		const submitBtn = document.querySelector( '.edd-settings-form #submit' );
		if ( ! submitBtn ) {
			return;
		}
		const handleSubmit = () => dispatch( { type: 'MARK_CLEAN' } );
		submitBtn.addEventListener( 'click', handleSubmit );
		return () => submitBtn.removeEventListener( 'click', handleSubmit );
	}, [] );

	return (
		<TaxRatesContext.Provider value={ { state, dispatch } }>
			<BulkActions />
			<TaxRatesTable />
		</TaxRatesContext.Provider>
	);
};

export default TaxRatesApp;
