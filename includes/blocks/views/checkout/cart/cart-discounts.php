<div class="edd-blocks-cart__row edd-blocks-cart__row-footer edd_cart_footer_row edd_cart_discount_row"
	<?php
	if ( ! edd_cart_has_discounts() ) {
		echo ' style="display:none;"';}
	?>
>
	<?php do_action( 'edd_checkout_table_discount_first' ); ?>
	<div class="edd_cart_discount">
		<?php edd_cart_discounts_html(); ?>
	</div>
	<?php do_action( 'edd_checkout_table_discount_last' ); ?>
	</div>
