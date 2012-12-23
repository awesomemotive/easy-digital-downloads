<?php
/**
 * Email Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Email Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Email Download Purchase Receipt
 *
 * Email the download link(s) and payment confirmation to the buyer.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_email_purchase_receipt( $payment_id, $admin_notice = true ) {
	global $edd_options;

	$payment_data = edd_get_payment_meta( $payment_id );
	$user_info = maybe_unserialize( $payment_data['user_info'] );

	if(isset($user_info['id']) && $user_info['id'] > 0) {
		$user_data = get_userdata($user_info['id']);
		$name = $user_data->display_name;
	} elseif( isset( $user_info['first_name'] ) && isset($user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $user_info['email'];
	}

	$message = edd_get_email_body_header();

		$message .= edd_get_email_body_content( $payment_id, $payment_data );

	$message .= edd_get_email_body_footer();

	$from_name = isset( $edd_options['from_name'] ) ? $edd_options['from_name'] : get_bloginfo('name');
	$from_email = isset( $edd_options['from_email'] ) ? $edd_options['from_email'] : get_option('admin_email');

	$subject = isset( $edd_options['purchase_subject'] ) && strlen( trim( $edd_options['purchase_subject'] ) ) > 0 ? edd_email_template_tags( $edd_options['purchase_subject'], $payment_data, $payment_id ) : __('Purchase Receipt', 'edd');

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";

	// allow add-ons to add file attachments
	$attachments = apply_filters( 'edd_receipt_attachments', array(), $payment_id, $payment_data );

	wp_mail( $payment_data['email'], $subject, $message, $headers, $attachments );

	if( $admin_notice ) {
		/* send an email notification to the admin */
		$admin_email   = edd_get_admin_notice_emails();
		$admin_message = __('Hello', 'edd') . "\n\n" . sprintf( __('A %s purchase has been made', 'edd'), edd_get_label_plural() ) . ".\n\n";
		$admin_message .= sprintf( __('%s sold:', 'edd'), edd_get_label_plural() ) .  "\n\n";

		$download_list = '';
		$downloads = maybe_unserialize( $payment_data['downloads'] );
		if( is_array( $downloads ) ) {
			foreach( $downloads as $download ) {
				$id = isset( $payment_data['cart_details'] ) ? $download['id'] : $download;
				$download_list .= html_entity_decode( get_the_title( $id ), ENT_COMPAT, 'UTF-8' ) . "\n";
			}
		}
		$gateway = edd_get_gateway_admin_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );

		$admin_message .= $download_list . "\n";
		$admin_message .= __('Purchased by: ', 'edd')   . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
		$admin_message .= __('Amount: ', 'edd')         . " " . html_entity_decode( edd_currency_filter( edd_format_amount( $payment_data['amount'] ) ), ENT_COMPAT, 'UTF-8' ) . "\n\n";
		$admin_message .= __('Payment Method: ', 'edd') . " " . $gateway . "\n\n";
		$admin_message .= __('Thank you', 'edd');
		$admin_message = apply_filters( 'edd_admin_purchase_notification', $admin_message, $payment_id, $payment_data );

		wp_mail( $admin_email, __('New download purchase', 'edd'), $admin_message );
	}
}


/**
 * Retrieves the admin notice emails
 *
 * If not emails are set, the WordPress admin email is used instead
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_get_admin_notice_emails() {

	global $edd_options;

	$emails = isset( $edd_options['admin_notice_emails'] ) && strlen( trim( $edd_options['admin_notice_emails'] ) ) > 0 ? $edd_options['admin_notice_emails'] : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'edd_admin_notice_emails', $emails );
}
