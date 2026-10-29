/* global eddAdminOrderOverview */

/**
 * Order overview tax row.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import { getTax, getTaxConfig, formatTaxLocation, hasTaxableItems } from '../state/selectors.js';
import { recalculateAmounts } from '../utils/update-amounts.js';

const OrderTax = () => {
	const { state, dispatch } = useOrderOverview();
	const { currency } = state.formatters;
	const { i18n } = eddAdminOrderOverview;
	const { hasNewTaxRate } = state;
	const taxConfig = getTaxConfig( state );
	const colspan = state.hasQuantity ? 2 : 1;

	const hasTaxValue = null !== taxConfig && false !== taxConfig.rate;
	const tax         = getTax( state );

	if ( ! hasTaxValue ) {
		return (
			<tbody hidden>
				<tr>
					<td>
						<input type="hidden" value={ tax } name="tax" />
						<input type="hidden" value="" name="tax_rate" />
					</td>
				</tr>
			</tbody>
		);
	}

	const taxRate = taxConfig.rate ?? '';

	const handleDismiss = () => {
		dispatch( { type: 'DISMISS_TAX_NOTICE' } );
	};

	const handleUpdateAmounts = ( e ) => {
		e.preventDefault();
		dispatch( { type: 'UPDATE_ADJUSTMENT_TAX' } );
		recalculateAmounts( state, dispatch, handleDismiss );
	};

	const taxableItems = hasTaxableItems( state );

	return (
		<tbody className="edd-order-overview-summary__tax">
			<tr className="is-expanded">
				<td>
					<input type="hidden" value={ tax }     name="tax" />
					<input type="hidden" value={ taxRate } name="tax_rate" />
				</td>
				<td colSpan={ colspan } className="column-primary">
					{ ( taxConfig.country || taxRate ) && (
						<>
							{ formatTaxLocation( taxConfig ) }
							<br />
						</>
					) }
					<small>{ i18n.tax }</small>
				</td>
				<td className="column-right" data-colname={ i18n.amount }>
					{ currency.format( tax ) }
				</td>
			</tr>

			{ hasNewTaxRate && taxableItems && (
				<tr className="is-expanded">
					<td></td>
					<td colSpan={ colspan + 1 } style={ { padding: 0 } } className="column-primary">
						<div id="notice-tax-change" className="notice notice-warning is-dismissible">
							<p>
								{ i18n.taxRateUpdated.replace( '%1$s', `${ taxRate }%` ) }
							</p>
							<p>
								<button
									type="button"
									className="button button-secondary button-small update-amounts"
									onClick={ handleUpdateAmounts }
									disabled={ state.isFetching }
								>
									{ i18n.updateAmounts }
								</button>
							</p>
							<button type="button" className="notice-dismiss" onClick={ handleDismiss }>
								<span className="screen-reader-text">
									{ i18n.dismissNotice }
								</span>
							</button>
						</div>
					</td>
				</tr>
			) }
		</tbody>
	);
};

export default OrderTax;
