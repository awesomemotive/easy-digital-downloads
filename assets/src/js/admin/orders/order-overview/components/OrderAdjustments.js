/**
 * Order overview rows for adjustments of the given types.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useOrderOverview } from '../state/context.js';
import OrderAdjustmentRow from './OrderAdjustmentRow.js';

/**
 * Renders a `<tbody>` of OrderAdjustment rows filtered by type.
 *
 * @param {Object}   props
 * @param {string[]} props.types  Adjustment types to include.
 * @param {string}   props.className  CSS class for the tbody.
 * @return {Element|null}
 */
const OrderAdjustments = ( { types, className } ) => {
	const { state } = useOrderOverview();

	const visible = state.adjustments.filter( ( adj ) => types.includes( adj.type ) );

	if ( 0 === visible.length ) {
		return null;
	}

	return (
		<tbody className={ className }>
			{ visible.map( ( adj ) => (
				<OrderAdjustmentRow key={ adj.id } adj={ adj } />
			) ) }
		</tbody>
	);
};

export default OrderAdjustments;
