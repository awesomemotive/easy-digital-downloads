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
		'edd-action' => 'edit_discount',
	)
);
?>

<td></td>

<td class="column-primary" colspan="{{ data.config.colspan }}">
	<div class="removable">
		<# if ( true === data.state.isAdding ) { #>
		<button class="button-link delete">
			<span class="dashicons dashicons-no"></span>
			<span class="screen-reader-text"><?php printf( __( 'Remove %s', 'easy-digital-downloads' ), '{{{ data.type }}}' ); ?></span>
		</button>
		<# } #>

		<div>
			<# if ( '' !== data.description ) { #>
				{{ data.description }}
			<# } #>

			<# if ( '' !== data.description || false !== data.orderItem ) { #>
				<br />
				<small>
			<# } #>
				<# if ( false !== data.orderItem ) { #>
					<?php esc_html_e( 'Fee', 'easy-digital-downloads' ); ?>: {{ data.orderItem.productName }}
				<# } else if ( 'credit' === data.type ) { #>
					<?php esc_html_e( 'Order Credit', 'easy-digital-downloads' ); ?>
				<# } else { #>
					<?php esc_html_e( 'Order Fee', 'easy-digital-downloads' ); ?>
				<# } #>
			<# if ( '' !== data.description || false !== data.orderItem ) { #>
				</small>
			<# } #>
		</div>
	</div>
</td>

<td class="column-right" data-colname="<?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?>">
	<# if ( 'fee' !== data.type || data.subtotal < 0 ) { #>&ndash;<# } #>{{ data.subtotalCurrency }}
</td>

<input type="hidden" value="{{ data.objectId }}" name="adjustments[{{ data.id }}][object_id]" />
<input type="hidden" value="{{ data.objectType }}" name="adjustments[{{ data.id }}][object_type]" />
<input type="hidden" value="{{ data.type }}" name="adjustments[{{ data.id }}][type]" />
<input type="hidden" value="{{ data.description }}" name="adjustments[{{ data.id }}][description]" />
<input type="hidden" value="{{ data.subtotal }}" name="adjustments[{{ data.id }}][subtotal]" />
<input type="hidden" value="{{ data.tax }}" name="adjustments[{{ data.id }}][tax]" />
<input type="hidden" value="{{ data.total }}" name="adjustments[{{ data.id }}][total]" />
