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


/**
 * Email Download Purchase Receipt
 * 
 * Email the download link(s) and payment confirmation to the buyer.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_email_purchase_receipt($payment_id, $admin_notice = true) {
	global $edd_options;
	
	$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);

	$message = edd_get_email_body_header();

		$message .= edd_get_email_body_content( $payment_id, $payment_data );
		
	$message .= edd_get_email_body_header();	
	
	$from_name = isset($edd_options['from_name']) ? $edd_options['from_name'] : get_bloginfo('name');
	$from_email = isset($edd_options['from_email']) ? $edd_options['from_email'] : get_option('admin_email');
	
	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";	
		
	wp_mail( $payment_data['email'], $edd_options['purchase_subject'], $message, $headers);
	
	if($admin_notice) {
		/* send an email notification to the admin */
		$admin_email = isset($edd_options['from_email']) ? $edd_options['from_email'] : get_option('admin_email');
		$admin_message = __('Hello', 'edd') . "\n\n" . __('A download purchase has been made', 'edd') . ".\n\n";
		$admin_message .= __('Downloads sold:', 'edd') .  "\n\n";
			
		$download_list = '';	
		foreach(maybe_unserialize($payment_data['downloads']) as $download) {
			$id = isset($payment_data['cart_details']) ? $download['id'] : $download;
			$download_list .= get_the_title($id) . "\n";
		}
		
		$admin_message .= $download_list . "\n";
		$admin_message .= __('Amount: ', 'edd') . " " . html_entity_decode(edd_currency_filter($payment_data['amount'])) . "\n\n";
		$admin_message .= __('Thank you', 'edd');
		$admin_message = apply_filters('edd_admin_purchase_notification', $admin_message, $payment_id, $payment_data);
		wp_mail( $admin_email, __('New download purchase', 'edd'), $admin_message );
	}
}
