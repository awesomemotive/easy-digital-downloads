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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Sets up and stores a new discount code.
 *
 * @since 1.0
 * @since 3.0 Added backwards compatibility for pre-3.0 discount data. Added discount start/end time.
 *
 * @param array $data Discount code data.
 */
function edd_admin_add_discount( $data = array() ) {

	// Bail if no nonce or nonce fails.
	if ( ! isset( $data['edd-discount-nonce'] ) || ! wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		return;
	}

	// Bail if current user cannot manage shop discounts.
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if discount does not exist.
	if ( edd_get_discount_by( 'code', $data['code'] ) ) {
		edd_redirect( add_query_arg( 'edd-message', 'discount_exists', $data['edd-redirect'] ) );
	}

	// Bail if missing important data.
	if ( empty( $data['name'] ) || empty( $data['code'] ) || empty( $data['amount_type'] ) || ( empty( $data['amount'] ) && 0 !== absint( $data['amount'] ) ) ) {
		edd_redirect( add_query_arg( 'edd-message', 'discount_validation_failed' ) );
	}

	// Verify only accepted characters.
	$sanitized = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $data['code'] );
	if ( strtoupper( $data['code'] ) !== strtoupper( $sanitized ) ) {
		edd_redirect( add_query_arg( 'edd-message', 'discount_invalid_code' ) );
	}

	$sanitized_amount = (float) edd_sanitize_amount( $data['amount'] );
	if ( empty( $data['amount'] ) || 0.00 === $sanitized_amount ) {
		edd_redirect( add_query_arg( 'edd-message', 'discount_invalid_amount' ) );
	}

	// Setup default discount values.
	$to_add            = array();
	$to_add['status']  = 'active';
	$current_timestamp = current_time( 'timestamp' );

	$data = array_filter( $data );

	foreach ( $data as $column => $value ) {
		switch ( $column ) {

			// We skip these here as they are handled below.
			case 'start_date':
			case 'start':
			case 'end_date':
			case 'expiration':
			case 'edd-action':
			case 'edd-discount-nonce':
			case 'edd-redirect':
				break;

			case 'product_reqs':
			case 'categories':
				$to_add[ $column ] = $value;
				break;

			case 'amount':
				$to_add['amount'] = edd_sanitize_amount( $value );
				break;

			default:
				$to_add[ $column ] = is_array( $value )
					? array_map( 'sanitize_text_field', $value )
					: sanitize_text_field( $value );
				break;
		}
	}

	// Start date.
	if ( ! empty( $data['start_date'] ) ) {
		$start_date        = sanitize_text_field( $data['start_date'] );
		$start_date_hour   = isset( $data['start_date_hour'] ) && (int) $data['start_date_hour'] >= 0 && (int) $data['start_date_hour'] <= 23
			? intval( $data['start_date_hour'] )
			: '00';
		$start_date_minute = isset( $data['start_date_minute'] ) && (int) $data['start_date_minute'] >= 0 && (int) $data['start_date_minute'] <= 59
			? intval( $data['start_date_minute'] )
			: '00';

		$start_date_string = EDD()->utils->get_date_string(
			$start_date,
			$start_date_hour,
			$start_date_minute
		);
		// The start date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$to_add['start_date'] = edd_get_utc_date_string( $start_date_string );
	}

	// End date.
	if ( ! empty( $data['end_date'] ) ) {
		$end_date        = sanitize_text_field( $data['end_date'] );
		$end_date_hour   = isset( $data['end_date_hour'] ) && (int) $data['end_date_hour'] >= 0 && (int) $data['end_date_hour'] <= 23
			? intval( $data['end_date_hour'] )
			: '23';
		$end_date_minute = isset( $data['end_date_minute'] ) && (int) $data['end_date_minute'] >= 0 && (int) $data['end_date_minute'] <= 59
			? intval( $data['end_date_minute'] )
			: '59';

		$end_date_string = EDD()->utils->get_date_string(
			$end_date,
			$end_date_hour,
			$end_date_minute
		);
		// The end date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$to_add['end_date'] = edd_get_utc_date_string( $end_date_string );
	}

	// Meta values.
	$to_add['product_reqs']      = isset( $data['product_reqs'] ) ? preg_filter( '/\d|\d_\d/', '$0', (array) $data['product_reqs'] ) : ''; // only accepts patterns like 123 or 123_4
	$to_add['excluded_products'] = isset( $data['excluded_products'] ) ? wp_parse_id_list( $data['excluded_products'] ) : '';
	$to_add['categories']        = isset( $data['categories'] ) ? wp_parse_id_list( $data['categories'] ) : array();
	$to_add['term_condition']    = isset( $data['term_condition'] ) ? $data['term_condition'] : '';

	$to_add = array_filter( $to_add );

	// Strip out data that should not be sent to the query methods.
	$to_strip = array(
		'discount-id',
		'start_date_minute',
		'start_date_hour',
		'end_date_minute',
		'end_date_hour',
	);

	// Loop through fields to update, and unset known bad keys.
	foreach ( $to_add as $key => $value ) {
		if ( in_array( $key, $to_strip, true ) ) {
			unset( $to_add[ $key ] );
		}
	}

	// Attempt to add.
	$created = edd_add_discount( $to_add );
	$arg     = ! empty( $created )
		? 'discount_added'
		: 'discount_add_failed';

	// Redirect.
	edd_redirect( add_query_arg( 'edd-message', sanitize_key( $arg ), $data['edd-redirect'] ) );
}
add_action( 'edd_add_discount', 'edd_admin_add_discount' );

/**
 * Saves an edited discount
 *
 * @since 3.0
 * @param array $data Discount code data
 * @return void
 */
function edd_admin_edit_discount( $data = array() ) {

	// Bail if no nonce or nonce fails
	if ( ! isset( $data['edd-discount-nonce'] ) || ! wp_verify_nonce( $data['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
		return;
	}

	// Bail if current user cannot manage shop discounts
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if discount does not exist
	if ( empty( $data['discount-id'] ) ) {
		wp_die( __( 'No discount ID supplied', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Setup default discount values
	$discount_id = absint( $data['discount-id'] );
	$discount    = edd_get_discount( $discount_id );

	// Bail if no discount
	if ( empty( $discount ) || ( $discount->id <= 0 ) ) {
		wp_die( __( 'Invalid discount', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$sanitized_amount = (float) edd_sanitize_amount( $data['amount'] );
	if ( empty( $data['amount'] ) || 0.00 === $sanitized_amount ) {
		edd_redirect( add_query_arg( 'edd-message', 'discount_invalid_amount' ) );
	}

	// Prepare update
	$to_update    = array();
	$current_time = current_time( 'timestamp' );

	$data = array_filter( $data );

	foreach ( $data as $column => $value ) {
		switch ( $column ) {
			// We skip these here as they are handled below.
			case 'start_date':
			case 'start':
			case 'end_date':
			case 'expiration':
			case 'edd-redirect':
			case 'edd-action':
			case 'edd-discount-nonce':
			case '_wp_http_referer':
				break;

			case 'discount-id':
				$to_update['id'] = $value;
				break;

			case 'amount':
				$to_update['amount'] = edd_sanitize_amount( $value );
				break;

			default:
				$to_update[ $column ] = is_array( $value )
					? array_map( 'sanitize_text_field', $value )
					: sanitize_text_field( $value );
				break;
		}
	}

	// Start date.
	if ( ! empty( $data['start_date'] ) ) {
		$start_date        = sanitize_text_field( $data['start_date'] );
		$start_date_hour   = isset( $data['start_date_hour'] ) && (int) $data['start_date_hour'] >= 0 && (int) $data['start_date_hour'] <= 23
			? intval( $data['start_date_hour'] )
			: '00';
		$start_date_minute = isset( $data['start_date_minute'] ) && (int) $data['start_date_minute'] >= 0 && (int) $data['start_date_minute'] <= 59
			? intval( $data['start_date_minute'] )
			: '00';

		$start_date_string = EDD()->utils->get_date_string(
			$start_date,
			$start_date_hour,
			$start_date_minute
		);

		// The start date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$to_update['start_date'] = edd_get_utc_date_string( $start_date_string );
	} else {
		$to_update['start_date'] = null;
	}

	// End date.
	if ( ! empty( $data['end_date'] ) ) {
		$end_date        = sanitize_text_field( $data['end_date'] );
		$end_date_hour   = isset( $data['end_date_hour'] ) && (int) $data['end_date_hour'] >= 0 && (int) $data['end_date_hour'] <= 23
			? intval( $data['end_date_hour'] )
			: '23';
		$end_date_minute = isset( $data['end_date_minute'] ) && (int) $data['end_date_minute'] >= 0 && (int) $data['end_date_minute'] <= 59
			? intval( $data['end_date_minute'] )
			: '59';

		$end_date_string = EDD()->utils->get_date_string(
			$end_date,
			$end_date_hour,
			$end_date_minute
		);

		// The end date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$to_update['end_date'] = edd_get_utc_date_string( $end_date_string );
	} else {
		$to_update['end_date'] = null;
	}

	// Known & accepted core discount meta.
	$to_update['product_reqs']      = isset( $data['product_reqs'] ) ? preg_filter( '/\d|\d_\d/', '$0', (array) $data['product_reqs'] ) : ''; // only accepts patterns like 123 or 123_4
	$to_update['excluded_products'] = isset( $data['excluded_products'] ) ? wp_parse_id_list( $data['excluded_products'] ) : '';
	$to_update['categories']        = ! empty( $data['categories'] ) ? wp_parse_id_list( $data['categories'] ) : array();
	$to_update['term_condition']    = isset( $data['term_condition'] ) ? $data['term_condition'] : '';

	// "Once per customer" checkbox.
	$to_update['once_per_customer'] = isset( $data['once_per_customer'] )
		? 1
		: 0;

	// Strip out known non-columns
	$to_strip = array(

		// Legacy
		'discount-id',

		// Time
		'start_date_minute',
		'start_date_hour',
		'end_date_minute',
		'end_date_hour'
	);

	// Loop through fields to update, and unset known bad keys
	foreach ( $to_update as $key => $value ) {
		if ( in_array( $key, $to_strip, true ) ) {
			unset( $to_update[ $key ] );
		}
	}

	// Attempt to update
	$updated = edd_update_discount( $discount_id, $to_update );
	$arg     = ! empty( $updated )
		? 'discount_updated'
		: 'discount_not_changed';

	// Redirect
	edd_redirect( add_query_arg( 'edd-message', sanitize_key( $arg ), $data['edd-redirect'] ) );
}
add_action( 'edd_edit_discount', 'edd_admin_edit_discount' );

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 3.0
 * @param array $data Discount code data
 * @uses edd_delete_discount()
 * @return void
 */
function edd_admin_delete_discount( $data = array() ) {

	// Bail if no nonce or nonce fails
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if discount does not exist
	if ( empty( $data['discount'] ) ) {
		wp_die( __( 'No discount ID supplied', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Setup default discount values
	$discount_id = absint( $data['discount'] );
	$deleted     = edd_delete_discount( $discount_id );
	$arg         = ! empty( $deleted )
		? 'discount_deleted'
		: 'discount_deleted_failed';

	// Redirect
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_delete_discount', 'edd_admin_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount status to active
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_update_discount_status()
 * @return void
 */
function edd_activate_discount( $data = array() ) {

	// Bail if no nonce or nonce fails
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop
	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$activated   = edd_update_discount_status( $discount_id, 'active' );
	$arg         = ! empty( $activated )
		? 'discount_activated'
		: 'discount_activation_failed';

	// Redirect
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_activate_discount', 'edd_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount status to deactivate
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses edd_update_discount_status()
 * @return void
 */
function edd_deactivate_discount( $data = array() ) {

	// Bail if no nonce or nonce fails
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$activated   = edd_update_discount_status( $discount_id, 'inactive' );
	$arg         = ! empty( $activated )
		? 'discount_deactivated'
		: 'discount_deactivation_failed';

	// Redirect
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_deactivate_discount', 'edd_deactivate_discount' );

/**
 * Archive Discount
 *
 * Sets a discount status to archived
 *
 * @since 3.2.0
 * @param array $data Discount code data
 *
 * @uses edd_update_discount_status()
 */
function edd_archive_discount( $data = array() ) {
	// Bail if no nonce or nonce fails
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'edd_discount_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Bail if current user cannot manage shop
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$discount_id = absint( $data['discount'] );
	$archived    = edd_update_discount_status( $discount_id, 'archived' );
	$arg         = ! empty( $archived )
		? 'discount_archived'
		: 'discount_archived_failed';

	// Redirect
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', sanitize_key( $arg ), $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_archive_discount', 'edd_archive_discount' );
