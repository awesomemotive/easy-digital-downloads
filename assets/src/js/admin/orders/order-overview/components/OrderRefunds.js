/* global eddAdminOrderOverview */

/**
 * Order overview rows for refunds of the order.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';

const OrderRefunds = () => {
	const { state } = useOrderOverview();
	const { refunds } = state;
	const { currency } = state.formatters;
	const { i18n, urls } = eddAdminOrderOverview;
	const colspan = state.hasQuantity ? 2 : 1;

	if ( 0 === refunds.length ) {
		return null;
	}

	return (
		<tbody className="edd-order-overview-summary__refunds">
			{ refunds.map( ( refund ) => (
				<tr key={ refund.id } className="is-expanded">
					<td></td>

					<td colSpan={ colspan } className="column-primary">
						<a href={ `${ urls?.viewRefund ?? '' }&id=${ refund.id }` }>
							{ refund.number }
						</a>
						{ ' — ' }
						{ i18n.refund }
						<br />
						<small>{ refund.dateCreatedi18n }</small>
					</td>

					<td className="column-right" data-colname={ i18n.total }>
						{ currency.format( refund.total ) }
					</td>
				</tr>
			) ) }
		</tbody>
	);
};

export default OrderRefunds;
