<?php
/**
 * Cart Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Cart Template
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Checkout Cart
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edd_checkout_cart() {
	do_action( 'edd_before_checkout_cart' );
	echo '<!--dynamic-cached-content-->';
	edd_get_template_part( 'checkout_cart' );
	echo '<!--/dynamic-cached-content-->';
	do_action( 'edd_after_checkout_cart' );
}

/**
 * Shopping Cart
 *
 * @access      public
 * @since       1.0
 * @return      string
*/
function edd_shopping_cart( $echo = false ) {
	global $edd_options;

	ob_start(); ?>
	<?php do_action('edd_before_cart'); ?>
	<ul class="edd-cart">
	<!--dynamic-cached-content-->
	<?php
		$cart_items = edd_get_cart_contents();
		if($cart_items) :
			foreach( $cart_items as $key => $item ) :
				echo edd_get_cart_item_template( $key, $item, false );
			endforeach;
			echo '<li class="cart_item edd_checkout"><a href="' . edd_get_checkout_uri() . '">' . __('Checkout', 'edd') . '</a></li>';
		else :
			echo '<li class="cart_item empty">' . edd_empty_cart_message() . '</li>';
			echo '<li class="cart_item edd_checkout" style="display:none;"><a href="' . edd_get_checkout_uri() . '">' . __('Checkout', 'edd') . '</a></li>';
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
 * @access      public
 * @since       1.0
 * @return      string
*/
function edd_get_cart_item_template( $cart_key, $item, $ajax = false ) {
	global $post;

	$id = is_array( $item ) ? $item['id'] : $item;

	$remove_url = edd_remove_item_url( $cart_key, $post, $ajax );
	$title      = get_the_title( $id );
	$options    = !empty( $item['options'] ) ? $item['options'] : array();
	$price      = edd_get_cart_item_price( $id, $options );

	if ( edd_use_taxes() && edd_taxes_on_prices() ) {
		$price += edd_calculate_tax( $price );
	}

	if ( ! empty( $options ) ) {
		$title .= ' <span class="edd-cart-item-separator">-</span> ' . edd_get_price_name( $id, $item['options'] );
	}

	ob_start();

	edd_get_template_part( 'widget', 'cart-item' );

	$item = ob_get_clean();

	$item = str_replace( '{item_title}', $title, $item );
	$item = str_replace( '{item_amount}', edd_currency_filter( edd_format_amount( $price ) ), $item );
	$item = str_replace( '{cart_item_id}', absint( $cart_key ), $item );
	$item = str_replace( '{item_id}', absint( $id ), $item );
	$item = str_replace( '{remove_url}', $remove_url, $item );

	return apply_filters( 'edd_cart_item', $item, $id );
}

/**
 * Empty Cart Message
 *
 * Gets the message for an empty cart.
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function edd_empty_cart_message() {
	return apply_filters( 'edd_empty_cart_message', __('Your cart is empty.', 'edd') );
}

/**
 * Empty Checkout Cart
 *
 * @access      private
 * @since       1.0
 * @return      string
 */
function edd_empty_checkout_cart() {
	echo edd_empty_cart_message();
}
add_action( 'edd_empty_cart', 'edd_empty_checkout_cart' );