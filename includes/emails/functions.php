<?php
/**
 * Email Functions
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
	global $edd_options;

	$payment_data = edd_get_payment_meta( $payment_id );
	$user_id      = edd_get_payment_user_id( $payment_id );
	$user_info    = maybe_unserialize( $payment_data['user_info'] );
	$email        = edd_get_payment_user_email( $payment_id );

	if ( isset( $user_id ) && $user_id > 0 ) {
		$user_data = get_userdata($user_id);
		$name = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$message = edd_get_email_body_header();
	$message .= edd_get_email_body_content( $payment_id, $payment_data, $admin_notice );
	$message .= edd_get_email_body_footer();

	$from_name = isset( $edd_options['from_name'] ) ? $edd_options['from_name'] : get_bloginfo('name');
	$from_email = isset( $edd_options['from_email'] ) ? $edd_options['from_email'] : get_option('admin_email');

	$subject = apply_filters( 'edd_purchase_subject', ! empty( $edd_options['purchase_subject'] )
		? wp_strip_all_tags( $edd_options['purchase_subject'], true )
		: __( 'Purchase Receipt', 'edd' ), $payment_id );

	$subject = edd_email_template_tags( $subject, $payment_data, $payment_id );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	//$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers = apply_filters( 'edd_receipt_headers', $headers, $payment_id, $payment_data );

	// Allow add-ons to add file attachments
	$attachments = apply_filters( 'edd_receipt_attachments', array(), $payment_id, $payment_data );

	wp_mail( $email, $subject, $message, $headers, $attachments );

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
	global $edd_options;

	$default_email_body = __( "Dear", "edd" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = isset( $edd_options['purchase_receipt'] ) ? $edd_options['purchase_receipt'] : $default_email_body;

	$message = edd_get_email_body_header();
	$message .= apply_filters( 'edd_purchase_receipt', edd_email_preview_templage_tags( $email ), 0, array() );
	$message .= edd_get_email_body_footer();

	$from_name = isset( $edd_options['from_name'] ) ? $edd_options['from_name'] : get_bloginfo('name');
	$from_email = isset( $edd_options['from_email'] ) ? $edd_options['from_email'] : get_option('admin_email');

	$subject = apply_filters( 'edd_purchase_subject', isset( $edd_options['purchase_subject'] )
		? trim( $edd_options['purchase_subject'] )
		: __( 'Purchase Receipt', 'edd' ), 0 );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers = apply_filters( 'edd_test_purchase_headers', $headers );

	wp_mail( edd_get_admin_notice_emails(), $subject, $message, $headers );
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
	global $edd_options;

	/* Send an email notification to the admin */
	$admin_email = edd_get_admin_notice_emails();
	$user_id      = edd_get_payment_user_id( $payment_id );
	$user_info   = maybe_unserialize( $payment_data['user_info'] );

	if ( isset( $user_id ) && $user_id > 0 ) {
		$user_data = get_userdata($user_id);
		$name = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset($user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $user_info['email'];
	}

	$admin_message = edd_get_email_body_header();
	$admin_message .= edd_get_sale_notification_body_content( $payment_id, $payment_data );
	$admin_message .= edd_get_email_body_footer();

	$admin_subject = apply_filters( 'edd_admin_sale_notification_subject', !empty( $edd_options['sale_notification_subject'] )
		? wp_strip_all_tags( $edd_options['sale_notification_subject'], true )
		: sprintf( __( 'New download purchase - Order #%1$s', 'edd' ), $payment_id ), $payment_id, $payment_data );

	$admin_subject = edd_email_template_tags( $admin_subject, $payment_data, $payment_id, true );

	$admin_headers = "MIME-Version: 1.0\r\n";
	$admin_headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$admin_headers .= apply_filters( 'edd_admin_sale_notification_headers', $admin_headers, $payment_id, $payment_data );

	$admin_attachments = apply_filters( 'edd_admin_sale_notification_attachments', array(), $payment_id, $payment_data );

	wp_mail( $admin_email, $admin_subject, $admin_message, $admin_headers, $admin_attachments );
}
add_action( 'edd_admin_sale_notice', 'edd_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the EDD Settings)
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
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
 * @return bool
 */
function edd_admin_notices_disabled( $payment_id = 0 ) {
	global $edd_options;
	$retval = isset( $edd_options['disable_admin_notices'] );
	return apply_filters( 'edd_admin_notices_disabled', $retval, $payment_id );
}


/**
 * Get Purchase Receipt Template Tags
 *
 * Displays all available template tags for the purchase receipt.
 *
 * @since 1.6
 * @author Daniel J Griffiths
 * @return string $tags
 */
function edd_get_purchase_receipt_template_tags() {
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd') . '<br/>' .
		'{download_list} - ' . __('A list of download links for each download purchased', 'edd') . '<br/>' .
		'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased', 'edd') . '<br/>' .
		'{name} - ' . __('The buyer\'s first name', 'edd') . '<br/>' .
		'{fullname} - ' . __('The buyer\'s full name, first and last', 'edd') . '<br/>' .
		'{username} - ' . __('The buyer\'s user name on the site, if they registered an account', 'edd') . '<br/>' .
		'{user_email} - ' . __('The buyer\'s email address', 'edd') . '<br/>' .
		'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
		'{subtotal} - ' . __('The price of the purchase before taxes', 'edd') . '<br/>' .
		'{tax} - ' . __('The taxed amount of the purchase', 'edd') . '<br/>' .
		'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
		'{payment_id} - ' . __('The unique ID number for this purchase', 'edd') . '<br/>' .
		'{receipt_id} - ' . __('The unique ID number for this purchase receipt', 'edd') . '<br/>' .
		'{payment_method} - ' . __('The method of payment used for this purchase', 'edd') . '<br/>' .
		'{sitename} - ' . __('Your site name', 'edd') . '<br/>' .
		'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'edd' );

	return apply_filters( 'edd_purchase_receipt_template_tags_description', $tags );
}


/**
 * Get Sale Notification Template Tags
 *
 * Displays all available template tags for the sale notification email
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $tags
 */
function edd_get_sale_notification_template_tags() {
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'edd' ) . '<br/>' .
		'{download_list} - ' . __('A list of download links for each download purchased', 'edd') . '<br/>' .
		'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased', 'edd') . '<br/>' .
		'{name} - ' . __('The buyer\'s first name', 'edd') . '<br/>' .
		'{fullname} - ' . __('The buyer\'s full name, first and last', 'edd') . '<br/>' .
		'{username} - ' . __('The buyer\'s user name on the site, if they registered an account', 'edd') . '<br/>' .
		'{user_email} - ' . __('The buyer\'s email address', 'edd') . '<br/>' .
		'{date} - ' . __('The date of the purchase', 'edd') . '<br/>' .
		'{subtotal} - ' . __('The price of the purchase before taxes', 'edd') . '<br/>' .
		'{tax} - ' . __('The taxed amount of the purchase', 'edd') . '<br/>' .
		'{price} - ' . __('The total price of the purchase', 'edd') . '<br/>' .
		'{payment_id} - ' . __('The unique ID number for this purchase', 'edd') . '<br/>' .
		'{receipt_id} - ' . __('The unique ID number for this purchase receipt', 'edd') . '<br/>' .
		'{payment_method} - ' . __('The method of payment used for this purchase', 'edd') . '<br/>' .
		'{sitename} - ' . __('Your site name', 'edd');

	return apply_filters( 'edd_sale_notification_template_tags_description', $tags );
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
