<?php
if ( ! empty( $block_attributes['link'] ) && ! edd_is_checkout() ) {
	?>
	<a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>">
	<?php
}
require EDD_BLOCKS_DIR . 'assets/images/cart.svg';
$output = array();
if ( ! empty( $block_attributes['show_quantity'] ) ) {
	$output[] = '<span class="edd-blocks-cart__mini-quantity">' . \EDD\Blocks\Checkout\Functions\get_quantity_string() . '</span>';
}
if ( ! empty( $block_attributes['show_total'] ) ) {
	$output[] = '<span class="edd-blocks-cart__mini-total">' . edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ) . '</span>';
}
if ( ! empty( $output ) ) {
	echo wp_kses_post( implode( ' - ', $output ) );
}
if ( ! empty( $block_attributes['link'] ) && ! edd_is_checkout() ) {
	?>
	</a>
	<?php
}
