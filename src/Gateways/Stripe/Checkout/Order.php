<?php
/**
 * Handle the order creation process for the Stripe Gateway.
 *
 * @package EDD\Gateways\Stripe\Checkout
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Stripe Order class.
 */
class Order {

	/**
	 * The purchase data.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	private $purchase_data;

	/**
	 * Constructor.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {
		$this->purchase_data = $this->get_purchase_data();
	}

	/**
	 * Create the order.
	 *
	 * @since 3.3.5
	 * @return false|int The order ID on success, false on failure.
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	public function create( $intent ) {
		// Ensure $_COOKIE is available without a new HTTP request.
		if ( \EDD\Checkout\AutoRegister::is_enabled() ) {
			add_action( 'set_logged_in_cookie', 'edd_set_logged_in_cookie' );
		}

		$order_id = edd_build_order( $this->purchase_data );
		if ( false === $order_id ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1006: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to insert order record. (1006)'
			);
		}

		$this->update_intent( $order_id, $intent );

		return $order_id;
	}

	/**
	 * Update the order and the Intent.
	 *
	 * @since 3.3.5
	 * @param int   $order_id The order ID.
	 * @param mixed $intent The Intent.
	 * @return int The order ID.
	 */
	public function update_intent( $order_id, $intent ) {

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return $order_id;
		}

		if ( edd_get_order_meta( $order->id, '_edds_stripe_payment_intent_id', true ) ) {
			return $order_id;
		}

		// Retrieve the relevant Intent.
		if ( 'setup_intent' === $intent->object ) {
			$intent = $this->maybe_update_intent( $intent, $order->id, 'SetupIntent' );

			edd_add_order_meta(
				$order->id,
				'_edds_stripe_setup_intent_id',
				$intent->id
			);
		} else {
			$intent = $this->maybe_update_intent( $intent, $order->id, 'PaymentIntent' );

			edd_add_order_meta(
				$order->id,
				'_edds_stripe_payment_intent_id',
				$intent->id
			);

			// Use Intent ID for temporary transaction ID.
			// It will be updated when a charge is available.
			edd_add_order_transaction(
				array(
					'object_id'      => $order->id,
					'object_type'    => 'order',
					'transaction_id' => sanitize_text_field( $intent->id ),
					'gateway'        => 'stripe',
					'status'         => 'pending',
					'total'          => $order->total,
				)
			);
		}

		// Adds the customer ID to the order meta.
		edd_update_order_meta( $order->id, '_edds_stripe_customer_id', $intent->customer );

		if ( ! empty( $intent->charges->data ) ) {
			foreach ( $intent->charges->data as $charge ) {
				$this->add_payment_method( $order->id, $charge->payment_method_details );
				if ( empty( $charge->payment_method_details->card->mandate ) ) {
					continue;
				}

				// The returned Intent charges might contain a mandate ID, so let's save that and make a note.
				$mandate_id = $charge->payment_method_details->card->mandate;
				edd_update_order_meta( $order->id, '_edds_stripe_mandate', $mandate_id );
			}
		}

		// Attach the \Stripe\Customer ID to the \EDD_Customer meta if one exists.
		$edd_customer = edd_get_customer_by( 'email', $this->purchase_data['user_email'] );
		if ( $edd_customer ) {
			$edd_customer->update_meta( edd_stripe_get_customer_key(), $intent->customer );
		}

		if ( \EDD\Checkout\AutoRegister::is_enabled() ) {
			remove_action( 'set_logged_in_cookie', 'edd_set_logged_in_cookie' );
		}

		$this->do_legacy_create_hook( $order, $intent );

		/**
		 * Allows further processing after a order is created.
		 *
		 * Sends back just the Intent ID to avoid needing always retrieve
		 * the intent in this step, which has been transformed via JSON,
		 * and is no longer a \Stripe\PaymentIntent
		 *
		 * @since 2.9.0
		 *
		 * @param \EDD\Orders\Order                         $order EDD Order Object.
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
		 */
		do_action( 'edds_order_created', $order, $intent );

		return $order_id;
	}

	/**
	 * Add the payment method to the order meta.
	 *
	 * @since 3.3.5
	 * @param int   $order_id              The order ID.
	 * @param mixed $payment_method_details The payment method details.
	 */
	public static function add_payment_method( $order_id, $payment_method_details ) {
		if ( empty( $payment_method_details->type ) ) {
			return;
		}
		$type = $payment_method_details->type;
		if ( 'card' === $type && ! empty( $payment_method_details->card->wallet->type ) ) {
			$type = $payment_method_details->card->wallet->type;
		}
		if ( $type !== edd_get_order_meta( $order_id, 'stripe_payment_method_type', true ) ) {
			edd_update_order_meta( $order_id, 'stripe_payment_method_type', $type );
		}
	}

	/**
	 * Retrieve the purchase data.
	 *
	 * @since 3.3.5
	 * @return array
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function get_purchase_data() {
		$purchase_data = \EDD\Sessions\PurchaseData::get( false );
		if ( empty( $purchase_data ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1004: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve purchase data during payment creation.'
			);
		}

		return $purchase_data;
	}

	/**
	 * Maybe update the Intent with the EDD order id.
	 *
	 * @since 3.3.5
	 * @param mixed  $intent      The Intent.
	 * @param int    $order_id    The order ID.
	 * @param string $intent_type The Intent type.
	 * @return mixed The Intent.
	 */
	private function maybe_update_intent( $intent, $order_id, $intent_type ) {

		if ( isset( $intent->metadata->edd_payment_id ) ) {
			return $intent;
		}

		return edds_api_request(
			$intent_type,
			'update',
			$intent->id,
			array(
				'metadata' => array(
					'edd_payment_id' => $order_id,
				),
			)
		);
	}

	/**
	 * Legacy hook for `edds_payment_created`.
	 *
	 * @since 3.3.5
	 */
	private function do_legacy_create_hook( $order, $intent ) {
		if ( ! has_action( 'edds_payment_created' ) ) {
			return;
		}

		// Load up an EDD Payment record here, in the event there is something hooking into it.
		$payment = new \EDD_Payment( $order->id );

		/**
		 * Allows further processing after a payment is created.
		 *
		 * NOTE TO DEVELOPERS: Only hook into one of these complete hooks. Using both will result in
		 * unexpected double processing.
		 *
		 * @since 2.7.0
		 *
		 * @param \EDD_Payment                              $payment EDD Payment.
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
		 */
		do_action( 'edds_payment_created', $payment, $intent );
	}
}
