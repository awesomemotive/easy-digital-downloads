<div class="edd-blocks-cart__row edd-blocks-cart__row-footer edd_cart_footer_row edd_cart_tax_row"
	<?php
	if ( ! edd_is_cart_taxed() ) {
		echo ' style="display:none;"';}
	?>
>
	<?php do_action( 'edd_checkout_table_tax_first' ); ?>
	<div class="edd_cart_tax">
		<?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo esc_attr( edd_get_cart_tax() ); ?>"><?php edd_cart_tax( true ); // Escaped ?></span>
	</div>
	<?php do_action( 'edd_checkout_table_tax_last' ); ?>
</div>
