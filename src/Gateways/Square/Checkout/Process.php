<?php
/**
 * Process the Square checkout process.
 *
 * When clicking the "Complete Purchase" button, the form is submitted to the 'wp_ajax_edd_square_process_checkout_form' action.
 *
 * If the validate method is successful, it fires off the 'edd_gateway_square' action, which then in turn calls the 'process' method.
 *
 * The 'process' method will handle the following:
 * 1. Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
 * 2. Simulate being in an `edd_process_purchase_form()` request.
 * 3. If the cart contains a recurring item, process the recurring payment.
 * 4. If the cart contains a single payment, process the single payment.
 *
 * Once done, it returns the created order ID to the client to continue with the payment collection.
 *
 * @package     EDD\Gateways\Square\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Checkout\Transactions\Single;
use EDD\Gateways\Square\Helpers\Compat;
use EDD\Gateways\Square\Helpers\Customer;
use EDD\Gateways\Square\Helpers\Address;

/**
 * Process the Square checkout process.
 *
 * @since 3.4.0
 */
class Process {

	/**
	 * The purchase data.
	 *
	 * @var array
	 */
	protected static $purchase_data;

	/**
	 * The customer data.
	 *
	 * @var array
	 */
	protected static $customer_data;

	/**
	 * Process the payment.
	 *
	 * @since 3.4.0
	 *
	 * @param array $purchase_data The purchase data.
	 * @return void
	 */
	public static function process( $purchase_data ) {
		self::$purchase_data = $purchase_data;

		// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
		Compat::map_form_data_to_request( $_POST );

		// Simulate being in an `edd_process_purchase_form()` request.
		Compat::mock_process_purchase_step();

		if ( (bool) ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() ) ) {
			self::process_recurring();

			return;
		}

		self::process_single();
	}

	/**
	 * Process the single payment.
	 *
	 * @since 3.4.0
	 *
	 * @throws \Exception If there is an error processing the single payment.
	 */
	private static function process_single() {
		edd_debug_log( 'EDD Square: Processing single payment.' );

		self::$customer_data = Customer::maybe_create_customer( self::$purchase_data );

		$args = array();

		// Add the Square Customer ID.
		$args['customer_id'] = self::$customer_data['square_customer_id'];

		// Add the EDD Customer ID.
		$args['edd_customer_id'] = self::$customer_data['edd_customer']->id;

		// Sanitize the first and last names.
		$first_name = isset( self::$purchase_data['user_info']['first_name'] )
			? sanitize_text_field( self::$purchase_data['user_info']['first_name'] )
			: '';
		$last_name  = isset( self::$purchase_data['user_info']['last_name'] )
			? sanitize_text_field( self::$purchase_data['user_info']['last_name'] )
			: '';

		// Billing Name.
		$args['billing']['first_name'] = $first_name;
		$args['billing']['last_name']  = $last_name;

		// Billing Address.
		if ( ! empty( self::$purchase_data['user_info']['address'] ) ) {
			$address = self::$purchase_data['user_info']['address'];

			// Add the first and last names to the billing address information.
			$address['first_name'] = $first_name;
			$address['last_name']  = $last_name;

			// Format the address.
			$args['billing']['address'] = Address::build_address_object( $address );
		}

		// Buyer Email.
		if ( ! empty( self::$customer_data['edd_customer']->email ) ) {
			$args['buyer_email'] = sanitize_email( self::$purchase_data['user_info']['email'] );
		}

		$args['currency'] = edd_get_currency();

		try {
			$transaction = new Single( self::$purchase_data, $args );
			$result      = $transaction->process();

			$order_id = edd_build_order( self::$purchase_data );
			if ( false === $order_id ) {
				edd_debug_log( 'Square error: ' . esc_html__( 'Error 1106: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
				throw new \Exception( esc_html__( 'Error 1106: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
			}

			// Mark the order as completed.
			$updated = edd_update_order_status( $order_id, 'complete' );
			if ( ! $updated ) {
				edd_debug_log( 'Square error: ' . esc_html__( 'Error 1107: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
				throw new \Exception( esc_html__( 'Error 1107: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
			}

			// Save the Square Order ID to the order meta.
			edd_update_order_meta( $order_id, 'square_order_id', $result['order']->getId() );

			// Insert the order transaction ID.
			edd_add_order_transaction(
				array(
					'object_id'      => $order_id,
					'object_type'    => 'order',
					'transaction_id' => sanitize_text_field( $result['payment']->getId() ),
					'gateway'        => 'square',
					'status'         => 'complete',
					'total'          => self::$purchase_data['price'],
				)
			);

			edd_empty_cart();

			wp_send_json_success(
				array(
					'square_payment_status' => strtoupper( $result['payment']->getStatus() ),
				)
			);
		} catch ( \Exception $e ) {
			edd_debug_log( 'Square error: ' . $e->getMessage() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Process the recurring payment.
	 *
	 * @since 3.4.0
	 *
	 * @throws \Exception If there is an error processing the recurring payment.
	 */
	private static function process_recurring() {
	}
}
