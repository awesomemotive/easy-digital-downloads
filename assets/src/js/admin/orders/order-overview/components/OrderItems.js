/**
 * Order overview item rows.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import OrderItem from './OrderItem.js';
import NoOrderItems from './NoOrderItems.js';

const OrderItems = () => {
	const { state } = useOrderOverview();
	const { items } = state;

	return (
		<tbody className="edd-order-overview-summary__items">
			{ 0 === items.length
				? <NoOrderItems />
				: items.map( ( item ) => (
					<OrderItem key={ item.id } item={ item } />
				) )
			}
		</tbody>
	);
};

export default OrderItems;
