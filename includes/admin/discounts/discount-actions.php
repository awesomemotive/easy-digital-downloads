<?php
/**
 * Discount Actions
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up and stores a new discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_store_discount()
 * @return void
 */
function edd_add_discount( $data ) {

	if ( ! isset( $data['edd-discount-nonce'] ) || ! wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( edd_get_discount_by_code( $data['code'] ) ) {
		wp_redirect( add_query_arg( 'edd-message', 'discount_exists', $data['edd-redirect'] ) ); edd_die();
	}

	if ( empty( $data['name'] ) || empty( $data['code'] ) || empty( $data['type'] ) || empty( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'edd-message', 'discount_validation_failed' ) );
		edd_die();
	}

	// Verify only accepted characters
	$sanitized = preg_replace('/[^a-zA-Z0-9-_]+/', '', $data['code'] );
	if ( strtoupper( $data['code'] ) !== strtoupper( $sanitized ) ) {
		wp_redirect( add_query_arg( 'edd-message', 'discount_invalid_code' ) );
		edd_die();
	}

	$discount = new EDD_Discount;
	$to_add   = array();
	$to_add['status'] = 'active'; // Default status of active

	foreach( $discount->db->get_columns() as $column => $value ) {

		// Each column gets passed through a generic sanitization method during the update() call

		if( isset( $data[ $column ] ) ) {

			switch( $column ) {

				case 'start_date':
				case 'end_date'  :
					$to_add[ $column ] = date( 'Y-n-d 23:59:59', strtotime( sanitize_text_field( $data[ $column ] ), current_time( 'timestamp' ) ) );
					break;

				case 'product_reqs' :
				case 'excluded_products' :
					$to_add[ $column ] = json_encode( $data[ $column ] );
					break;

				default :
					$to_add[ $column ] = sanitize_text_field( $data[ $column ] );
					break;

			}

		}
	}

	$created = $discount->add( $to_add );

	if ( $created ) {

		wp_redirect( add_query_arg( 'edd-message', 'discount_added', $data['edd-redirect'] ) ); edd_die();

	} else {

		wp_redirect( add_query_arg( 'edd-message', 'discount_add_failed', $data['edd-redirect'] ) ); edd_die();

	}


}
add_action( 'edd_add_discount', 'edd_add_discount' );

/**
 * Saves an edited discount
 *
 * @since 1.0
 * @param array $data Discount code data
 * @return void
 */
function edd_edit_discount( $data ) {

	if ( ! isset( $data['edd-discount-nonce'] ) || ! wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( empty( $data['discount-id'] ) ) {
		wp_die( __( 'No discount ID supplied', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount = new EDD_Discount( absint( $data['discount-id'] ) );

	if( ! $discount || ! $discount->ID > 0 ) {
		wp_die( __( 'Invalid discount', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$to_update  = array();

	foreach( $discount->db->get_columns() as $column => $value ) {

		// Each column gets passed through a generic sanitization method during the update() call

		if( isset( $data[ $column ] ) ) {

			switch( $column ) {

				case 'start_date':
				case 'end_date'  :
					$to_update[ $column ] = date( 'Y-n-d 23:59:59', strtotime( sanitize_text_field( $data[ $column ] ), current_time( 'timestamp' ) ) );
					break;

				case 'product_reqs' :
				case 'excluded_products' :
					$to_update[ $column ] = json_encode( $data[ $column ] );
					break;

				default :
					$to_update[ $column ] = sanitize_text_field( $data[ $column ] );
					break;

			}

		}
	}
//echo '</pre>'; print_r( $)
	$updated = $discount->update( $to_update );

	if ( $updated ) {

		wp_redirect( add_query_arg( 'edd-message', 'discount_updated', $data['edd-redirect'] ) ); edd_die();

	} else {

		wp_redirect( add_query_arg( 'edd-message', 'discount_update_failed', $data['edd-redirect'] ) ); edd_die();

	}

}
add_action( 'edd_edit_discount', 'edd_edit_discount' );

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_remove_discount()
 * @return void
 */
function edd_delete_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = $data['discount'];
	edd_remove_discount( $discount_id );
}
add_action( 'edd_delete_discount', 'edd_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount code's status to active
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_update_discount_status()
 * @return void
 */
function edd_activate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	edd_update_discount_status( $id, 'active' );
}
add_action( 'edd_activate_discount', 'edd_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount code's status to deactivate
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_update_discount_status()
 * @return void
*/
function edd_deactivate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	edd_update_discount_status( $id, 'inactive' );
}
add_action( 'edd_deactivate_discount', 'edd_deactivate_discount' );
