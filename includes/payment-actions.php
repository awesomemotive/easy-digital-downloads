<?php
/**
 * Payment Actions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Payment Actions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


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

function edd_update_edited_purchase($data) {
	if(wp_verify_nonce($data['edd-payment-nonce'], 'edd_payment_nonce')) {
		$payment_id = $_POST['payment-id'];
		$payment_data = get_post_meta($payment_id, '_edd_payment_meta', true);
		if(isset($_POST['edd-purchased-downloads'])) {
			$updated_downloads = array();
			foreach( $_POST['edd-purchased-downloads'] as $download ) {
				if(isset($payment_data['cart_details'])) {
					$updated_downloads[] = array('id' => $download );
				} else {
					$updated_downloads[] = $download;	
				}
			}	
			$payment_data['downloads'] = serialize($updated_downloads);
			
		}		
		$payment_data['email'] = strip_tags($_POST['edd-buyer-email']);
		update_post_meta($payment_id, '_edd_payment_meta', $payment_data);
		update_post_meta($payment_id, '_edd_payment_user_email', $payment_data['email']);
		if($_POST['edd-old-status'] != $_POST['edd-payment-status']) {
			wp_update_post(array('ID' => $payment_id, 'post_status' => $_POST['edd-payment-status']));
		}
	}
}
add_action('edd_edit_payment', 'edd_update_edited_purchase');


/**
 * Delete Purchase
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_delete_purchase($data) {
	if(wp_verify_nonce($data['_wpnonce'], 'edd_payment_nonce')) {
		$payment_id = $data['purchase_id'];
		wp_delete_post($payment_id, true);
		wp_redirect(admin_url('/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted')); exit;
	}
}
add_action('edd_delete_payment', 'edd_delete_purchase');