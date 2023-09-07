<?php
/**
 * Buy Now: AJAX
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Adds a Download to the Cart on the `edds_add_to_cart` AJAX action.
 *
 * @since 2.8.0
 */
function edds_buy_now_ajax_add_to_cart() {
	$data = $_POST;

	if ( empty( $data['download_id'] ) ) {
		return wp_send_json_error( array(
			'message' => __( 'Unable to add item to cart.', 'easy-digital-downloads' ),
		) );
	}

	$download_id = absint( $data['download_id'] );
	if ( false === edds_verify( 'nonce', 'edd-add-to-cart-' . $download_id ) ) {
		return wp_send_json_error( array(
			'message' => __( 'Unable to add item to cart.', 'easy-digital-downloads' ),
		) );
	}

	$args = array(
		'quantity' => absint( $data['quantity'] ),
		'price_id' => null,
	);

	if ( edd_has_variable_prices( $download_id ) ) {
		$args['price_id'] = absint( $data['price_id'] );
	}

	// Add individual item.
	edd_add_to_cart( $download_id, $args );

	return wp_send_json_success( array(
		'checkout' => edds_buy_now_checkout(),
	) );
}
add_action( 'wp_ajax_edds_add_to_cart', 'edds_buy_now_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_edds_add_to_cart', 'edds_buy_now_ajax_add_to_cart' );

/**
 * Empties the cart on the `edds_buy_now_empty_cart` AJAX action.
 *
 * @since 2.8.0
 */
function edds_buy_now_ajax_empty_cart() {
	if ( ! empty( EDD()->cart->contents ) ) {
		EDD()->cart->empty_cart();
	}

	return wp_send_json_success();
}
add_action( 'wp_ajax_edds_empty_cart', 'edds_buy_now_ajax_empty_cart' );
add_action( 'wp_ajax_nopriv_edds_empty_cart', 'edds_buy_now_ajax_empty_cart' );
