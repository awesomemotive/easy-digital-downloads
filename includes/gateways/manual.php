<?php
/**
 * Manual Gateway
 *
 * @package     Easy Digital Downloads
 * @subpackage  Manual Gateway
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Manual Remove CC Form
 *
 * Manual does not need a CC form, so remove it.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_manual_remove_cc_form() {
	// we only register the action so that the default CC form is not shown
}
add_action( 'edd_manual_cc_form', 'edd_manual_remove_cc_form' );


/**
 * Manual Payment
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_manual_payment( $purchase_data ) {
	global $edd_options;

	/* 
	* purchase data comes in like this
	*
	$purchase_data = array(
		'downloads' => array of download IDs,
		'price' => total price of cart contents,
		'purchase_key' =>  // random key
		'user_email' => $user_email,
		'date' => date('Y-m-d H:i:s'),
		'user_id' => $user_id,
		'post_data' => $_POST,
		'user_info' => array of user's information and used discount code
		'cart_details' => array of cart details,
	);
	*/
	
	$payment = array( 
		'price' 		=> $purchase_data['price'], 
		'date' 			=> $purchase_data['date'], 
		'user_email' 	=> $purchase_data['user_email'],
		'purchase_key' 	=> $purchase_data['purchase_key'],
		'currency' 		=> $edd_options['currency'],
		'downloads' 	=> $purchase_data['downloads'],
		'user_info' 	=> $purchase_data['user_info'],
		'cart_details' 	=> $purchase_data['cart_details'],
		'status' 		=> 'pending'
	);
	
	// record the pending payment
	$payment = edd_insert_payment( $payment );
		
	if($payment) {
		edd_update_payment_status( $payment, 'publish' );
		// empty the shopping cart
		edd_empty_cart();
		edd_send_to_success_page();
	} else {
		// if errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	}
}
add_action( 'edd_gateway_manual', 'edd_manual_payment' );