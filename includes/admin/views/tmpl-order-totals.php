<?php
/**
 * Order Overview: Totals
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<tr>
	<td></td>
	<th colspan="{{ data.config.colspan }}">Subtotal</th>
	<td class="column-right">{{ data.subtotal }}</td>
</tr>

<# if ( false !== data.config.hasTax ) { #>
<tr>
	<td></td>
	<th colspan="{{ data.config.colspan }}">Tax</th>
	<td class="column-right">{{ data.tax }}</td>
</tr>
<# } #>

<tr>
	<td></td>
	<th colspan="{{ data.config.colspan }}">Total</th>
	<td class="column-right">
		<span class="total">{{ data.total }}</span>
	</td>
</tr>
