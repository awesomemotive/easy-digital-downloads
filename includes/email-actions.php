<?php
/**
 * Email Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Email Actions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.2
*/



/**
 * Resend Email Purchase Receipt
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_resend_purchase_receipt($data) {
	$purchase_id = $data['purchase_id'];
	edd_email_purchase_receipt( $purchase_id, false );
	wp_redirect( add_query_arg( array( 'edd-message' => 'email_sent', 'edd-action' => false, 'purchase_id' => false ) ) ); exit;
}
add_action('edd_email_links', 'edd_resend_purchase_receipt');


/**
 * Export all customers to CSV
 * 
 * Using wpdb directly for performance reasons (workaround of calling all posts and fetch data respectively)
 * 
 */
function edd_export_all_customers() {
	if( current_user_can( 'administrator' ) ) {
		global $wpdb;
		
		$emails = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' ");
		
		if( ! empty( $emails ) ) {
			header("Content-type: text/csv");
			$today = date("Y-m-d");
			header("Content-Disposition: attachment; filename=user_emails-$today.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			
			echo implode( "\n", $emails );
			exit;
		}
	} else {
		wp_die(__( 'Export not allowed for non-administrators.', 'edd' ) );
	}
}
add_action( 'edd_email_export', 'edd_export_all_customers' );
