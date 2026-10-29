/* global eddTaxRates */

/**
 * Tax rates table row for a single rate.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useTaxRates } from '../state/context.js';

function formatAmount( amount ) {
	const num = Number.parseFloat( amount ) || 0;
	return `${ num.toFixed( 2 ) }%`;
}

const TaxRateRow = ( { rate } ) => {
	const { state, dispatch } = useTaxRates();
	const { i18n } = eddTaxRates;
	const isSelected = state.selected.includes( rate.id );

	const handleSelect = ( e ) => {
		dispatch( { type: 'SELECT_RATE', payload: { id: rate.id, checked: e.target.checked } } );
	};

	const handleRemove = ( e ) => {
		e.preventDefault();
		dispatch( { type: 'REMOVE_RATE', payload: rate.id } );
	};

	const handleDeactivate = ( e ) => {
		e.preventDefault();
		dispatch( { type: 'SET_STATUS', payload: { id: rate.id, status: 'inactive' } } );
	};

	const handleActivate = ( e ) => {
		e.preventDefault();

		const addingRegion = rate.region;
		const addingCountry = rate.country;
		const addingGlobal = '' === rate.region;

		const hasDuplicate = state.rates.some( ( r ) =>
			r.id !== rate.id &&
			r.status === 'active' &&
			r.region === addingRegion &&
			r.country === addingCountry &&
			( '' === r.region ) === addingGlobal
		);

		if ( hasDuplicate ) {
			const regionString = '' === rate.region ? '' : `: ${ rate.region }`;
			const taxRateString = `${ rate.country }${ regionString }`;
			// eslint-disable-next-line no-alert
			alert( i18n.duplicateRate.replace( '%s', `"${ taxRateString }"` ) );
			return;
		}

		dispatch( { type: 'SET_STATUS', payload: { id: rate.id, status: 'active' } } );
	};

	let actionButton;
	if ( rate.unsaved ) {
		actionButton = (
			<button className="button-link remove" onClick={ handleRemove }>
				{ i18n.remove }
			</button>
		);
	} else if ( rate.status === 'active' ) {
		actionButton = (
			<button className="button-link deactivate" onClick={ handleDeactivate }>
				{ i18n.deactivate }
			</button>
		);
	} else {
		actionButton = (
			<button className="button-link activate" onClick={ handleActivate }>
				{ i18n.activate }
			</button>
		);
	}

	return (
		<tr className={ `edd-tax-rate-row edd-tax-rate-row--${ rate.status }` }>
			<th className="edd-tax-rates-table-checkbox check-column">
				<input type="checkbox" checked={ isSelected } onChange={ handleSelect } />
				<input type="hidden" name={ `tax_rates[${ rate.id }][id]` } value={ rate.id } />
			</th>

			<td className="edd-tax-rates-table-country" data-colname={ i18n.country }>
				{ rate.country || '*' }
				<input type="hidden" name={ `tax_rates[${ rate.id }][country]` } value={ rate.country } />
			</td>

			<td data-colname={ i18n.region }>
				{ rate.global ? (
					<>
						&mdash;{/*
						*/}<input type="hidden" name={ `tax_rates[${ rate.id }][global]` } value="1" />
					</>
				) : (
					<>
						{ rate.region || '—' }
						<input type="hidden" name={ `tax_rates[${ rate.id }][state]` } value={ rate.region } />
					</>
				) }
			</td>

			<td data-colname={ i18n.rate }>
				{ formatAmount( rate.amount ) }
				<input type="hidden" name={ `tax_rates[${ rate.id }][rate]` } value={ rate.amount } />
			</td>

			<td className="edd-tax-rates-table-actions" data-colname={ i18n.actions }>
				{ actionButton }
				<input type="hidden" name={ `tax_rates[${ rate.id }][status]` } value={ rate.status } />
			</td>
		</tr>
	);
};

export default TaxRateRow;
