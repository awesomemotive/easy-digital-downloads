<?php
/**
 * General functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Whether the checkout page is using blocks.
 *
 * @since 2.0
 * @deprecated 3.3.8 Use \EDD\Checkout\Validator::has_block() instead.
 * @return bool
 */
function checkout_has_blocks() {
	return \EDD\Checkout\Validator::has_block();
}

/**
 * Gets a string to represent the cart quantity.
 *
 * @since 2.0
 * @return string
 */
function get_quantity_string() {
	$quantity = absint( edd_get_cart_quantity() );

	return sprintf(
		'%1$s %2$s',
		$quantity,
		_n( 'item', 'items', $quantity, 'easy-digital-downloads' )
	);
}

/**
 * Outputs the additional cart data. Table markup is replaced.
 *
 * @since 2.0
 * @param string $action The action being called.
 * @param mixed  $args
 * @return string
 */
function do_cart_action( $action = 'edd_cart_items_after', ...$args ) {
	ob_start();
	do_action( $action, $args );

	$details = ob_get_clean();
	if ( empty( $details ) ) {
		return;
	}
	$details = str_replace( '<tr', '<div', $details );
	$details = str_replace( '</tr', '</div', $details );
	$details = str_replace( '<td', '<div', $details );
	$details = str_replace( '</td', '</div', $details );

	echo wp_kses_post( $details );
}
