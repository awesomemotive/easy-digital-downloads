<?php
$classes = array( 'edd-blocks-cart__row', 'edd-blocks-cart__row-footer', 'edd-blocks-cart__row-buttons', 'edd_cart_footer_row' );
if ( edd_is_cart_saving_disabled() ) {
	$classes[] = 'edd-no-js';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php do_action( 'edd_cart_footer_buttons' ); ?>
</div>
