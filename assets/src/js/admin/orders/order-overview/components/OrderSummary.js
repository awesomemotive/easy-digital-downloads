/* global eddAdminOrderOverview */

/**
 * Order overview summary table.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import OrderItems from './OrderItems.js';
import OrderAdjustments from './OrderAdjustments.js';
import OrderSubtotal from './OrderSubtotal.js';
import OrderTax from './OrderTax.js';
import OrderTotal from './OrderTotal.js';
import OrderRefunds from './OrderRefunds.js';

const OrderSummary = () => {
	const { state } = useOrderOverview();
	const { i18n } = eddAdminOrderOverview;

	return (
		<table
			id="edd-order-overview-summary"
			className={ `widefat wp-list-table edd-order-overview-summary${ state.isRefund ? ' edd-order-overview-summary--refund' : '' }` }
		>
			<thead>
				<tr>
					<th className="column-name column-primary">
						{ i18n.downloadSingular }
					</th>
					<th className="column-amount">
						{ i18n.unitPrice }
					</th>
					{ state.hasQuantity && (
						<th className="column-quantity">
							{ i18n.quantity }
						</th>
					) }
					<th className="column-subtotal column-right">
						{ i18n.amount }
					</th>
				</tr>
			</thead>

			<OrderItems />

			<OrderAdjustments
				types={ [ 'discount', 'fee' ] }
				className="edd-order-overview-summary__adjustments"
			/>

			<OrderSubtotal />

			<OrderTax />

			<OrderAdjustments
				types={ [ 'credit' ] }
				className="edd-order-overview-summary__credits"
			/>

			<OrderTotal />

			<OrderRefunds />
		</table>
	);
};

export default OrderSummary;
