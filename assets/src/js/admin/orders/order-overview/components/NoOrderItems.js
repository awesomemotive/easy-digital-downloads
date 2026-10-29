/**
 * Placeholder row shown when an order has no items.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';

const NoOrderItems = () => {
	const { state } = useOrderOverview();
	const colspan = state.hasQuantity ? 4 : 3;

	return (
		<tr>
			<td colSpan={ colspan }>
				{ globalThis.eddAdminOrderOverview?.i18n.noOrderItems }
			</td>
		</tr>
	);
};

export default NoOrderItems;
