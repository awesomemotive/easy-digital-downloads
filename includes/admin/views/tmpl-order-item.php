<?php
/**
 * Order Overview: Item
 *
 * @package     EDD
 * @subpackage  Admin/Views
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

$view_url = add_query_arg(
	array(
		'action' => 'edit',
	),
	admin_url( 'post.php' )
);
?>

<td class="has-row-actions column-name">
	<# if ( true === data.state.isAdding ) { #>
	<button class="button-link delete">
		<span class="dashicons dashicons-no"></span>
	</button>
	<# } #>

	<div class="edd-order-overview-summary__items-name">
		<a
			href="<?php echo esc_url( $view_url ); ?>&post={{ data.productId }}"
			class="row-title"
		>
			{{{ data.productName }}}
		</a>

		<div class="row-actions">
			<# if ( data.discount > 0 ) { #>
			<span class="text"><strong><?php esc_html_e( 'Discount:', 'easy-digital-downloads' ); ?></strong> {{ data.discountCurrency }}</span>
			<# } #>

			<# if ( false !== data.state.hasTax ) { #>
			<span class="text"><strong><?php esc_html_e( 'Tax:', 'easy-digital-downloads' ); ?></strong> {{ data.taxCurrency }}</span>
			<# } #>

			<# if ( false === data.state.isAdding ) { #>
			<span>
				<button class="button-link copy-download-link">
					<?php echo esc_html( sprintf( __( 'Copy %s Links', 'easy-digital-downloads' ), edd_get_label_singular() ) ); ?>
				</button>
			</span>
			<# } #>
			&nbsp;
		</div>
	</div>
</td>

<td>
	{{ data.amountCurrency }}
</td>

<# if ( true === data.state.hasQuantity ) { #>
<td>
	{{ data.quantity }}
</td>
<# } #>

<td class="column-right">
	{{ data.subtotalCurrency }}
</td>

<input type="hidden" value="{{ data.productId }}" name="downloads[{{ data.id }}][id]" />
<input type="hidden" value="{{ data.priceId }}" name="downloads[{{ data.id }}][price_id]" />
<input type="hidden" value="{{ data.quantity }}" name="downloads[{{ data.id }}][quantity]" />
<input type="hidden" value="{{ data.amount }}" name="downloads[{{ data.id }}][amount]" />
<input type="hidden" value="{{ data.tax }}" name="downloads[{{ data.id }}][tax]" />
<input type="hidden" value="{{ data.subtotal }}" name="downloads[{{ data.id }}][subtotal]" />
<input type="hidden" value="{{ data.total }}" name="downloads[{{ data.id }}][total]" />
