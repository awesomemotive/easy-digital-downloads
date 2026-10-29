/* global eddTaxRates */

/**
 * Tax rates table navigation: bulk status changes and the add rate dialog.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useRef, useState } from '@wordpress/element';
import { useTaxRates } from '../state/context.js';
import AddRateDialog from './AddRateDialog.js';

const BulkActions = () => {
	const { state, dispatch } = useTaxRates();
	const { i18n } = eddTaxRates;
	const selectRef = useRef( null );
	const [ dialogOpen, setDialogOpen ] = useState( false );

	const handleApply = ( e ) => {
		e.preventDefault();
		const status = selectRef.current?.value;
		if ( ! status ) {
			return;
		}
		dispatch( { type: 'BULK_STATUS', payload: { status, ids: state.selected } } );
	};

	const handleShowHide = ( e ) => {
		dispatch( { type: 'TOGGLE_SHOW_ALL' } );
		// TaxRatesTable sets the has-inactive class from this state.
	};

	return (
		<>
		<div className="tablenav top">
			<div className="edd-admin-tax-rates__tablenav--left actions">
				<select id="edd-admin-tax-rates-table-bulk-actions" ref={ selectRef }>
					<option value="">{ i18n.bulkActions }</option>
					<option value="active">{ i18n.activate }</option>
					<option value="inactive">{ i18n.deactivate }</option>
				</select>
				<button
					className="button edd-admin-tax-rates-table-filter"
					onClick={ handleApply }
					type="button"
				>
					{ i18n.apply }
				</button>
			</div>
			<div className="edd-admin-tax-rates__tablenav--right">
				<label className="edd-toggle edd-admin-tax-rates-table-hide">
					<span className="label">{ i18n.showDeactivated }</span>
					<input
						type="checkbox"
						id="hide-deactivated"
						checked={ state.showAll }
						onChange={ handleShowHide }
					/>
				</label>
				<button
					id="tax_rate_submit"
					className="button button-primary"
					type="button"
					onClick={ () => setDialogOpen( true ) }
				>
					{ i18n.addRate }
				</button>
			</div>
		</div>

		{ dialogOpen && (
			<AddRateDialog onClose={ () => setDialogOpen( false ) } />
		) }
		</>
	);
};

export default BulkActions;
