<?php
defined( 'ABSPATH' ) OR exit;

global $post;
?>
<table id="edd_checkout_cart" <?php edd_is_ajax_enabled() AND print 'class="ajaxed"'; ?>>
	<thead>
		<tr class="edd_cart_header_row">
			<?php do_action( 'edd_checkout_table_header_first' ); ?>
			<th class="edd_cart_item_name"><?php _e( 'Item Name', 'edd' ); ?></th>
			<th class="edd_cart_item_price"><?php _e( 'Item Price', 'edd' ); ?></th>
			<th class="edd_cart_actions"><?php _e( 'Actions', 'edd' ); ?></th>
			<?php do_action( 'edd_checkout_table_header_last' ); ?>
		</tr>
	</thead>
	<tbody>
	<?php
	if ( edd_get_cart_contents() ) {
		do_action( 'edd_cart_items_before' );
		foreach ( $cart_items as $key => $item ) {
			?>
			<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $item['id'] ); ?>">
				<?php do_action( 'edd_checkout_table_body_first', $item['id'] ); ?>
				<td class="edd_cart_item_name">
					<?php
					if (
						current_theme_supports( 'post-thumbnails' )
						AND has_post_thumbnail( $item['id'] )
					) {
						printf(
							'<div class="edd_cart_item_image">%s</div>',
							get_the_post_thumbnail(
								$item['id'],
								apply_filters( 'edd_checkout_image_size', array( 25, 25 ) )
							)
						);
					}
					$item_title = get_the_title( $item['id'] );
					if ( !empty( $item['options'] ) ) {
						$item_title .= edd_has_variable_prices( $item['id'] )
							? " - ".edd_get_price_name( $item['id'], $item['options'] )
							: edd_get_price_name( $item['id'], $item['options'] )
						;
					}
					printf(
						'<span class="edd_checkout_cart_item_title">%s</span>',
						esc_html( $item_title )
					);
					?>
				</td>
				<td class="edd_cart_item_price">
					<?php echo edd_cart_item_price( $item['id'], $item['options'] ); ?>
				</td>
				<td class="edd_cart_actions">
					<a href="<?php echo esc_url( edd_remove_item_url( $key, $post ) ); ?>">
						<?php _e( 'Remove', 'edd' ); ?>
					</a>
				</td>
				<?php do_action( 'edd_checkout_table_body_last', $item ); ?>
			</tr>
			<?php
		} // endforeach;

		// Show any cart fees, both positive and negative fees
		if ( edd_cart_has_fees() ) {
			foreach( edd_get_cart_fees() as $fee_id => $fee ) {
				?>
				<tr class="edd_cart_fee" id="edd_cart_fee_<?php echo $fee_id; ?>">
					<td class="edd_cart_fee_label">
						<?php esc_html_e( $fee['label'] ); ?>
					</td>
					<td class="edd_cart_fee_amount">
						<?php esc_html_e( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?>
					</td>
					<td></td>
				</tr>
				<?php
			} // endforeach
		} // endif
		do_action( 'edd_cart_items_after' );
	} else {
		?>
		<tr class="edd_cart_item">
			<td colspan="3"  class="edd_cart_item_empty">
				<?php do_action( 'edd_empty_cart' ); ?>
			</td>
		</tr>
		<?php
	} // endif
	?>
	</tbody>
	<tfoot>
	<?php
	if ( edd_use_taxes() ) {
		?>
		<tr class="edd_cart_footer_row edd_cart_subtotal_row"<?php ! edd_is_cart_taxed() AND print ' style="display:none;"'; ?>>
			<?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
			<th colspan="3" class="edd_cart_subtotal">
				<?php
				printf(
					'%s:&nbsp;<span class="edd_cart_subtotal">%s</span>',
					__( 'Subtotal', 'edd' ),
					edd_cart_subtotal()
				);
				?>
			</th>
			<?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
		</tr>
		<?php
		if ( ! edd_prices_show_tax_on_checkout() ) {
			?>
			<tr class="edd_cart_footer_row edd_cart_tax_row"<?php edd_local_taxes_only() && ! edd_local_tax_opted_in() AND print ' style="display:none;"'; ?>>
				<?php do_action( 'edd_checkout_table_tax_first' ); ?>
				<th colspan="3" class="edd_cart_tax">
					<?php
					printf(
						'%s:&nbsp;<span class="edd_cart_tax_amount" data-tax="%s">%s</span>',
						__( 'Tax', 'edd' ),
						edd_get_cart_tax( false ),
						esc_html( edd_cart_tax() )
					);
					?>
				</th>
				<?php do_action( 'edd_checkout_table_tax_last' ); ?>
			</tr>
			<?php
		} // endif
	} // endif
	?>
	<tr class="edd_cart_footer_row edd_cart_discount_row" <?php if( ! edd_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
		<?php do_action( 'edd_checkout_table_discount_first' ); ?>
		<th colspan="3" class="edd_cart_discount">
			<?php edd_cart_discounts_html(); ?>
		</th>
		<?php do_action( 'edd_checkout_table_discount_last' ); ?>
	</tr>
	<tr class="edd_cart_footer_row">
		<?php do_action( 'edd_checkout_table_footer_first' ); ?>
		<th colspan="3" class="edd_cart_total">
			<?php
			printf(
				'%s:&nbsp;<span class="edd_cart_amount" data-subtotal="%s" data-tax="%s">',
				__( 'Total', 'edd' ),
				edd_get_cart_total(),
				edd_get_cart_total()
			);
			edd_cart_total();
			echo '</span>';
			?>
		</th>
		<?php do_action( 'edd_checkout_table_footer_last' ); ?>
	</tr>
	</tfoot>
</table>