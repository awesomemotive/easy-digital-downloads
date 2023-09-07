<div class="edd-blocks-cart__row edd-blocks-cart__row-item edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
	<div class="edd_cart_item_name">
		<div class="edd_checkout_cart_item_title">
		<?php
		if ( has_post_thumbnail( $item['id'] ) ) {
			echo '<div class="edd_cart_item_image">';
				echo get_the_post_thumbnail( $item['id'], apply_filters( 'edd_checkout_image_size', array( 25, 25 ) ) );
			echo '</div>';
		}
		echo esc_html( edd_get_cart_item_name( $item ) );
		echo '</div>';
		/**
		 * Runs after the item in cart's title is echoed
		 * @since 2.6
		 *
		 * @param array $item Cart Item
		 * @param int $key Cart key
		 */
		do_action( 'edd_checkout_cart_item_title_after', $item, $key );
		if ( $is_checkout_block && edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $item['id'] ) ) :
			?>
			<div class="edd_cart_actions">
				<label for="edd-cart-download-<?php echo esc_attr( $key ); ?>-quantity"><?php esc_html_e( 'Quantity:', 'easy-digital-downloads' ); ?></label>
				<input type="number" min="1" step="1" name="edd-cart-download-<?php echo esc_attr( $key ); ?>-quantity" id="edd-cart-download-<?php echo esc_attr( $key ); ?>-quantity" data-key="<?php echo esc_attr( $key ); ?>" class="edd-input edd-item-quantity" value="<?php echo esc_attr( edd_get_cart_item_quantity( $item['id'], $item['options'] ) ); ?>"/>
				<input type="hidden" name="edd-cart-downloads[]" value="<?php echo esc_attr( $item['id'] ); ?>"/>
				<input type="hidden" name="edd-cart-download-<?php echo esc_attr( $key ); ?>-options" value="<?php echo esc_attr( json_encode( $item['options'] ) ); ?>"/>
			</div>
			<?php
		endif;
		?>
	</div>
	<div class="edd_cart_item_price">
		<?php
		echo wp_kses_post( edd_cart_item_price( $item['id'], $item['options'] ) );
		do_action( 'edd_checkout_cart_item_price_after', $item );
		$remove_url   = edd_remove_item_url( $key );
		$button_class = 'edd-remove-from-cart';
		if ( empty( $is_cart_widget ) ) {
			$remove_url   = wp_nonce_url( $remove_url, 'edd-remove-from-cart-' . sanitize_key( $key ), 'edd_remove_from_cart_nonce' );
			$button_class = 'edd_cart_remove_item_btn';
		}
		?>
		<div class="edd_cart_actions">
			<?php do_action( 'edd_cart_actions', $item, $key ); ?>
			<a
				class="edd-blocks-cart__action-remove <?php echo esc_attr( $button_class ); ?>"
				href="<?php echo esc_url( $remove_url ); ?>"
				<?php if ( ! empty( $is_cart_widget ) ) : ?>
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-remove-cart-widget-item' ) ); ?>"
					data-cart-item="<?php echo absint( $key ); ?>"
					data-download-id="<?php echo absint( $item['id'] ); ?>"
					data-action="edd_remove_from_cart"
				<?php endif; ?>
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Remove', 'easy-digital-downloads' ); ?></span>
			</a>
		</div>
	</div>
</div>
