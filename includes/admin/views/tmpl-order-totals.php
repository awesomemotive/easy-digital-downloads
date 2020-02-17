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
	<th colspan="{{ data.config.colspan }}"><?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?></th>
</tr>

<# if ( false !== data.config.hasTax ) { #>
<tr>
	<td></td>
	<th colspan="{{ data.config.colspan }}"><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?></th>
</tr>
<# } #>

<tr>
	<td></td>
	<th colspan="{{ data.config.colspan }}"><?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?></th>
	<td class="column-right">
		<span class="total">{{ data.total }}</span>
	</td>
</tr>
