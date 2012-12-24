<?php
/**
 * Cart Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Cart Actions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Register our endpoints
 *
 * These end points are used for adding / removing items from the cart
 *
 * @access      private
 * @since       1.3.4
 * @return      void
*/

function edd_add_rewrite_endpoints( $rewrite_rules ) {

	add_rewrite_endpoint( 'edd-add', EP_ALL );
	add_rewrite_endpoint( 'edd-remove', EP_ALL );

}
add_action( 'init', 'edd_add_rewrite_endpoints' );


/**
 * Process cart endpoints
 *
 * Listens for add / remove requests
 *
 * @access      private
 * @since       1.3.4
 * @return      void
*/

function edd_process_cart_endpoints() {

	global $wp_query;

	// adds an item to the cart with a /edd-add/# URL
	if( isset( $wp_query->query_vars['edd-add'] ) ) {

		$download_id = absint( $wp_query->query_vars['edd-add'] );
		$cart        = edd_add_to_cart( $download_id, array() );

		wp_redirect( edd_get_checkout_uri() ); exit;
	}

	// removes an item from the cart with a /edd-remove/# URL
	if( isset( $wp_query->query_vars['edd-remove'] ) ) {

		$cart_key = absint( $wp_query->query_vars['edd-remove'] );
		$cart     = edd_remove_from_cart( $cart_key );

		wp_redirect( edd_get_checkout_uri() ); exit;
	}

}
add_action( 'template_redirect', 'edd_process_cart_endpoints', 100 );


/**
 * Process Add To Cart
 *
 * Process the add to cart request.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_process_add_to_cart( $data ) {
	$download_id = $data['download_id'];
	$options = isset( $data['edd_options'] ) ? $data['edd_options'] : array();
	$cart = edd_add_to_cart( $download_id, $options );
}
add_action( 'edd_add_to_cart', 'edd_process_add_to_cart' );


/**
 * Remove From Cart
 *
 * Process the remove form cart request.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_process_remove_from_cart( $data ) {
	$cart_key = $_GET['cart_item'];
	$cart = edd_remove_from_cart( $cart_key );
}
add_action( 'edd_remove', 'edd_process_remove_from_cart' );


/**
 * Process Collection Purchase
 *
 * Process the collection purchase request.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_process_collection_purchase( $data ) {
	$taxonomy = urldecode( $data['taxonomy'] );
	$terms = urldecode( $data['terms'] );
	$cart_items = edd_add_collection_to_cart( $taxonomy, $terms );
	wp_redirect( add_query_arg( 'added', '1', remove_query_arg( array( 'edd_action', 'taxonomy', 'terms' ) ) ) );
	exit;
}
add_action( 'edd_purchase_collection', 'edd_process_collection_purchase' );