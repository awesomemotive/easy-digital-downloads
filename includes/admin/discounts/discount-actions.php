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
				break;

			case 'product_reqs':
				$to_add[ $column ] = $value;
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

		// The start date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$date                 = edd_get_utc_equivalent_date( EDD()->utils->date( $start_date . ' ' . $start_date_hour . ':' . $start_date_minute . ':00', edd_get_timezone_id(), false ) );
		$to_add['start_date'] = $date->format( 'Y-m-d H:i:s' );
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

		// The end date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$date               = edd_get_utc_equivalent_date( EDD()->utils->date( $end_date . ' ' . $end_date_hour . ':' . $end_date_minute . ':00', edd_get_timezone_id(), false ) );
		$to_add['end_date'] = $date->format( 'Y-m-d H:i:s' );
	}

	// Meta values.
	$to_add['product_reqs']      = isset( $data['product_reqs'] ) ? array_unique( array_map( 'esc_attr', $data['product_reqs'] ) ) : '';
	$to_add['excluded_products'] = isset( $data['excluded_products'] ) ? array_unique( array_map( 'esc_attr', $data['excluded_products'] ) ) : '';

	$to_add = array_filter( $to_add );

	// Strip out data that should not be sent to the query methods.
	$to_strip = array(
		'discount-id',
		'edd-redirect',
		'edd-action',
		'edd-discount-nonce',
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
	edd_redirect( add_query_arg( 'edd-message', $arg, $data['edd-redirect'] ) );
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
				break;

			case 'discount-id':
				$to_update['id'] = $value;
				break;

			default :
				$to_update[ $column ] = sanitize_text_field( $value );
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

		// The start date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$date                 = edd_get_utc_equivalent_date( EDD()->utils->date( $start_date . ' ' . $start_date_hour . ':' . $start_date_minute . ':00', edd_get_timezone_id(), false ) );
		$to_update['start_date'] = $date->format( 'Y-m-d H:i:s' );
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

		// The end date is entered in the user's WP timezone. We need to convert it to UTC prior to saving now.
		$date               = edd_get_utc_equivalent_date( EDD()->utils->date( $end_date . ' ' . $end_date_hour . ':' . $end_date_minute . ':00', edd_get_timezone_id(), false ) );
		$to_update['end_date'] = $date->format( 'Y-m-d H:i:s' );
	} else {
		$to_update['end_date'] = null;
	}

	// Known & accepted core discount meta
	$to_update['product_reqs']      = isset( $data['product_reqs'] ) ? array_unique( array_map( 'esc_attr', $data['product_reqs'] ) ) : '';
	$to_update['excluded_products'] = isset( $data['excluded_products'] ) ? array_unique( array_map( 'esc_attr', $data['excluded_products'] ) ) : '';

	// "Once per customer" checkbox.
	$to_update['once_per_customer'] = isset( $data['once_per_customer'] )
		? 1
		: 0;

	// Strip out known non-columns
	$to_strip = array(

		// Legacy
		'discount-id',

		// Redirect
		'edd-redirect',
		'edd-action',
		'edd-discount-nonce',
		'_wp_http_referer',

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
	edd_redirect( add_query_arg( 'edd-message', $arg, $data['edd-redirect'] ) );
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
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
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
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', $arg, $_SERVER['REQUEST_URI'] ) ) );
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
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
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
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', $arg, $_SERVER['REQUEST_URI'] ) ) );
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
		wp_die( __( 'Trying to cheat or something?', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
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
	edd_redirect( remove_query_arg( 'edd-action', add_query_arg( 'edd-message', $arg, $_SERVER['REQUEST_URI'] ) ) );
}
add_action( 'edd_deactivate_discount', 'edd_deactivate_discount' );

/** Notes *********************************************************************/

/**
 * Add a discount note via AJAX.
 *
 * @since 3.0
 */
function edd_ajax_add_discount_note() {

	// Check AJAX referrer
	check_ajax_referer( 'edd_discount_note', 'nonce' );

	// Get discount ID
	$discount_id = ! empty( $_POST['discount_id'] )
		? absint( $_POST['discount_id'] )
		: 0;

	// Get note contents (maybe sanitize)
	$note = ! empty( $_POST['note'] )
		? trim( wp_kses( stripslashes_deep( $_POST['note'] ), edd_get_allowed_tags() ) )
		: '';

	// Bail if no discount
	if ( empty( $discount_id ) ) {
		wp_die( -1 );
	}

	// Bail if no note
	if ( empty( $note ) ) {
		wp_die( -1 );
	}

	// Bail if user not capable
	if ( ! current_user_can( 'manage_shop_discounts', $discount_id ) ) {
		wp_die( __( 'You do not have permission to edit this discount.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Does discount exist?
	$discount = edd_get_discount( $discount_id );

	// Bail if discount is not found
	if ( empty( $discount ) ) {
		wp_die( -1 );
	}

	// Add the note
	$note_id = edd_add_note( array(
		'object_id'   => $discount_id,
		'object_type' => 'discount',
		'content'     => $note,
		'user_id'     => get_current_user_id()
	) );

	$x = new WP_Ajax_Response();
	$x->add(
		array(
			'what' => 'edd_discount_note_html',
			'data' => edd_get_discount_note_html( $note_id, $discount_id ),
		)
	);
	$x->send();
}
add_action( 'wp_ajax_edd_add_discount_note', 'edd_ajax_add_discount_note' );

/**
 * Delete a discount note.
 *
 * @since 3.0
 *
 * @param array $data Data from $_GET.
 */
function edd_delete_discount_note( $data ) {

	// Bail if missing any data
	if ( empty( $data['_wpnonce'] ) || empty( $data['note_id'] ) || empty( $data['discount_id'] ) ) {
		return;
	}

	// Bail if nonce fails
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_delete_discount_note_' . $data['note_id'] ) ) {
		return;
	}

	// Bail if not capable
	if ( ! current_user_can( 'manage_shop_discounts', $data['discount_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this discount.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Does discount exist?
	$discount = edd_get_discount( $data['discount_id'] );

	// Bail if discount is not found
	if ( empty( $discount ) ) {
		wp_die( -1 );
	}

	// Delete the note
	edd_delete_note( $data['note_id'] );

	// Redirect on delete
	edd_redirect( edd_get_admin_url( array(
		'page'        => 'edd-discounts',
		'edd-action'  => 'edit_discount',
		'edd-message' => 'discount-note-deleted',
		'discount'    => absint( $data['discount_id'] )
	) ) );
}
add_action( 'edd_delete_discount_note', 'edd_delete_discount_note' );

/**
 * Delete a discount note via AJAX.
 *
 * @since 3.0
 */
function edd_ajax_delete_discount_note() {

	// Check AJAX referrer
	check_ajax_referer( 'edd_discount_note', 'nonce' );

	// Get discount ID
	$discount_id = ! empty( $_POST['discount_id'] )
		? absint( $_POST['discount_id'] )
		: 0;

	// Get note ID
	$note_id     = ! empty( $_POST['note_id'] )
		? absint( $_POST['note_id'] )
		: 0;

	// Bail if no discount
	if ( empty( $discount_id ) ) {
		wp_die( -1 );
	}

	// Bail if no note
	if ( empty( $note_id ) ) {
		wp_die( -1 );
	}

	// Bail if user not capable
	if ( ! current_user_can( 'manage_shop_discounts', $discount_id ) ) {
		wp_die( __( 'You do not have permission to edit this discount.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Does discount exist?
	$discount = edd_get_discount( $discount_id );

	// Bail if discount is not found
	if ( empty( $discount ) ) {
		wp_die( -1 );
	}

	// Delete note
	if ( edd_delete_note( $note_id ) ) {
		wp_die( 1 );
	}

	wp_die( 0 );
}
add_action( 'wp_ajax_edd_delete_discount_note', 'edd_ajax_delete_discount_note' );
