/**
 * Tax rates state context.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { createContext, useContext } from '@wordpress/element';

export const TaxRatesContext = createContext( null );

export function useTaxRates() {
	return useContext( TaxRatesContext );
}
