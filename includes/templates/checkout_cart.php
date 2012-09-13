<?php global $post; ?>
<table id="edd_checkout_cart" <?php if ( edd_is_ajax_enabled() ) { echo 'class="ajaxed"'; } ?>>
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
		<?php $cart_items = edd_get_cart_contents(); ?>
		<?php if ( $cart_items ) : ?>
			<?php foreach ( $cart_items as $key => $item ) : ?>
				<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $item['id'] ); ?>">
					<?php do_action( 'edd_checkout_table_body_first', $item['id'] ); ?>
					<td class="edd_cart_item_name">
						<?php 
							//print_r( $item );
							if ( current_theme_supports( 'post-thumbnails' ) ) {
								if ( has_post_thumbnail( $item['id'] ) ) { 
									echo '<div class="edd_cart_item_image">';
										echo get_the_post_thumbnail( $item['id'], apply_filters( 'edd_checkout_image_size', array( 25,25 ) ) ); 
									echo '</div>';
								} 
							}
							$item_title = get_the_title( $item['id'] );
							if ( !empty( $item['options'] ) ) {
								$item_title .= ' - ' . edd_get_price_name( $item['id'], $item['options'] );							
							}
							echo '<span class="edd_checkout_cart_item_title">' . esc_html( $item_title ) . '</span>'; 
						?>
					</td>
					<td class="edd_cart_item_price"><?php echo esc_html( edd_currency_filter( edd_get_cart_item_price( $item['id'], $item['options'] ) ) ); ?></td>
					<td class="edd_cart_actions"><a href="<?php echo esc_url( edd_remove_item_url( $key, $post ) ); ?>"><?php _e( 'remove', 'edd' ); ?></a></td>
					<?php do_action( 'edd_checkout_table_body_last', $item ); ?>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr class="edd_cart_item">
				<td colspan="3"  class="edd_cart_item_empty"><?php do_action( 'edd_empty_cart' ); ?></td>
			</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr class="edd_cart_footer_row">
			<?php do_action( 'edd_checkout_table_footer_first' ); ?>
			<th colspan="3" class="edd_cart_total"><?php _e( 'Total', 'edd' ); ?>: <span class="edd_cart_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( edd_get_cart_amount() ) ) ); ?></span></th>
			<?php do_action( 'edd_checkout_table_footer_last' ); ?>
		</tr>
	</tfoot>
</table>