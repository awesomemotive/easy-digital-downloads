<?php
/**
 * Email Actions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Triggers Purchase Receipt to be sent after the payment status is updated
 *
 * @since 1.0.8.4
 * @param int $payment_id Payment ID
 * @return void
 */
function edd_trigger_purchase_receipt( $payment_id ) {
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['edd-action'] ) && 'edit_payment' == $_POST['edd-action'] )
		return;

	// Send email with secure download link
	edd_email_purchase_receipt( $payment_id );
}
add_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 1 );

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 * @param array $data Payment Data
 * @return void
 */
function edd_resend_purchase_receipt( $data ) {
	$purchase_id = $data['purchase_id'];
	edd_email_purchase_receipt( $purchase_id, false );

	// Grab all downloads of the purchase and update their file download limits, if needed
	// This allows admins to resend purchase receipts to grant additional file downloads
	$downloads = edd_get_payment_meta_downloads( $purchase_id );

	if ( is_array( $downloads ) ) {
		foreach ( $downloads as $download ) {
			$limit = edd_get_file_download_limit( $download['id'] );
			if ( ! empty( $limit ) ) {
				edd_set_file_download_limit_override( $download['id'], $purchase_id );
			}
		}
	}

	wp_redirect( add_query_arg( array( 'edd-message' => 'email_sent', 'edd-action' => false, 'purchase_id' => false ) ) );
	exit;
}
add_action( 'edd_email_links', 'edd_resend_purchase_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.5
 * @param array $data Paramaters sent from Settings page
 * @return void
 */
function edd_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-test-email' ) )
		return;

	// Send a test email
	edd_email_test_purchase_receipt();
}
add_action( 'edd_send_test_email', 'edd_send_test_email' );
