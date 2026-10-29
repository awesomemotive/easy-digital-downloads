/* global eddAdminOrderOverview */

/**
 * Order overview subtotal row.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import { getSubtotal } from '../state/selectors.js';

const OrderSubtotal = () => {
	const { state } = useOrderOverview();
	const { currency } = state.formatters;
	const { i18n } = eddAdminOrderOverview;
	const colspan  = state.hasQuantity ? 2 : 1;
	const subtotal = getSubtotal( state );

	return (
		<tbody className="edd-order-overview-summary__subtotal">
			<tr className="is-expanded">
				<td>
					<input type="hidden" value={ subtotal } name="subtotal" />
				</td>
				<td colSpan={ colspan } className="column-primary">
					{ i18n.subtotal }
				</td>
				<td className="column-right" data-colname={ i18n.amount }>
					{ currency.format( subtotal ) }
				</td>
			</tr>
		</tbody>
	);
};

export default OrderSubtotal;
