<?php
/**
 * Manual Gateway
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * Manual Gateway does not need a CC form, so remove it. This function is only
 * defined so that the credit card form isn't shown.
 *
 * @since 1.0
 * @return void
 */
function edd_manual_remove_cc_form() {
	/** We only register the action so that the default CC form is not shown */
}
add_action( 'edd_manual_cc_form', 'edd_manual_remove_cc_form' );

/**
 * Processes the purchase data and uses the Manual Payment gateway to record
 * the transaction in the Purchase History
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @param array $purchase_data Purchase Data
 * @return void
*/
function edd_manual_payment( $purchase_data ) {
	global $edd_options;

	/*
	* Purchase data comes in like this
	*
	$purchase_data = array(
		'downloads' => array of download IDs,
		'price' => total price of cart contents,
		'purchase_key' =>  // Random key
		'user_email' => $user_email,
		'date' => date('Y-m-d H:i:s'),
		'user_id' => $user_id,
		'post_data' => $_POST,
		'user_info' => array of user's information and used discount code
		'cart_details' => array of cart details,
	);
	*/

	$payment_data = array(
		'price' 		=> $purchase_data['price'],
		'date' 			=> $purchase_data['date'],
		'user_email' 	=> $purchase_data['user_email'],
		'purchase_key' 	=> $purchase_data['purchase_key'],
		'currency' 		=> edd_get_currency(),
		'downloads' 	=> $purchase_data['downloads'],
		'user_info' 	=> $purchase_data['user_info'],
		'cart_details' 	=> $purchase_data['cart_details'],
		'status' 		=> 'pending'
	);

	// Record the pending payment
	$payment = edd_insert_payment( $payment_data );

	if ( $payment ) {
		edd_update_payment_status( $payment, 'publish' );
		// Empty the shopping cart
		edd_empty_cart();
		edd_send_to_success_page();
	} else {
		edd_record_gateway_error( __( 'Payment Error', 'edd' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'edd' ), json_encode( $payment_data ) ), $payment );
		// If errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	}
}
add_action( 'edd_gateway_manual', 'edd_manual_payment' );