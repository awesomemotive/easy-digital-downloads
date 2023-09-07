<div class="edd-blocks-cart__row edd-blocks-cart__row-footer edd_cart_footer_row">
	<?php
	if ( $is_checkout_block ) {
		include EDD_BLOCKS_DIR . 'views/checkout/discount.php';
	}
	?>
	<div class="edd_cart_total">
		<?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo esc_attr( edd_get_cart_subtotal() ); ?>" data-total="<?php echo esc_attr( edd_get_cart_total() ); ?>"><?php edd_cart_total(); // Escaped ?></span>
	</div>
</div>
