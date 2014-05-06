<?php
/**
 * Email Functions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email the download link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true)
 * @return void
 */
function edd_email_purchase_receipt( $payment_id, $admin_notice = true ) {

	$payment_data = edd_get_payment_meta( $payment_id );

	$from_name    = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
	$from_name    = apply_filters( 'edd_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email   = edd_get_option( 'from_name', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'edd_purchase_from_address', $from_email, $payment_id, $payment_data );

	$subject      = edd_get_option( 'purchase_subject', __( 'Purchase Receipt', 'edd' ) );
	$subject      = apply_filters( 'edd_purchase_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject      = edd_do_email_tags( $subject, $payment_id );

	$attachments  = apply_filters( 'edd_receipt_attachments', array(), $payment_id, $payment_data );
	$message      = edd_get_email_body_content( $payment_id, $payment_data );

	$emails = new EDD_Emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'attachments', $attachments );

	$headers = apply_filters( 'edd_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $email, $subject, $message );

	if ( $admin_notice && ! edd_admin_notices_disabled( $payment_id ) ) {
		do_action( 'edd_admin_sale_notice', $payment_id, $payment_data );
	}
}

/**
 * Email the download link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_email_test_purchase_receipt() {

	$from_name   = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
	$from_name   = apply_filters( 'edd_purchase_from_name', $from_name, 0, array() );

	$from_email  = edd_get_option( 'from_name', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'edd_purchase_from_address', $from_email, 0, array() );

	$subject     = edd_get_option( 'purchase_subject', __( 'Purchase Receipt', 'edd' ) );
	$subject     = apply_filters( 'edd_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject     = edd_do_email_tags( $subject, 0 );

	$attachments = apply_filters( 'edd_receipt_attachments', array(), 0, array() );

	$message     = edd_get_email_body_content( 0, array() );

	$emails = new EDD_Emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'attachments', $attachments );

	$headers = apply_filters( 'edd_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( edd_get_admin_notice_emails(), $subject, $message );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.4.2
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function edd_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$from_name   = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
	$from_name   = apply_filters( 'edd_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email  = edd_get_option( 'from_name', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'edd_purchase_from_address', $from_email, $payment_id, $payment_data );

	$subject     = edd_get_option( 'sale_notification_subject', sprintf( __( 'New download purchase - Order #%1$s', 'edd' ), $payment_id ) );
	$subject     = apply_filters( 'edd_admin_sale_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject     = edd_do_email_tags( $subject, $payment_id );

	$headers     = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers    .= "Reply-To: ". $from_email . "\r\n";
	//$headers  .= "MIME-Version: 1.0\r\n";
	$headers    .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers     = apply_filters( 'edd_admin_sale_notification_headers', $headers, $payment_id, $payment_data );

	$attachments = apply_filters( 'edd_admin_sale_notification_attachments', array(), $payment_id, $payment_data );

	$message     = edd_get_sale_notification_body_content( $payment_id, $payment_data );

	$emails = new EDD_Emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'headers', $headers );
	$emails->__set( 'attachments', $attachments );

	$emails->send( edd_get_admin_notice_emails(), $subject, $message );

}
add_action( 'edd_admin_sale_notice', 'edd_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return mixed
 */
function edd_get_admin_notice_emails() {
	global $edd_options;

	$emails = isset( $edd_options['admin_notice_emails'] ) && strlen( trim( $edd_options['admin_notice_emails'] ) ) > 0 ? $edd_options['admin_notice_emails'] : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'edd_admin_notice_emails', $emails );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id
 * @return mixed
 */
function edd_admin_notices_disabled( $payment_id = 0 ) {
	global $edd_options;
	$retval = isset( $edd_options['disable_admin_notices'] );
	return apply_filters( 'edd_admin_notices_disabled', $retval, $payment_id );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $message
 */
function edd_get_default_sale_notification_email() {
	global $edd_options;

	$default_email_body = __( 'Hello', 'edd' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'edd' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'edd' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= '{download_list}' . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'edd' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'edd' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'edd' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'edd' );

	$message = ( isset( $edd_options['sale_notification'] ) && !empty( $edd_options['sale_notification'] ) ) ? $edd_options['sale_notification'] : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.9
 * @param $user_info
 *
 * @return array $email_names
 */
function edd_get_email_names( $user_info ) {
	$email_names = array();
	$user_info 	= maybe_unserialize( $user_info );

	$email_names[ 'fullname' ] = '';
	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
		$user_data = get_userdata( $user_info['id'] );
		$email_names[ 'name' ]      = $user_info['first_name'];
		$email_names[ 'fullname' ]  = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names[ 'username' ]  = $user_data->user_login;
	} elseif ( isset( $user_info['first_name'] ) ) {
		$email_names[ 'name' ]     = $user_info['first_name'];
		$email_names[ 'fullname' ] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names[ 'username' ] = $user_info['first_name'];
	} else {
		$email_names[ 'name' ]     = $user_info['email'];
		$email_names[ 'username' ] = $user_info['email'];
	}

	return $email_names;
}
