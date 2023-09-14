<?php
/**
 * Ajax functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Ajax;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_cart_script' );
/**
 * Enqueues a small script to update the cart block when an item is added to the cart.
 *
 * @since 2.0
 * @return void
 */
function enqueue_cart_script() {
	wp_register_script( 'edd-blocks-cart', EDD_BLOCKS_URL . 'assets/js/cart.js', array( 'edd-ajax' ), EDD_VERSION, true );
}

add_filter( 'edd_ajax_remove_from_cart_response', __NAMESPACE__ . '\update_cart_response' );
add_filter( 'edd_ajax_add_to_cart_response', __NAMESPACE__ . '\update_cart_response' );
/**
 * Filters the add to cart response to update the block cart.
 *
 * @since 2.0
 * @param array $response
 * @return array
 */
function update_cart_response( $response ) {
	$cart_items = edd_get_cart_contents();
	if ( empty( $cart_items ) ) {
		$response['block_cart'] = '<p class="edd-blocks-form__cart">' . esc_html( __( 'Your cart is empty.', 'easy-digital-downloads' ) ) . '</p>';
	} else {
		$is_cart_widget = true;
		ob_start();
		include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';

		$response['block_cart'] = ob_get_clean();
	}

	$response['quantity_formatted'] = \EDD\Blocks\Checkout\Functions\get_quantity_string();

	return $response;
}
