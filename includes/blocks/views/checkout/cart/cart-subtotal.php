<div class="edd-blocks-cart__row edd-blocks-cart__row-footer edd_cart_footer_row edd_cart_subtotal_row"
	<?php
	if ( ! edd_is_cart_taxed() ) {
		echo ' style="display:none;"';
	}
	?>
>
	<?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
	<div class="edd_cart_subtotal">
		<?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_subtotal_amount"><?php echo edd_cart_subtotal(); // Escaped ?></span>
	</div>
	<?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
</div>
