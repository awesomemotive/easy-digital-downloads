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

	if( $old_status == 'publish' || $old_status == 'complete' )
		return; // make sure that payments are only completed once
	
	if( !edd_is_test_mode() ) {
				
		$payment_data 	= edd_get_payment_meta( $payment_id );
		$downloads 		= maybe_unserialize( $payment_data['downloads'] );
		$user_info 		= maybe_unserialize( $payment_data['user_info'] );
		$cart_details 	= maybe_unserialize( $payment_data['cart_details'] );				
					

		if( is_array( $downloads ) ) { 			
			// increase purchase count and earnings
			foreach( $downloads as $download ) {
				
				edd_record_sale_in_log( $download['id'], $payment_id, $user_info, $payment_data['date'] );
				edd_increase_purchase_count( $download['id'] );
				$amount = null;

				if( is_array( $cart_details ) ) {
					
					foreach( $cart_details as $key => $item ) {
						if( array_search( $download['id'], $item ) ) {
							$cart_item_id = $key;
						}
					}

					$amount = isset( $cart_details[$cart_item_id]['price'] ) ? $cart_details[$cart_item_id]['price'] : null;

				}

				$amount = edd_get_download_final_price( $download['id'], $user_info, $amount );
				edd_increase_earnings( $download['id'], $amount );
				
			}
		}
		
		if( isset( $user_info['discount'] ) ) {
			edd_increase_discount_usage( $user_info['discount'] );
		}
	}
	
	// empty the shopping cart
	edd_empty_cart();	
	
}
add_action( 'edd_update_payment_status', 'edd_complete_purchase', 10, 3 );


/**
 * Trigger Purchase Receipt
 *
 * Causes the purchase receipt to be emailed. 
 *
 * @access      private
 * @since       1.0.8.4 
 * @return      void
*/

function edd_trigger_purchase_receipt( $payment_id, $new_status, $old_status ) {

	if( $old_status == 'publish' || $old_status == 'complete')
		return;

	// send email with secure download link
	edd_email_purchase_receipt( $payment_id );

}
add_action( 'edd_update_payment_status', 'edd_trigger_purchase_receipt', 10, 3 );

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
	
	if( wp_verify_nonce( $data['edd-payment-nonce'], 'edd_payment_nonce' ) ) {

		$payment_id = $_POST['payment-id'];

		$payment_data = edd_get_payment_meta( $payment_id );

		if(isset($_POST['edd-purchased-downloads'])) {
			$updated_downloads = array();

			foreach( $_POST['edd-purchased-downloads'] as $download ) {
				if(isset($payment_data['cart_details'])) {
					$updated_downloads[] = array('id' => $download );
				} else {
					$updated_downloads[] = $download;	
				}
			}
			
			$payment_data['downloads'] = serialize( $updated_downloads );
		}		
		
		$payment_data['email'] = strip_tags( $_POST['edd-buyer-email'] );
		
		update_post_meta( $payment_id, '_edd_payment_meta', $payment_data );
		
		update_post_meta( $payment_id, '_edd_payment_user_email', $payment_data['email'] );
		
		if( $_POST['edd-old-status'] != $_POST['edd-payment-status'] ) {
			edd_update_payment_status( $payment_id, $_POST['edd-payment-status'] );
		}
		
		if( $_POST['edd-payment-status'] == 'publish' && isset( $_POST['edd-payment-send-email'] ) ) {
			// send the purchase receipt
			edd_email_purchase_receipt( $payment_id, false );
		}
	}
	
}
add_action( 'edd_edit_payment', 'edd_update_edited_purchase' );


/**
 * Delete Purchase
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_delete_purchase( $data ) {
	if( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {
		$payment_id = absint( $data['purchase_id'] );
		$downloads = edd_get_payment_meta_downloads( $payment_id );
		if( is_array( $downloads ) ) {
			// update sale counts and earnings for all purchased products
			foreach( $downloads as $download ) {
				edd_undo_purchase( $download['id'], $payment_id );
			}
		}					
		
		do_action( 'edd_payment_delete', $payment_id );
		wp_delete_post( $payment_id, true );
		do_action( 'edd_payment_deleted', $payment_id );
		wp_redirect( admin_url( '/edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
		exit;
	}
}
add_action( 'edd_delete_payment', 'edd_delete_purchase' );


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
	if( !wp_verify_nonce( $data['_wpnonce'], 'edd_upgrade_payments_nonce' ) )
		return;

	if( get_option( 'edd_payment_totals_upgraded' ) )
		return;

	$payments = edd_get_payments( array(
		'offset' => 0, 
		'number' => -1
	) );

	if( $payments ) {
		foreach( $payments as $payment ) {
			$meta = edd_get_payment_meta( $payment->ID );
			update_post_meta( $payment->ID, '_edd_payment_total', $meta['amount'] );
		}
	}

	add_option( 'edd_payment_totals_upgraded', 1 );
}
add_action( 'edd_upgrade_payments', 'edd_update_old_payments_with_totals' );