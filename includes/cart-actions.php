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

function edd_process_remove_fromt_cart( $data ) {
	$cart_key = $_GET['cart_item'];
	$cart = edd_remove_from_cart( $cart_key );
}
add_action( 'edd_remove', 'edd_process_remove_fromt_cart' );


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