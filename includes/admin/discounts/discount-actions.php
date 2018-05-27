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

	// Setup the discount code details
	$posted = array();

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

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$posted[ $key ] = $value;

		} else if ( $key != 'edd-discount-nonce' && $key != 'edd-action' && $key != 'edd-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$posted[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$posted[ $key ] = array_map( 'absint', $value );

			}
		}

	}

	// Ensure this discount doesn't already exist
	if ( ! edd_get_discount_by_code( $posted['code'] ) ) {

		// Set the discount code's default status to active
		$posted['status'] = 'active';

		if ( edd_store_discount( $posted ) ) {

			wp_redirect( add_query_arg( 'edd_discount_added', '1', $data['edd-redirect'] ) ); edd_die();

		} else {

			wp_redirect( add_query_arg( 'edd-message', 'discount_add_failed', $data['edd-redirect'] ) ); edd_die();

		}

	} else {

		wp_redirect( add_query_arg( 'edd-message', 'discount_exists', $data['edd-redirect'] ) ); edd_die();

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

	// Setup the discount code details
	$discount = array();

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$discount[ $key ] = $value;

		} else if ( $key != 'edd-discount-nonce' && $key != 'edd-action' && $key != 'discount-id' && $key != 'edd-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$discount[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$discount[ $key ] = array_map( 'absint', $value );

			}

		}

	}

	$old_discount     = new EDD_Discount( (int) $data['discount-id'] );
	$discount['uses'] = edd_get_discount_uses( $old_discount->ID );

	if ( edd_store_discount( $discount, $data['discount-id'] ) ) {

		wp_redirect( add_query_arg( 'edd_discount_updated', '1', $data['edd-redirect'] ) ); edd_die();

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
