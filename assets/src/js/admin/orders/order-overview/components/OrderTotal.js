/* global eddAdminOrderOverview */

/**
 * Order overview total row.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import { getTotal, getDiscount, hasManualAdjustment } from '../state/selectors.js';

const OrderTotal = () => {
	const { state } = useOrderOverview();
	const { currency } = state.formatters;
	const { i18n } = eddAdminOrderOverview;
	const colspan  = state.hasQuantity ? 2 : 1;

	const total    = getTotal( state );
	const discount = getDiscount( state );
	const isManual = hasManualAdjustment( state );

	return (
		<tbody className="edd-order-overview-summary__total">
			<tr className="is-expanded edd-order-overview-summary__total-total">
				<td>
					<input type="hidden" value={ discount } name="discount" />
					<input type="hidden" value={ total }    name="total" />
				</td>
				<td colSpan={ colspan } className="column-primary">
					{ i18n.total }
					{ isManual && (
						<>
							<br />
							<small>{ i18n.manualAdjustmentNote }</small>
						</>
					) }
				</td>
				<td className="column-right" data-colname={ i18n.amount }>
					<span className={ `total${ total < 0 ? ' is-negative' : '' }` }>
						{ currency.format( total ) }
					</span>
				</td>
			</tr>
		</tbody>
	);
};

export default OrderTotal;
