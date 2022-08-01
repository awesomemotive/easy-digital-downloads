<?php
/**
 * Order Overview: Subtotal
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
?>

<tr class="is-expanded">
	<td></td>
	<td colspan="{{ data.config.colspan }}" class="column-primary">
		<?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>
	</td>
	<td class="column-right" data-colname="<?php esc_attr_e( 'Amount', 'easy-digital-downloads' ); ?>">
		{{ data.subtotalCurrency }}
	</td>
</tr>

<input type="hidden" value="{{ data.subtotal }}" name="subtotal" />
