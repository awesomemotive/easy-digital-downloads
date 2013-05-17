<?php
/**
 * Cart Template
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Builds the Cart by providing hooks and calling all the hooks for the Cart
 *
 * @since 1.0
 * @return void
 */
function edd_checkout_cart() {
	do_action( 'edd_before_checkout_cart' );
	echo '<!--dynamic-cached-content-->';
	edd_get_template_part( 'checkout_cart' );
	echo '<!--/dynamic-cached-content-->';
	do_action( 'edd_after_checkout_cart' );
}

/**
 * Renders the Shopping Cart
 *
 * @since 1.0
 * @return string Fully formatted cart
*/
function edd_shopping_cart( $echo = false ) {
	global $edd_options;

	ob_start();
	do_action('edd_before_cart');
	$display = 'style="display:none;"';
  	$cart_quantity = edd_get_cart_quantity();

  	if ( $cart_quantity > 0 ){
  	  $display = "";
  	}

  	echo "<p class='edd-cart-number-of-items' {$display}>" . __( 'Number of items in cart', 'edd' ) . ': <span class="edd-cart-quantity">' . $cart_quantity . '<span></p>';
 	?>

	<ul class="edd-cart">
	<!--dynamic-cached-content-->
	<?php
		$cart_items = edd_get_cart_contents();
		if($cart_items) :
			foreach( $cart_items as $key => $item ) :
				echo edd_get_cart_item_template( $key, $item, false );
			endforeach;
			edd_get_template_part( 'widget', 'cart-checkout' );
		else :
			edd_get_template_part( 'widget', 'cart-empty' );
		endif;
	?>
	<!--/dynamic-cached-content-->
	</ul>
	<?php

	do_action( 'edd_after_cart' );

	if ( $echo )
		echo ob_get_clean();
	else
		return ob_get_clean();
}

/**
 * Get Cart Item Template
 *
 * @since 1.0
 * @param int $cart_key Cart key
 * @param array $item Cart item
 * @param bool $ajax AJAX?
 * @return string Cart item
*/
function edd_get_cart_item_template( $cart_key, $item, $ajax = false ) {
	global $post;

	$id = is_array( $item ) ? $item['id'] : $item;

	$remove_url = edd_remove_item_url( $cart_key, $post, $ajax );
	$title      = get_the_title( $id );
	$options    = !empty( $item['options'] ) ? $item['options'] : array();
	$price      = edd_get_cart_item_price( $id, $options );

	if ( ! empty( $options ) ) {
		$title .= ( edd_has_variable_prices( $item['id'] ) ) ? ' <span class="edd-cart-item-separator">-</span> ' . edd_get_price_name( $id, $item['options'] ) : edd_get_price_name( $id, $item['options'] );
	}

	ob_start();

	edd_get_template_part( 'widget', 'cart-item' );

	$item = ob_get_clean();

	$item = str_replace( '{item_title}', $title, $item );
	$item = str_replace( '{item_amount}', edd_currency_filter( edd_format_amount( $price ) ), $item );
	$item = str_replace( '{cart_item_id}', absint( $cart_key ), $item );
	$item = str_replace( '{item_id}', absint( $id ), $item );
	$item = str_replace( '{remove_url}', $remove_url, $item );
  	$subtotal = '';
  	if ( $ajax ){
   	 $subtotal = edd_currency_filter( edd_get_cart_amount( false ) ) ;
  	}
 	$item = str_replace( '{subtotal}', $subtotal, $item );

	return apply_filters( 'edd_cart_item', $item, $id );
}

/**
 * Returns the Empty Cart Message
 *
 * @since 1.0
 * @return string Cart is empty message
 */
function edd_empty_cart_message() {
	return apply_filters( 'edd_empty_cart_message', '<span class="edd_empty_cart">' . __( 'Your cart is empty.', 'edd' ) . '</span>' );
}

/**
 * Echoes the Empty Cart Message
 *
 * @since 1.0
 * @return void
 */
function edd_empty_checkout_cart() {
	echo edd_empty_cart_message();
}
add_action( 'edd_empty_cart', 'edd_empty_checkout_cart' );
