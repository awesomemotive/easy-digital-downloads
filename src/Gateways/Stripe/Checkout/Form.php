<?php
/**
 * Form handling during the checkout process for the Stripe Gateway.
 *
 * @package EDD\Gateways\Stripe\Checkout
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents a form for processing Stripe checkout in Easy Digital Downloads Pro.
 */
class Form {
	use Traits\Recurring;

	/**
	 * Purchase data.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	private $purchase_data;

	/**
	 * Indicates whether an existing intent is being used.
	 *
	 * @var bool
	 */
	private $existing_intent = false;

	/**
	 * @var mixed $intent The intent variable holds the Stripe intent object.
	 */
	private $intent;

	/**
	 * @var mixed $customer The customer variable holds the Stripe customer object.
	 */
	private $customer;

	/**
	 * @var int $amount The amount variable holds the amount for the Stripe checkout form.
	 */
	private $amount;

	/**
	 * Constructor for the Form class.
	 *
	 * @since 3.3.5
	 * @param array $purchase_data The purchase data.
	 */
	public function __construct( $purchase_data ) {
		$this->purchase_data = $purchase_data;
	}

	/**
	 * Process the checkout form.
	 *
	 * This method is responsible for processing the checkout form and performing any necessary actions.
	 * It is called when the form is submitted.
	 *
	 * @since 3.3.5
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	public function process() {
		// Catch a straight to gateway request.
		// Remove the error set by the "gateway mismatch" and allow the redirect.
		if ( isset( $_REQUEST['edd_action'] ) && 'straight_to_gateway' === $_REQUEST['edd_action'] ) {
			edd_unset_error( 'edd-straight-to-gateway-error' );
			edd_send_back_to_checkout();
		}

		try {
			if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
				throw new \EDD_Stripe_Gateway_Exception( edd_stripe()->rate_limiting->get_rate_limit_error_message() );
			}

			$payment_method = $this->get_payment_method();
			if ( empty( $payment_method ) ) {
				throw new \EDD_Stripe_Gateway_Exception(
					esc_html__(
						'Error 1008: An error occurred, but your payment may have gone through. Please contact the site administrator.',
						'easy-digital-downloads'
					),
					'No payment method provided.'
				);
			}

			/**
			 * Allows processing before an Intent is created.
			 *
			 * @since 2.7.0
			 *
			 * @param array $purchase_data Purchase data.
			 */
			do_action( 'edds_pre_process_purchase_form', $this->purchase_data );

			$amount   = $this->get_amount();
			$customer = $this->get_customer();

			/**
			 * Allows processing before an Intent is created, but
			 * after a \Stripe\Customer is available.
			 *
			 * @since 2.7.0
			 *
			 * @param array            $purchase_data Purchase data.
			 * @param \Stripe\Customer $customer Stripe Customer object.
			 */
			do_action( 'edds_process_purchase_form_before_intent', $this->purchase_data, $customer );

			// Create the Payment Intent Builder.
			$intent_builder = new \EDD\Gateways\Stripe\PaymentIntents\Builder(
				$this->purchase_data,
				$payment_method,
				$customer,
				$amount
			);

			// Get the intent type for fingerprinting and response.
			$intent_type = $intent_builder->get_intent_type();

			// Build arguments for fingerprint comparison (before mandate options which include timestamp).
			$intent_args     = $intent_builder->get_arguments_for_fingerprint();
			$new_fingerprint = md5( json_encode( $intent_args ) );

			// Only update the intent, and process this further if we've made changes to the intent.
			if ( $this->existing_intent_unchanged( $new_fingerprint ) ) {
				return wp_send_json_success(
					array(
						'intent_id'          => $this->intent->id,
						'client_secret'      => $this->intent->client_secret,
						'intent_type'        => $intent_type,
						'token'              => wp_create_nonce( 'edd-process-checkout' ),
						'intent_fingerprint' => $new_fingerprint,
						'intent_changed'     => 0,
					)
				);
			}

			// Create or update the intent using the Builder.
			if ( ! empty( $this->existing_intent ) && ! empty( $this->intent ) ) {
				$intent_builder->set_existing_intent( $this->intent );
				$intent = $intent_builder->update( $this->intent->id );
			} else {
				$intent = $intent_builder->create();
				$this->maybe_create_order( $payment_method['type'], $intent );
			}

			/**
			 * Allows further processing after an Intent is created.
			 *
			 * @since 2.7.0
			 *
			 * @param array                                     $purchase_data Purchase data.
			 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
			 * @param int                                       $payment_id EDD Payment ID.
			 */
			do_action( 'edds_process_purchase_form', $this->purchase_data, $intent );

			return wp_send_json_success(
				array(
					'intent_id'          => $intent->id,
					'client_secret'      => $intent->client_secret,
					'intent_type'        => $intent_type,
					'token'              => wp_create_nonce( 'edd-process-checkout' ),
					'intent_fingerprint' => $new_fingerprint,
					'intent_changed'     => 1,
				)
			);

		} catch ( \EDD\Vendor\Stripe\Exception\ApiErrorException $e ) {
			$error = $e->getJsonBody()['error'];

			// Record error in log.
			edd_record_gateway_error(
				esc_html__( 'Stripe Error 002', 'easy-digital-downloads' ),
				sprintf(
				/* translators: %s: Error message */
					esc_html__( 'There was an error while processing a Stripe payment. Order data: %s', 'easy-digital-downloads' ),
					wp_json_encode( $error['message'] )
				),
				0
			);

			return wp_send_json_error(
				array(
					'message' => esc_html(
						edds_get_localized_error_message( $error['type'], $error['message'] )
					),
				)
			);

			// Catch gateway processing errors.
		} catch ( \EDD_Stripe_Gateway_Exception $e ) {
			if ( true === $e->hasLogMessage() ) {
				edd_record_gateway_error(
					esc_html__( 'Stripe Error 003', 'easy-digital-downloads' ),
					$e->getLogMessage(),
					0
				);
			}

			return wp_send_json_error(
				array(
					'message' => esc_html( $e->getMessage() ),
				)
			);

			// Catch any remaining error.
		} catch ( \Exception $e ) {

			return wp_send_json_error(
				array(
					'message' => esc_html( $e->getMessage() ),
				)
			);
		}
	}

	/**
	 * Retrieves the payment method for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return array|false The payment method, or false if not found.
	 */
	private function get_payment_method() {
		if ( ! empty( $_REQUEST['payment_method'] ) && is_array( $_REQUEST['payment_method'] ) ) {
			return $_REQUEST['payment_method'];
		}

		return false;
	}

	/**
	 * Retrieves the amount for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return int The amount for the Stripe checkout form.
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function get_amount() {
		if ( ! is_null( $this->amount ) ) {
			return $this->amount;
		}

		if ( edds_is_zero_decimal_currency() ) {
			$this->amount = $this->purchase_data['price'];
		} else {
			$this->amount = round( $this->purchase_data['price'] * 100, 0 );
		}

		if ( ! $this->cart_contains_subscription() ) {
			return $this->amount;
		}

		if ( ! edd_gateway_supports_cart_contents( 'stripe' ) ) {
			throw new \EDD_Stripe_Gateway_Exception( edds_get_single_subscription_cart_error() );
		}

		if ( $this->cart_has_free_trial() ) {
			$this->amount = 0;
		}

		global $edd_recurring_stripe;
		remove_filter( 'edds_create_payment_intent_args', array( $edd_recurring_stripe, 'create_payment_intent_args' ), 10, 2 );

		return $this->amount;
	}

	/**
	 * Retrieves the customer associated with the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return mixed The customer object or null if not found.
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function get_customer() {
		if ( ! empty( $this->customer ) ) {
			return $this->customer;
		}

		$customer = false;
		$intent   = $this->get_intent();
		if ( $intent && ! empty( $this->intent->customer ) ) {
			$this->existing_intent = true;
			$customer              = edds_get_stripe_customer( $this->intent->customer, array() );
		}

		// We didn't have a customer on the existing intent. Make a new one.
		if ( empty( $customer ) ) {
			// Retrieves or creates a Stripe Customer.
			$customer = edds_checkout_setup_customer( $this->purchase_data );
		}

		if ( ! $customer ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to create customer. Please try again.',
					'easy-digital-downloads'
				)
			);
		}

		$this->customer = $customer;

		return $customer;
	}

	/**
	 * Retrieves the intent associated with the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return mixed The intent object or null if not found.
	 */
	private function get_intent() {
		if ( ! is_null( $this->intent ) ) {
			return $this->intent;
		}

		if ( ! empty( $_REQUEST['intent_id'] ) && ! empty( $_REQUEST['intent_fingerprint'] ) ) {
			$this->intent = edds_api_request( $_REQUEST['intent_type'], 'retrieve', $_REQUEST['intent_id'] );
		}

		return $this->intent;
	}


	/**
	 * Maybe creates an order for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @param string $payment_method_type The payment method type.
	 * @return int|false The order ID, or false if not created.
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function maybe_create_order( $payment_method_type, $intent ) {
		if ( in_array( $payment_method_type, array( 'card', 'link' ), true ) ) {
			return false;
		}

		$order    = new Order();
		$order_id = $order->create( $intent );
		if ( ! $order_id ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1009: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to insert order record. (1009)'
			);
		}
		edd_update_order_meta( $order_id, 'stripe_payment_method_type', $payment_method_type );

		return $order_id;
	}

	/**
	 * Checks if the intent needs more processing (likely due to args changing).
	 *
	 * @since 3.3.5
	 * @param string $new_fingerprint The new fingerprint.
	 * @return bool Returns true if the intent needs more processing, false otherwise.
	 */
	private function existing_intent_unchanged( $new_fingerprint ) {
		if ( ! $this->get_intent() || empty( $_REQUEST['intent_fingerprint'] ) ) {
			return false;
		}

		return hash_equals( sanitize_text_field( $_REQUEST['intent_fingerprint'] ), $new_fingerprint );
	}
}
