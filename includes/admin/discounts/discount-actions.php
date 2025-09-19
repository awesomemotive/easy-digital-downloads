<?php
/**
 * Discount Actions
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Sets up and stores a new discount code.
 *
 * @since 1.0
 * @since 3.0 Added backwards compatibility for pre-3.0 discount data. Added discount start/end time.
 * @since 3.3.9 Updated to use the new discount manager.
 *
 * @param array $data Discount code data.
 */
function edd_admin_add_discount( $data = array() ) {
	$manager = new EDD\Admin\Discounts\Manager();
	$manager->add( $data );
}

/**
 * Saves an edited discount.
 *
 * @since 3.0
 * @since 3.3.9 Updated to use the new discount manager.
 *
 * @param array $data Discount code data.
 * @return void
 */
function edd_admin_edit_discount( $data = array() ) {
	$manager = new EDD\Admin\Discounts\Manager();
	$manager->update( $data );
}

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 3.0
 * @param array $data Discount code data.
 * @uses edd_delete_discount()
 * @return void
 */
function edd_admin_delete_discount( $data = array() ) {

	// Bail if no nonce or nonce fails.
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop.
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if discount does not exist.
	if ( empty( $data['discount'] ) ) {
		wp_die( __( 'No discount ID supplied', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Setup default discount values.
	$discount_id = absint( $data['discount'] );
	$deleted     = edd_delete_discount( $discount_id );
	$arg         = ! empty( $deleted )
		? 'discount_deleted'
		: 'discount_deleted_failed';

	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_delete_discount', 'edd_admin_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount status to active
 *
 * @since 1.0
 * @param array $data Discount code data.
 * @uses edd_update_discount_status()
 * @return void
 */
function edd_activate_discount( $data = array() ) {

	// Bail if no nonce or nonce fails.
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop.
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$activated   = edd_update_discount_status( $discount_id, 'active' );
	$arg         = ! empty( $activated )
		? 'discount_activated'
		: 'discount_activation_failed';

	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_activate_discount', 'edd_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount status to deactivate
 *
 * @since 1.0
 * @param array $data Discount code data.
 * @uses edd_update_discount_status()
 * @return void
 */
function edd_deactivate_discount( $data = array() ) {

	// Bail if no nonce or nonce fails.
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop.
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$activated   = edd_update_discount_status( $discount_id, 'inactive' );
	$arg         = ! empty( $activated )
		? 'discount_deactivated'
		: 'discount_deactivation_failed';

	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_deactivate_discount', 'edd_deactivate_discount' );

/**
 * Archive Discount
 *
 * Sets a discount status to archived
 *
 * @since 3.2.0
 * @param array $data Discount code data.
 *
 * @uses edd_update_discount_status()
 */
function edd_archive_discount( $data = array() ) {
	// Bail if no nonce or nonce fails.
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop.
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$archived    = edd_update_discount_status( $discount_id, 'archived' );
	$arg         = ! empty( $archived )
		? 'discount_archived'
		: 'discount_archived_failed';

	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_archive_discount', 'edd_archive_discount' );
