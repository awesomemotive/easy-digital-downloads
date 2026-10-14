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
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Square\Checkout\Transactions\Single;
use EDD\Gateways\Square\Exceptions\PaymentDeclined;
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
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'] ?? '', 'edd-gateway' ) ) {
			edd_debug_log( 'EDD Square: Gateway nonce verification failed.' );
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ),
				)
			);

			return;
		}

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
	 * @since 3.7.2 Builds the order and claims its discount uses before Square is asked to take payment.
	 *
	 * @throws \Exception If there is an error processing the single payment.
	 * @throws \Throwable An unexpected error, after failing an order Square did not charge.
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
			$order_id = edd_build_order( self::$purchase_data );
			if ( false === $order_id ) {
				edd_debug_log( 'Square error: Error 1106: Unable to build the order before payment.' );
				throw new \Exception( esc_html__( 'Error 1106: An error occurred while creating your order. Please try again.', 'easy-digital-downloads' ) );
			}

			$refusal = \EDD\Discounts\OrderValidation::refuse_unclaimed( $order_id );
			if ( $refusal ) {
				$refresh = true;
				throw new \Exception( esc_html( $refusal ) );
			}

			// A processing order can't be resumed by a later submit; one already rebuilding the order is not stopped.
			if ( ! \EDD\Discounts\OrderValidation::start_capture( $order_id ) ) {
				throw new \Exception( esc_html__( 'This order can no longer be processed.', 'easy-digital-downloads' ) );
			}

			$transaction = new Single( self::$purchase_data, $args );
			try {
				$result = $transaction->process();
			} catch ( \Throwable $e ) {
				// Only an order Square certainly did not charge is failed, which gives its discount uses back.
				if ( $transaction->has_payment() ) {
					self::record_payment( $order_id, $transaction->get_square_order_id(), $transaction->get_payment_id(), 'pending' );
				} elseif ( $e instanceof PaymentDeclined || ! $transaction->sent_payment_request() ) {
					edd_update_order_status( $order_id, 'failed' );
				} elseif ( $transaction->get_square_order_id() ) {
					// The charge may have gone through, so leave the order processing where the webhook can find it.
					edd_update_order_meta( $order_id, 'square_order_id', $transaction->get_square_order_id() );
				}
				throw $e;
			}

			$payment_status      = strtoupper( $result['payment']->getStatus() );
			$is_payment_complete = 'COMPLETED' === $payment_status;

			self::record_payment( $order_id, $result['order']->getId(), $result['payment']->getId(), $is_payment_complete ? 'complete' : 'pending' );

			// An order whose payment has not completed yet is completed by Square's order.updated webhook.
			if ( $is_payment_complete ) {
				// Square's webhook can complete the order once its payment is recorded.
				$updated = edd_update_order_status( $order_id, 'complete' );
				if ( ! $updated && 'complete' !== edd_get_order( $order_id )->status ) {
					edd_debug_log( 'Square error: ' . esc_html__( 'Error 1107: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
					throw new \Exception( esc_html__( 'Error 1107: An error occurred, but your payment may have gone through. Please contact the site administrator.', 'easy-digital-downloads' ) );
				}
			} else {
				\EDD\Discounts\OrderValidation::end_capture( $order_id, 'pending' );
			}

			edd_empty_cart();
		} catch ( \Exception $e ) {
			edd_debug_log( 'Square error: ' . $e->getMessage() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'refresh' => isset( $refresh ),
				)
			);

			return;
		}

		wp_send_json_success(
			array(
				'square_payment_status' => $payment_status,
			)
		);
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

	/**
	 * Records the payment Square took for an order, so Square's webhook can find the order.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id        The EDD order ID.
	 * @param string $square_order_id The Square order ID.
	 * @param string $payment_id      The Square payment ID.
	 * @param string $status          The transaction status.
	 * @return void
	 */
	private static function record_payment( $order_id, $square_order_id, $payment_id, $status ) {
		edd_update_order_meta( $order_id, 'square_order_id', $square_order_id );

		edd_add_order_transaction(
			array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => sanitize_text_field( $payment_id ),
				'gateway'        => 'square',
				'status'         => $status,
				'total'          => self::$purchase_data['price'],
			)
		);
	}
}
