<?php
/**
 * Discount Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discount Actions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Discount
 *
 * Setups and stores a new discount code.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function edd_add_discount( $data ) {
	if ( wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		// Setup the discount code details
		$posted = array();

		foreach ( $data as $key => $value ) {
			if ( $key != 'edd-discount-nonce' && $key != 'edd-action' ) {
				if ( is_string( $value ) || is_int( $value ) )
					$posted[ $key ] = strip_tags( addslashes( $value ) );
				elseif ( is_array( $value ) )
					$posted[ $key ] = array_map( 'absint', $value );
			}
		}

		// Set the discount code's default status to active
		$posted['status'] = 'active';
		$save = edd_store_discount( $posted );
	}
}
add_action( 'edd_add_discount', 'edd_add_discount' );

/**
 * Edit Discount
 *
 * Saves an edited discount.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function edd_edit_discount( $data ) {
	if ( isset( $data['edd-discount-nonce'] ) && wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		// Setup the discount code details
		$discount = array();

		foreach ( $data as $key => $value ) {
			if ( $key != 'edd-discount-nonce' && $key != 'edd-action' && $key != 'discount-id' && $key != 'edd-redirect' ) {
				if ( is_string( $value ) || is_int( $value ) )
					$discount[ $key ] = strip_tags( addslashes( $value ) );
				elseif ( is_array( $value ) )
					$discount[ $key ] = array_map( 'absint', $value );
			}
		}

		$old_discount = edd_get_discount_by_code( $data['code'] );
		$discount['uses'] = edd_get_discount_uses( $old_discount->ID );

		if ( edd_store_discount( $discount, $data['discount-id'] ) ) {
			wp_redirect( add_query_arg( 'edd-message', 'discount_updated', $data['edd-redirect'] ) ); exit;
		} else {
			wp_redirect( add_query_arg( 'edd-message', 'discount_update_failed', $data['edd-redirect'] ) ); exit;
		}
	}
}
add_action( 'edd_edit_discount', 'edd_edit_discount' );

/**
 * Delete Discount
 *
 * Listens for when a discount delete button is clicked.
 *
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_delete_discount( $data ) {
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) )
		wp_die( __( 'Trying to cheat or something?', 'edd' ), __( 'Error', 'edd' ) );

	$discount_id = $data['discount'];
	edd_remove_discount( $discount_id );
}
add_action( 'edd_delete_discount', 'edd_delete_discount' );

/**
 * Activate Discount
 *
 * Sets a discount code to active.
 *
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_activate_discount( $data ) {
	$id = $data['discount'];
	edd_update_discount_status( $id, 'active' );
}
add_action( 'edd_activate_discount', 'edd_activate_discount' );

/**
 * Deactivate Discount
 *
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_deactivate_discount( $data) {
	$id = $data['discount'];
	edd_update_discount_status( $id, 'inactive' );
}
add_action( 'edd_deactivate_discount', 'edd_deactivate_discount' );