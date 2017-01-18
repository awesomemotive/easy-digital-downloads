<?php
/**
 * Manual Payment Gateway.
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Gateway_Manual Class.
 *
 * @since   2.7
 * @version 1.0
 */
class EDD_Gateway_Manual extends EDD_Gateway {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return void
	 */
	public function __construct() {
		$this->ID = 'manual';

		parent::__construct();
	}

	/**
	 * Process the purchase.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param array $purchase_data {
	 *    Purchase Data.
	 *
	 *    @type array  downloads    Array of download IDs.
	 *    @type float  price        Total price of cart contents.
	 *    @type string purchase_key Randomly generated purchase key.
	 *    @type string user_email   User's email address.
	 *    @type string date         Date.
	 *    @type int    user_id      User ID.
	 *    @type array  post_data    Array containing the $_POST data.
	 *    @type array  user_info    Array of user's information and used discount code.
	 *    @type array  cart_into    Array of cart details.
	 * }
	 * @return void
	 */
	public function process_purchase( $purchase_data = array() ) {
		if( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

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

		$payment = edd_insert_payment( $payment_data );

		if ( $payment ) {
			edd_update_payment_status( $payment, 'publish' );
			edd_empty_cart();
			edd_send_to_success_page();
		} else {
			edd_record_gateway_error( __( 'Payment Error', 'easy-digital-downloads' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'easy-digital-downloads' ), json_encode( $payment_data ) ), $payment );
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
		}
	}

	/**
	 * Checkout form. The form returns false as it is disabled for this gateway.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @return false
	 */
	public function cc_form() {
		return false;
	}
}