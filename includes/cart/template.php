<?php
/**
 * Cart Template
 *
 * @package     Easy Digital Downloads
 * @subpackage  Cart Template
 * @copyright   Copyright (c) 2012, Pippin Williamson
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
	edd_get_template_part( 'checkout_cart' );
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
	</ul>
	<?php

	do_action( 'edd_after_cart' );

	if( $echo )
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
	$title = get_the_title( $id );
	$options = !empty( $item['options'] ) ? $item['options'] : array();
	if( !empty( $options ) ) {
		$title .= ' <span class="edd-cart-item-separator">-</span> ' . edd_get_price_name( $id, $item['options'] );
	}
	$remove = '<a href="' . esc_url( $remove_url ) . '" data-cart-item="' . absint( $cart_key ) . '" data-download-id="' . absint( $id ) . '" data-action="edd_remove_from_cart" class="edd-remove-from-cart">' . __('remove', 'edd') . '</a>';
	$item = '<li class="edd-cart-item"><span class="edd-cart-item-title">' . $title . '</span>&nbsp;';
	$item .= '<span class="edd-cart-item-separator">-</span>&nbsp;' . edd_currency_filter( edd_format_amount( edd_get_cart_item_price( $id, $options ) ) ) . '&nbsp;';
	$item .= '<span class="edd-cart-item-separator">-</span> ' . $remove . '</li>';
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