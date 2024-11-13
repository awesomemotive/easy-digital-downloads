<?php
/**
 * Handle completing the checkout process for the Stripe Gateway.
 *
 * @package EDD\Gateways\Stripe\Checkout
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Checkout Complete
 */
class Complete {

	/**
	 * Process the checkout completion.
	 *
	 * This method is responsible for processing the checkout completion for the Stripe gateway.
	 * It performs necessary actions such as updating the order status, sending notifications, etc.
	 *
	 * @since 3.3.5
	 * @return void
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	public function process() {
		// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
		_edds_map_form_data_to_request( $_POST );

		// Simulate being in an `edd_process_purchase_form()` request.
		_edds_fake_process_purchase_step();

		try {
			if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
				throw new \EDD_Stripe_Gateway_Exception(
					esc_html__(
						'Error 1001: An error occurred, but your payment may have gone through. Please contact the site administrator.',
						'easy-digital-downloads'
					),
					'Rate limit reached during order completion.'
				);
			}

			// This must happen in the Checkout flow, so validate the Checkout nonce.
			if ( false === edds_verify() ) {
				throw new \EDD_Stripe_Gateway_Exception(
					esc_html__(
						'Error 1002: An error occurred, but your payment may have gone through. Please contact the site administrator.',
						'easy-digital-downloads'
					),
					'Nonce verification failed during order completion.'
				);
			}

			$intent = $this->get_intent();

			// Get the existing order if one was created.
			if ( ! empty( $intent->metadata->edd_payment_id ) ) {
				$order_id = $intent->metadata->edd_payment_id;
			} else {
				// Create a new order.
				$order_builder = new Order();
				$order_id      = $order_builder->create( $intent );
			}
			self::mark_complete( edd_get_order( $order_id ), $intent );

			$order = edd_get_order( $order_id );

			wp_send_json_success(
				array(
					'intent'   => $intent,
					'nonce'    => wp_create_nonce( 'edd-process-checkout' ),
					'status'   => $order->status,
					'order_id' => $order->id,
				)
			);

			// Catch gateway processing errors.
		} catch ( \EDD_Stripe_Gateway_Exception $e ) {
			// Increase the rate limit count when something goes wrong mid-process.
			edd_stripe()->rate_limiting->increment_card_error_count();

			if ( true === $e->hasLogMessage() ) {
				edd_record_gateway_error(
					esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
					$e->getLogMessage(),
					0
				);
			}

			wp_send_json_error(
				array(
					'message' => esc_html( $e->getMessage() ),
				)
			);

			// Catch any remaining error.
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html( $e->getMessage() ),
				)
			);
		}
	}

	/**
	 * Mark the order as complete.
	 *
	 * @since 3.3.5
	 *
	 * @param \EDD\Orders\Order                         $order  The EDD Order object.
	 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent The Stripe Intent object.
	 * @return void
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	public static function mark_complete( $order, $intent ) {
		if ( 'succeeded' !== $intent->status ) {
			return;
		}
		$final_status = edds_is_preapprove_enabled() ? 'preapproval' : 'complete';
		if ( $final_status === $order->status ) {
			return;
		}

		$updated = edd_update_order_status( $order->id, $final_status );
		if ( $updated ) {

			if ( 'setup_intent' !== $intent['object'] ) {
				$charge_id = sanitize_text_field( current( $intent['charges']['data'] )['id'] );

				edd_add_note(
					array(
						'object_id'   => $order->id,
						'content'     => 'Stripe Charge ID: ' . $charge_id,
						'user_id'     => is_admin() ? get_current_user_id() : 0,
						'object_type' => 'order',
					)
				);

				$order_transaction = edd_get_order_transaction_by( 'object_id', $order->id );
				if ( ! empty( $order_transaction ) ) {
					edd_update_order_transaction(
						$order_transaction->id,
						array(
							'transaction_id' => sanitize_text_field( $charge_id ),
							'status'         => 'complete',
						)
					);
				}
			}

			self::do_legacy_complete_hook( $order, $intent );

			/**
			 * Allows further processing after a order is completed.
			 *
			 * Sends back just the Intent ID to avoid needing always retrieve
			 * the intent in this step, which has been transformed via JSON,
			 * and is no longer a \Stripe\PaymentIntent
			 *
			 * @since 2.9.0
			 *
			 * @param \EDD\Orders\Order $order   The EDD Order object.
			 * @param string       $intent_id Stripe Intent ID.
			 */
			do_action( 'edds_order_complete', $order, $intent['id'] );

			// Empty cart.
			edd_empty_cart();
		} else {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1007: An error occurred completing the order, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to complete order. (1007)'
			);
		}
	}

	/**
	 * Mark an order complete based on a successful charge. This may used for
	 * slower payment methods like bank transfers.
	 *
	 * This method does not run the legacy complete hook.
	 *
	 * @since 3.3.5
	 * @param EDD\Orders\Order         $order  The EDD Order object.
	 * @param EDD\Vendor\Stripe\Charge $charge The Stripe Charge object.
	 * @return void
	 */
	public static function mark_complete_from_charge( $order, $charge ) {
		$final_status = edds_is_preapprove_enabled() ? 'preapproval' : 'complete';
		if ( $final_status === $order->status ) {
			return;
		}

		$updated = edd_update_order_status( $order->id, $final_status );
		if ( ! $updated ) {
			return;
		}

		edd_add_note(
			array(
				'object_id'   => $order->id,
				'content'     => 'Stripe Charge ID: ' . $charge->id,
				'user_id'     => 0,
				'object_type' => 'order',
			)
		);

		/**
		 * Allows further processing after an order is completed.
		 *
		 * @since 3.3.5
		 *
		 * @param \EDD\Orders\Order $order     The EDD Order object.
		 * @param string            $intent_id Stripe Payment Intent ID.
		 */
		do_action( 'edds_order_complete', $order, $charge->payment_intent );
	}

	/**
	 * Retrieve the Intent ID.
	 *
	 * @since 3.3.5
	 * @return string
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function get_intent_id() {
		$intent_id = isset( $_REQUEST['intent_id'] ) ? $_REQUEST['intent_id'] : '';
		if ( empty( $intent_id ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1003: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve Intent data during payment completion.'
			);
		}

		return $intent_id;
	}

	/**
	 * Retrieve the Intent.
	 *
	 * @since 3.3.5
	 * @return \Stripe\PaymentIntent|\Stripe\SetupIntent
	 */
	private function get_intent() {
		$request   = 'SetupIntent' === $_REQUEST['intent_type'] ? 'SetupIntent' : 'PaymentIntent';
		$intent_id = $this->get_intent_id();
		$intent    = edds_api_request( $request, 'retrieve', $intent_id );

		if ( ! $intent ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1005: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Missing PaymentIntent ' . $intent_id . ' during order completion.'
			);
		}

		return $intent;
	}

	/**
	 * Performs the legacy complete hook for the Stripe checkout.
	 *
	 * @param object $order The order object.
	 * @param object $intent The intent object.
	 * @return void
	 */
	private static function do_legacy_complete_hook( $order, $intent ) {
		if ( ! has_action( 'edds_payment_complete' ) ) {
			return;
		}

		// Load up an EDD Payment record here, in the event there is something hooking into it.
		$payment = new \EDD_Payment( $order->id );

		/**
		 * Allows further processing after a payment is completed.
		 *
		 * Sends back just the Intent ID to avoid needing always retrieve
		 * the intent in this step, which has been transformed via JSON,
		 * and is no longer a \Stripe\PaymentIntent
		 *
		 * NOTE TO DEVELOPERS: Only hook into one of these complete hooks. Using both will result in
		 * unexpected double processing.
		 *
		 * @since 2.7.0
		 *
		 * @param \EDD_Payment $payment   EDD Payment.
		 * @param string       $intent_id Stripe Intent ID.
		 */
		do_action( 'edds_payment_complete', $payment, $intent['id'] );
	}
}
