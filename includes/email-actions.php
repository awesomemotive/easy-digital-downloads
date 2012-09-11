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
