/* global eddTaxRates */

/**
 * Placeholder row shown when there are no tax rates.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

const EmptyRow = () => (
	<tr className="edd-tax-rate-row edd-tax-rate-row--is-empty">
		<td colSpan="5">{ eddTaxRates.i18n.noRates }</td>
	</tr>
);

export default EmptyRow;
