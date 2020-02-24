<?php
/**
 * Order Overview: Adjustment
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$view_url = edd_get_admin_url(
	array(
		'page'       => 'edd-discounts',
		'edd-action' => 'edit_discount'
	),
);
?>

<td></td>

<th class="column-name" colspan="{{ data.config.colspan }}">
	<# if ( true === data.state.isAdding ) { #>
	<button class="button-link delete">
		<span class="dashicons dashicons-no"></span>
	</button>
	<# } #>

	<# if ( 'credit' === data.type ) { #>
		<?php esc_html_e( 'Order Credit', 'easy-digital-downloads' ); ?>
	<# } else { #>
		<# if ( false !== data.orderItem ) { #>
			{{ data.orderItem.productName }}
		<# } else { #>
			<?php esc_html_e( 'Order Fee', 'easy-digital-downloads' ); ?>
		<# } #>
	<# } #>

	<# if ( '' !== data.description ) { #>
		<br />
		<small>
			{{ data.description }}
		</small>
	<# } #>
</th>

<td class="column-right">
	<# if ( 'credit' === data.type ) { #>&ndash;<# } #>{{ data.totalCurrency }}
</td>

<input type="hidden" value="{{ data.objectType }}" name="adjustments[{{ data.type }}][{{ data.id }}][object_type]" />
<input type="hidden" value="{{ data.type }}" name="adjustments[{{ data.type }}][{{ data.id }}][type]" />
<input type="hidden" value="{{ data.amount }}" name="adjustments[{{ data.type }}][{{ data.id }}][amount]" />
<input type="hidden" value="{{ data.description }}" name="adjustments[{{ data.type }}][{{ data.id }}][description]" />
