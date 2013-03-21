<?php
/**
 * Payment Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Payment Actions
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
 * @param		int $payment_id the ID number of the payment
 * @param		string $new_status the status of the payment, probably "publish"
 * @param		string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @access      private
 * @since       1.0.8.3
 * @return      void
*/
function edd_complete_purchase( $payment_id, $new_status, $old_status ) {
	if ( $old_status == 'publish' || $old_status == 'complete' )
		return; // Make sure that payments are only completed once

	// Make sure the payment completion is only processed when new status is complete
	if ( $new_status != 'publish' && $new_status != 'complete' )
		return;

	if ( edd_is_test_mode() && ! apply_filters( 'edd_log_test_payment_stats', false ) )
		return;

	$payment_data 	= edd_get_payment_meta( $payment_id );
	$downloads 		= maybe_unserialize( $payment_data['downloads'] );
	$user_info 		= maybe_unserialize( $payment_data['user_info'] );
	$cart_details 	= maybe_unserialize( $payment_data['cart_details'] );

	if ( is_array( $downloads ) ) {
		// Increase purchase count and earnings
		foreach ( $downloads as $download ) {
			edd_record_sale_in_log( $download['id'], $payment_id, $user_info );
			edd_increase_purchase_count( $download['id'] );
			$amount = null;

			if ( is_array( $cart_details ) ) {
				foreach ( $cart_details as $key => $item ) {
					if ( array_search( $download['id'], $item ) ) {
						$cart_item_id = $key;
					}
				}

				$amount = isset( $cart_details[$cart_item_id]['price'] ) ? $cart_details[$cart_item_id]['price'] : null;
			}

			$amount = edd_get_download_final_price( $download['id'], $user_info, $amount );
			edd_increase_earnings( $download['id'], $amount );
		}

		// Clear the total earnings cache
		delete_transient( 'edd_earnings_total' );
	}

	if ( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) {
		edd_increase_discount_usage( $user_info['discount'] );
	}

	// Empty the shopping cart
	edd_empty_cart();
}
add_action( 'edd_update_payment_status', 'edd_complete_purchase', 100, 3 );

/**
 * Record payment status change
 *
 *
 * @param		int $payment_id the ID number of the payment
 * @param		string $new_status the status of the payment, probably "publish"
 * @param		string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @access      private
 * @since       1.4.3
 * @return      void
 */
function edd_record_status_change( $payment_id, $new_status, $old_status ) {
	if ( $new_status == 'publish' )
		$new_status = 'complete';

	if ( $old_status == 'publish' )
		$old_status = 'complete';

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
 * @access      private
 * @since       1.0
 * @return      void
 */
function edd_update_edited_purchase( $data ) {
	if ( wp_verify_nonce( $data['edd-payment-nonce'], 'edd_payment_nonce' ) ) {
		$payment_id = $_POST['payment-id'];

		$payment_data = edd_get_payment_meta( $payment_id );

		if ( isset( $_POST['edd-purchased-downloads'] ) ) {
			$download_list = array();

			foreach ( $_POST['edd-purchased-downloads'] as $key => $download ) {
				if ( isset ( $download['options']['price_id'] ) ) {
					$download_list[] = array(
						'id' => $key,
						'options' => array(
							'price_id' => $download['options']['price_id']
						)
					);
				} else {
					$download_list[] = array( 'id' => $download );
				}
			}

			$payment_data['downloads'] = serialize( $download_list );
		}

		$user_info                 = maybe_unserialize( $payment_data['user_info'] );
		$user_info['email']        = strip_tags( $_POST['edd-buyer-email'] );
		$user_info['user_id']      = strip_tags( intval( $_POST['edd-buyer-user-id'] ) );
		$payment_data['user_info'] = serialize( $user_info );

		update_post_meta( $payment_id, '_edd_payment_meta', $payment_data );
		update_post_meta( $payment_id, '_edd_payment_user_email', strip_tags( $_POST['edd-buyer-email'] ) );
		update_post_meta( $payment_id, '_edd_payment_user_id', strip_tags( intval( $_POST['edd-buyer-user-id'] ) ) );

		if ( ! empty( $_POST['edd-payment-note'] ) ) {
			$note    = wp_kses( $_POST['edd-payment-note'], array() );
			$note_id = edd_insert_payment_note( $payment_id, $note );
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
 * Trigger a Purchase deletion
 *
 * @access      private
 * @since       1.3.4
 * @return      void
 */
function edd_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {
		$payment_id = absint( $data['purchase_id'] );
		edd_delete_purchase( $payment_id );
		wp_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
		exit;
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );

/**
 * Flushes the Total Earnings Cache when a payment is created
 *
 * @access      private
 * @since       1.2
 * @return      void
 */
function edd_clear_earnings_cache( $payment, $payment_data ) {
	delete_transient( 'edd_total_earnings' );
}
add_action( 'edd_insert_payment', 'edd_clear_earnings_cache', 10, 2 );

/**
 * Flushes the Current user's purchase history transient
 *
 * @access      private
 * @since       1.2.2
 * @return      void
*/
function edd_clear_user_history_cache( $payment_id, $new_status, $old_status ) {
	$user_info = edd_get_payment_meta_user_info( $payment_id );

	delete_transient( 'edd_user_' . $user_info['id'] . '_purchases' );
	delete_transient( md5( 'edd_customer_total_' . $user_info['email'] ) );
}
add_action( 'edd_update_payment_status', 'edd_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @access      private
 * @since       1.2
 * @return      void
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