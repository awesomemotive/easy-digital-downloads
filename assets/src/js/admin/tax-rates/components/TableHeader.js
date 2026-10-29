/* global eddTaxRates */

/**
 * Tax rates table header and footer rows.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useTaxRates } from '../state/context.js';

export const TableHeaderRow = () => {
	const { state, dispatch } = useTaxRates();
	const allSelected =
		state.rates.length > 0 && state.selected.length === state.rates.length;

	const handleSelectAll = ( e ) => {
		dispatch( { type: 'SELECT_ALL', payload: e.target.checked } );
	};

	const { i18n } = eddTaxRates;

	return (
		<tr>
			<td className="edd-tax-rates-table-checkbox check-column">
				<input
					type="checkbox"
					checked={ allSelected }
					onChange={ handleSelectAll }
				/>
			</td>
			<th className="edd-tax-rates-table-country">{ i18n.country }</th>
			<th>{ i18n.region }</th>
			<th className="edd-tax-rates-table-rate">{ i18n.rate }</th>
			<th className="edd-tax-rates-table-actions">{ i18n.actions }</th>
		</tr>
	);
};

const TableHeader = ( { tagName: Tag = 'thead' } ) => (
	<Tag>
		<TableHeaderRow />
	</Tag>
);

export default TableHeader;
