<?php
/**
 * Payment Actions
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Complete a purchase
 *
 * Performs all necessary actions to complete a purchase.
 * Triggered by the edd_update_payment_status() function.
 *
 * @since 1.0.8.3
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
*/
function edd_complete_purchase( $payment_id, $new_status, $old_status ) {
	if ( $old_status == 'publish' || $old_status == 'complete' )
		return; // Make sure that payments are only completed once

	// Make sure the payment completion is only processed when new status is complete
	if ( $new_status != 'publish' && $new_status != 'complete' )
		return;

	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$amount       = edd_get_payment_amount( $payment_id );
	$cart_details = edd_get_payment_meta_cart_details( $payment_id );

	if ( is_array( $cart_details ) ) {

		// Increase purchase count and earnings
		foreach ( $cart_details as $download ) {

			// "bundle" or "default"
			$download_type = edd_get_download_type( $download['id'] );

			$price_id      = isset( $download['options']['price_id'] ) ? (int) $download['options']['price_id'] : false;

			// Increase earnings and fire actions once per quantity number
			for( $i = 0; $i < $download['quantity']; $i++ ) {

				if ( ! edd_is_test_mode() || apply_filters( 'edd_log_test_payment_stats', false ) ) {

					edd_record_sale_in_log( $download['id'], $payment_id, $price_id );
					edd_increase_purchase_count( $download['id'] );
					edd_increase_earnings( $download['id'], $download['price'] );

				}

				do_action( 'edd_complete_download_purchase', $download['id'], $payment_id, $download_type, $download );

			}

		}

		// Clear the total earnings cache
		delete_transient( 'edd_earnings_total' );
		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
	}

	// Check for discount codes and increment their use counts
	if ( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) {

		$discounts = array_map( 'trim', explode( ',', $user_info['discount'] ) );

		if( ! empty( $discounts ) ) {

			foreach( $discounts as $code ) {

				edd_increase_discount_usage( $code );

			}

		}
	}

	edd_increase_total_earnings( $amount );

	do_action( 'edd_complete_purchase', $payment_id );

	// Empty the shopping cart
	edd_empty_cart();
}
add_action( 'edd_update_payment_status', 'edd_complete_purchase', 100, 3 );


/**
 * Record payment status change
 *
 * @since 1.4.3
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
 */
function edd_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = edd_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %s to %s', 'edd' ), $old_status, $new_status );

	edd_insert_payment_note( $payment_id, $status_change );
}
add_action( 'edd_update_payment_status', 'edd_record_status_change', 100, 3 );

/**
 * Update Edited Purchase
 *
 * Updates the purchase data for a payment.
 * Used primarily for adding new downloads to a purchase.
 *
 * @since 1.0
 * @param $data Arguments passed
 * @return void
 */
function edd_update_edited_purchase( $data ) {
	if ( wp_verify_nonce( $data['edd-payment-nonce'], 'edd_payment_nonce' ) ) {
		$payment_id = $_POST['payment-id'];

		$payment_data = edd_get_payment_meta( $payment_id );

		if ( isset( $_POST['edd-purchased-downloads'] ) ) {
			$download_list = array();
			$cart_items    = array();

			foreach ( $_POST['edd-purchased-downloads'] as $key => $download ) {

				$download_list[] = array(
					'id'         => $key,
					'options'    => isset( $download['options']['price_id'] ) ? array( 'price_id' => $download['options']['price_id'] ) : array()
				);

				$cart_items[]    = array(
					'id'          => $key,
					'name'        => get_the_title( $key ),
					'item_number' => array(
						'id'      => $key,
						'options' => isset( $download['options']['price_id'] ) ? array( 'price_id' => $download['options']['price_id'] ) : array(),
					),
					'price'       => 0,
					'quantity'    => 1,
					'tax'         => 0
				);
			}

			$payment_data['downloads']    = serialize( $download_list );
			$payment_data['cart_details'] = serialize( $cart_items );
		}

		$user_info                 = maybe_unserialize( $payment_data['user_info'] );
		$user_info['email']        = strip_tags( $_POST['edd-buyer-email'] );
		$user_info['id']           = strip_tags( intval( $_POST['edd-buyer-user-id'] ) );
		$payment_data['user_info'] = serialize( $user_info );
		$payment_data['email']     = strip_tags( $_POST['edd-buyer-email'] );

		update_post_meta( $payment_id, '_edd_payment_meta', $payment_data );
		update_post_meta( $payment_id, '_edd_payment_user_email', strip_tags( $_POST['edd-buyer-email'] ) );
		update_post_meta( $payment_id, '_edd_payment_user_id', strip_tags( intval( $_POST['edd-buyer-user-id'] ) ) );

		if ( ! empty( $_POST['edd-payment-note'] ) ) {
			$note    = wp_kses( $_POST['edd-payment-note'], array() );
			$note_id = edd_insert_payment_note( $payment_id, $note );
		}

		if ( ! empty( $_POST['edd-payment-amount'] ) ) {
			update_post_meta( $payment_id, '_edd_payment_total', sanitize_text_field( edd_sanitize_amount( $_POST['edd-payment-amount'] ) ) );
		}

		if ( ! empty( $_POST['edd-unlimited-downloads'] ) ) {
			add_post_meta( $payment_id, '_unlimited_file_downloads', '1' );
		} else {
			delete_post_meta( $payment_id, '_unlimited_file_downloads' );
		}

		if ( $_POST['edd-old-status'] != $_POST['edd-payment-status'] ) {
			edd_update_payment_status( $payment_id, $_POST['edd-payment-status'] );
		}

		if ( $_POST['edd-payment-status'] == 'publish' && isset( $_POST['edd-payment-send-email'] ) ) {
			// Send the purchase receipt
			edd_email_purchase_receipt( $payment_id, false );
		}

		do_action( 'edd_update_edited_purchase', $payment_id );
	}
}
add_action( 'edd_edit_payment', 'edd_update_edited_purchase' );

/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.8.2
 * @param $data Arguments passed
 * @return void
 */
function edd_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	if( 'publish' != $old_status )
		return;

	if( 'refunded' != $new_status )
		return;

	$downloads = edd_get_payment_meta_cart_details( $payment_id );
	if( $downloads ) {
		foreach( $downloads as $download ) {
			edd_undo_purchase( $download['id'], $payment_id );
		}
	}

	// Decrease store earnings
	$amount = edd_get_payment_amount( $payment_id );
	edd_decrease_total_earnings( $amount );
	
	// Clear the This Month earnings (this_monththis_month is NOT a typo)
	delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
}
add_action( 'edd_update_payment_status', 'edd_undo_purchase_on_refund', 100, 3 );


/**
 * Trigger a Purchase Deletion
 *
 * @since 1.3.4
 * @param $data Arguments passed
 * @return void
 */
function edd_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {
		$payment_id = absint( $data['purchase_id'] );
		edd_delete_purchase( $payment_id );
		wp_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
		edd_die();
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );

/**
 * Flushes the current user's purchase history transient when a payment status
 * is updated
 *
 * @since 1.2.2
 *
 * @param $payment_id
 * @param $new_status the status of the payment, probably "publish"
 * @param $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function edd_clear_user_history_cache( $payment_id, $new_status, $old_status ) {
	$user_info = edd_get_payment_meta_user_info( $payment_id );

	delete_transient( 'edd_user_' . $user_info['id'] . '_purchases' );
}
add_action( 'edd_update_payment_status', 'edd_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @since 1.2
 * @param array $data Arguments passed
 * @return void
*/
function edd_update_old_payments_with_totals( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_upgrade_payments_nonce' ) )
		return;

	if ( get_option( 'edd_payment_totals_upgraded' ) )
		return;

	$payments = edd_get_payments( array(
		'offset' => 0,
		'number' => -1,
		'mode'   => 'all'
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {
			$meta = edd_get_payment_meta( $payment->ID );
			update_post_meta( $payment->ID, '_edd_payment_total', $meta['amount'] );
		}
	}

	add_option( 'edd_payment_totals_upgraded', 1 );
}
add_action( 'edd_upgrade_payments', 'edd_update_old_payments_with_totals' );

/**
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 * @since 1.6
 * @return void
*/
function edd_mark_abandoned_orders() {
	$args = array(
		'status' => 'pending',
		'number' => -1,
		'fields' => 'ids'
	);

	add_filter( 'posts_where', 'edd_filter_where_older_than_week' );

	$payments = edd_get_payments( $args );

	remove_filter( 'posts_where', 'edd_filter_where_older_than_week' );

	if( $payments ) {
		foreach( $payments as $payment ) {
			edd_update_payment_status( $payment, 'abandoned' );
		}
	}
}
add_action( 'edd_weekly_scheduled_events', 'edd_mark_abandoned_orders' );
