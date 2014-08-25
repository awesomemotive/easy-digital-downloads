<?php
/**
 * Cart Actions
 *
 * @package     EDD
 * @subpackage  Cart
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Endpoints for the Cart
 *
 * These endpoints are used for adding/removing items from the cart
 *
 * @since 1.3.4
 * @return void
 */
function edd_add_rewrite_endpoints( $rewrite_rules ) {
	add_rewrite_endpoint( 'edd-add', EP_ALL );
	add_rewrite_endpoint( 'edd-remove', EP_ALL );
}
add_action( 'init', 'edd_add_rewrite_endpoints' );

/**
 * Process Cart Endpoints
 *
 * Listens for add/remove requests sent from the cart
 *
 * @since 1.3.4
 * @global $wp_query Used to access the current query that is being requested
 * @return void
*/
function edd_process_cart_endpoints() {
	global $wp_query;

	// Adds an item to the cart with a /edd-add/# URL
	if ( isset( $wp_query->query_vars['edd-add'] ) ) {
		$download_id = absint( $wp_query->query_vars['edd-add'] );
		$cart        = edd_add_to_cart( $download_id, array() );

		wp_redirect( edd_get_checkout_uri() ); edd_die();
	}

	// Removes an item from the cart with a /edd-remove/# URL
	if ( isset( $wp_query->query_vars['edd-remove'] ) ) {
		$cart_key = absint( $wp_query->query_vars['edd-remove'] );
		$cart     = edd_remove_from_cart( $cart_key );

		wp_redirect( edd_get_checkout_uri() ); edd_die();
	}
}
add_action( 'template_redirect', 'edd_process_cart_endpoints', 100 );

/**
 * Process the Add to Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function edd_process_add_to_cart( $data ) {
	$download_id = absint( $data['download_id'] );
	$options     = isset( $data['edd_options'] ) ? $data['edd_options'] : array();
	$cart        = edd_add_to_cart( $download_id, $options );

	if ( edd_straight_to_checkout() && ! edd_is_checkout() ) {
		wp_redirect( edd_get_checkout_uri(), 303 );
		edd_die();
	} else {
		wp_redirect( remove_query_arg( array( 'edd_action', 'download_id', 'edd_options' ) ) ); edd_die();
	}
}
add_action( 'edd_add_to_cart', 'edd_process_add_to_cart' );

/**
 * Process the Remove from Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function edd_process_remove_from_cart( $data ) {
	$cart_key = absint( $_GET['cart_item'] );
	edd_remove_from_cart( $cart_key );
	wp_redirect( remove_query_arg( array( 'edd_action', 'cart_item' ) ) ); edd_die();
}
add_action( 'edd_remove', 'edd_process_remove_from_cart' );

/**
 * Process the Remove fee from Cart request
 *
 * @since 2.0
 *
 * @param $data
 */
function edd_process_remove_fee_from_cart( $data ) {
	$fee = sanitize_text_field( $data['fee'] );
	EDD()->fees->remove_fee( $fee );
	wp_redirect( remove_query_arg( array( 'edd_action', 'fee' ) ) ); edd_die();
}
add_action( 'edd_remove_fee', 'edd_process_remove_fee_from_cart' );

/**
 * Process the Collection Purchase request
 *
 * @since 1.0
 *
 * @param $data
 */
function edd_process_collection_purchase( $data ) {
	$taxonomy   = urldecode( $data['taxonomy'] );
	$terms      = urldecode( $data['terms'] );
	$cart_items = edd_add_collection_to_cart( $taxonomy, $terms );
	wp_redirect( add_query_arg( 'added', '1', remove_query_arg( array( 'edd_action', 'taxonomy', 'terms' ) ) ) );
	edd_die();
}
add_action( 'edd_purchase_collection', 'edd_process_collection_purchase' );


/**
 * Process cart updates, primarily for quantities
 *
 * @since 1.7
 */
function edd_process_cart_update( $data ) {

	foreach( $data['edd-cart-downloads'] as $key => $cart_download_id ) {
		$options  = maybe_unserialize( stripslashes( $data['edd-cart-download-' . $key . '-options'] ) );
		$quantity = absint( $data['edd-cart-download-' . $key . '-quantity'] );
		edd_set_cart_item_quantity( $cart_download_id, $quantity, $options );
	}

}
add_action( 'edd_update_cart', 'edd_process_cart_update' );

/**
 * Process cart save
 *
 * @since 1.8
 * @return void
 */
function edd_process_cart_save( $data ) {

	$cart = edd_save_cart();
	if( ! $cart ) {
		wp_redirect( edd_get_checkout_uri() ); exit;
	}

}
add_action( 'edd_save_cart', 'edd_process_cart_save' );

/**
 * Process cart save
 *
 * @since 1.8
 * @return void
 */
function edd_process_cart_restore( $data ) {

	$cart = edd_restore_cart();
	if( ! is_wp_error( $cart ) ) {
		wp_redirect( edd_get_checkout_uri() ); exit;
	}

}
add_action( 'edd_restore_cart', 'edd_process_cart_restore' );
