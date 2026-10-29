/**
 * Tax rates table.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useTaxRates } from '../state/context.js';
import TableHeader, { TableHeaderRow } from './TableHeader.js';
import TaxRateRow from './TaxRateRow.js';
import EmptyRow from './EmptyRow.js';

const TaxRatesTable = () => {
	const { state } = useTaxRates();

	const hasVisibleRates = state.showAll
		? state.rates.length > 0
		: state.rates.some( ( r ) => r.status === 'active' || r.unsaved );

	return (
		<table
			id="edd_tax_rates"
			className={ `wp-list-table widefat fixed tax-rates${ state.showAll ? ' has-inactive' : '' }` }
		>
			<TableHeader tagName="thead" />
			<tbody>
				{ ! hasVisibleRates && <EmptyRow /> }
				{ /* Inactive rows render hidden by CSS, since the save handler only updates posted rows. */ }
				{ state.rates.map( ( rate ) => (
					<TaxRateRow key={ rate.id } rate={ rate } />
				) ) }
			</tbody>
			<tfoot>
				<TableHeaderRow />
			</tfoot>
		</table>
	);
};

export default TaxRatesTable;
