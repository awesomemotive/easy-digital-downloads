/**
 * Order overview state context.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { createContext, useContext } from '@wordpress/element';

export const OrderOverviewContext = createContext( null );

export function useOrderOverview() {
	return useContext( OrderOverviewContext );
}
