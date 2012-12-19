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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Resend Email Purchase Receipt
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_resend_purchase_receipt( $data ) {

	$purchase_id = $data['purchase_id'];
	edd_email_purchase_receipt( $purchase_id, false );


	// grab all downloads of the purchase and update their file download limits, if needed
	// this allows admins to resend purchase receipts to grant additional file downloads
	$downloads = edd_get_payment_meta_downloads( $purchase_id );
	if( is_array( $downloads ) ) {
		foreach( $downloads as $download ) {
			$limit = edd_get_file_download_limit( $download['id'] );
			if( ! empty( $limit ) ) {
				edd_set_file_download_limit_override( $download['id'], $purchase_id );
			}
		}
	}

	wp_redirect( add_query_arg( array( 'edd-message' => 'email_sent', 'edd-action' => false, 'purchase_id' => false ) ) );
	exit;
}
add_action( 'edd_email_links', 'edd_resend_purchase_receipt' );
