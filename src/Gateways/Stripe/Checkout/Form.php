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

	/**
	 * Purchase data.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	private $purchase_data;

	/**
	 * Indicates whether the cart contains a subscription.
	 *
	 * @var bool
	 */
	private $cart_contains_subscription;

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
			foreach ( $this->purchase_data['downloads'] as $download ) {
				$options             = isset( $download['options'] ) ? $download['options'] : array();
				$options['quantity'] = isset( $download['quantity'] ) ? $download['quantity'] : 1;

				edd_add_to_cart( $download['id'], $options );
			}

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

			// Shared Intent arguments.
			$intent_args = $this->get_intent_args( $payment_method );

			// We need the intent type later, so we'll set it here.
			$intent_type = ( 0 === $amount || edds_is_preapprove_enabled() ) ? 'SetupIntent' : 'PaymentIntent';

			// Update the intent arguments based on the intent type.
			if ( 'SetupIntent' === $intent_type ) {
				$intent_args = $this->update_setup_intent_args( $intent_args );
			} else {
				$intent_args = $this->update_payment_intent_args( $intent_args, $payment_method['type'] );
			}

			if ( edd_stripe()->application_fee->has_application_fee() ) {
				$application_fee = edd_stripe()->application_fee->get_application_fee_amount( $amount );
				if ( ! empty( $application_fee ) ) {
					$intent_args['application_fee_amount'] = $application_fee;
				}
			}

			$new_fingerprint = md5( json_encode( $intent_args ) );

			// Only update the intent, and process this further if we've made changes to the intent.
			if ( $this->existing_intent_unchanged( $new_fingerprint ) ) {
				return wp_send_json_success(
					array(
						'intent_id'          => $this->intent->id,
						'client_secret'      => $this->intent->client_secret,
						'intent_type'        => $this->intent_type,
						'token'              => wp_create_nonce( 'edd-process-checkout' ),
						'intent_fingerprint' => $new_fingerprint,
						'intent_changed'     => 0,
					)
				);
			}

			/**
			 * If purchasing a subscription with a card, we need to add the subscription mandate data.
			 *
			 * This will ensure that any cards that require mandates like INR payments or India based cards will correctly add
			 * the mandates necessary for recurring payments.
			 *
			 * We do this after we check for an existing intent ID, because the mandate data will change depending on the 'timestamp'.
			 */
			if ( $this->is_mandate_required( $payment_method ) ) {
				require_once EDDS_PLUGIN_DIR . 'includes/utils/class-edd-stripe-mandates.php';
				$mandates = new \EDD_Stripe_Mandates( $this->purchase_data, $intent_type );

				// Add the mandate options to the intent arguments.
				$intent_args['payment_method_options']['card']['mandate_options'] = $mandates->mandate_options;
			}

			if ( ! empty( $this->existing_intent ) && ! empty( $this->intent ) ) {
				// Existing intents need to not have the automatic_payment_methods flag set.
				if ( ! empty( $intent_args['automatic_payment_methods'] ) ) {
					unset( $intent_args['automatic_payment_methods'] );
				}

				edds_api_request( $intent_type, 'update', $this->intent->id, $intent_args );
				$intent = edds_api_request( $intent_type, 'retrieve', $this->intent->id );
			} else {
				$intent = edds_api_request( $intent_type, 'create', $intent_args );
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
	 * Checks if the cart contains a subscription.
	 *
	 * @since 3.3.5
	 * @return bool Returns true if the cart contains a subscription, false otherwise.
	 */
	private function cart_contains_subscription() {
		if ( is_null( $this->cart_contains_subscription ) ) {
			$this->cart_contains_subscription = (bool) ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() );
		}

		return $this->cart_contains_subscription;
	}

	/**
	 * Checks if the cart has a free trial.
	 *
	 * @since 3.3.5
	 * @return bool Returns true if the cart has a free trial, false otherwise.
	 */
	private function cart_has_free_trial() {
		return $this->cart_contains_subscription() && edd_recurring()->cart_has_free_trial();
	}

	/**
	 * Retrieves the payment items for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return array The payment items for the Stripe checkout form.
	 */
	private function get_payment_items() {
		$payment_items = array();

		// Create a list of {$download_id}_{$price_id}.
		foreach ( $this->purchase_data['cart_details'] as $item ) {
			$item_id = $item['id'];
			if ( isset( $item['item_number']['options']['price_id'] ) ) {
				$price_id = $item['item_number']['options']['price_id'];
				$item_id .= '_' . intval( $price_id );
			}

			$payment_items[] = $item_id;
		}

		return $payment_items;
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
	 * Retrieves the future usage for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @param string $type The type of payment method.
	 * @return string The future usage for the Stripe checkout form.
	 */
	private function get_future_usage( $type ) {
		if ( 'link' === $type || $this->cart_contains_subscription() ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the intent arguments for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @param array $payment_method The payment method.
	 * @return array The intent arguments for the Stripe checkout form.
	 * @throws \EDD_Stripe_Gateway_Exception If an error occurs.
	 */
	private function get_intent_args( $payment_method ) {
		$customer = $this->get_customer();

		$intent_args = array(
			'customer'                  => $customer->id,
			'metadata'                  => array(
				'email'                => esc_html( $this->purchase_data['user_info']['email'] ),
				'edd_payment_subtotal' => esc_html( $this->purchase_data['subtotal'] ),
				'edd_payment_discount' => esc_html( $this->purchase_data['discount'] ),
				'edd_payment_tax'      => esc_html( $this->purchase_data['tax'] ),
				'edd_payment_tax_rate' => esc_html( $this->purchase_data['tax_rate'] ),
				'edd_payment_fees'     => esc_html( edd_get_cart_fee_total() ),
				'edd_payment_total'    => esc_html( $this->purchase_data['price'] ),
				'edd_payment_items'    => esc_html( implode( ', ', $this->get_payment_items() ) ),
				'zero_decimal_amount'  => $this->get_amount(),
			),
			'payment_method'            => sanitize_text_field( $payment_method['id'] ),
			'automatic_payment_methods' => array( 'enabled' => true ),
			'description'               => edds_get_payment_description( $this->purchase_data['cart_details'] ),

		);

		$payment_method_configuration = $this->get_payment_method_configuration();
		if ( ! empty( $payment_method_configuration ) ) {
			$intent_args['payment_method_configuration'] = $payment_method_configuration;
		}

		return $intent_args;
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
	 * @param array $new_fingerprint The new fingerprint.
	 * @return bool Returns true if the intent needs more processing, false otherwise.
	 */
	private function existing_intent_unchanged( $new_fingerprint ) {
		if ( ! $this->get_intent() || empty( $_REQUEST['intent_fingerprint'] ) ) {
			return false;
		}

		return hash_equals( $_REQUEST['intent_fingerprint'], $new_fingerprint );
	}

	/**
	 * Updates the SetupIntent arguments for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @param array $intent_args The intent arguments.
	 * @return array The updated SetupIntent arguments for the Stripe checkout form.
	 */
	private function update_setup_intent_args( $intent_args ) {
		$intent_args['usage'] = 'off_session';

		/**
		 * BETA Functionality.
		 *
		 * Sending the automatic_payment_methods flag to the SetupIntent is a beta feature that we have to enable via an API version
		 *
		 * @link https://stripe.com/docs/payments/defer-intent-creation?type=setup#create-intent
		 */
		add_action(
			'edds_pre_stripe_api_request',
			function () {
				\EDD\Vendor\Stripe\Stripe::setApiVersion( '2018-09-24;automatic_payment_methods_beta=v1' );
			},
			11
		);

		/**
		 * Filters the arguments used to create a SetupIntent.
		 *
		 * @since 2.7.0
		 *
		 * @param array $intent_args   SetupIntent arguments.
		 * @param array $purchase_data The purchase data.
		 */
		return apply_filters( 'edds_create_setup_intent_args', $intent_args, $this->purchase_data );
	}

	/**
	 * Updates the PaymentIntent arguments for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @param array  $intent_args         The intent arguments.
	 * @param string $payment_method_type The payment method type.
	 * @return array The updated PaymentIntent arguments for the Stripe checkout form.
	 */
	private function update_payment_intent_args( $intent_args, $payment_method_type ) {

		$intent_args['amount']   = $this->get_amount();
		$intent_args['currency'] = edd_get_currency();

		// If this is a card payment method, we need to add the statement descriptor suffix.
		if ( 'card' === $payment_method_type ) {
			$statement_descriptor_suffix = \EDD\Gateways\Stripe\StatementDescriptor::sanitize_suffix( $intent_args['description'] );
			if ( ! empty( $statement_descriptor_suffix ) ) {
				$intent_args['statement_descriptor_suffix'] = $statement_descriptor_suffix;
			}
		} elseif ( 'wechat_pay' === $payment_method_type ) {
			$intent_args['payment_method_options']['wechat_pay']['client'] = 'web';
		}

		if ( $this->get_future_usage( $payment_method_type ) ) {
			$intent_args['setup_future_usage'] = 'off_session';
		}

		/**
		 * Filters the arguments used to create a PaymentIntent.
		 *
		 * @since 2.7.0
		 *
		 * @param array $intent_args PaymentIntent arguments.
		 * @param array $purchase_data The purchase data.
		 */
		$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, $this->purchase_data );

		/**
		 * As of Feb 1, 2024, Stripe no longer allows Statement Descriptors for PaymentIntents with cards.
		 *
		 * @since 3.2.8
		 *
		 * Because of this EDD will always default to the Stripe settings, by sending no statement descriptor.
		 * If a developer was altering it with this method, then the filters will no longer work, in order to avoid
		 * failed payments from happening.
		 *
		 * Dynamic statement descriptors can be enabled by including the Order ID in the EDD Stripe
		 */
		if ( isset( $intent_args['statement_descriptor'] ) ) {
			unset( $intent_args['statement_descriptor'] );
		}

		return $intent_args;
	}

	/**
	 * Retrieves the payment method configuration for the Stripe checkout form.
	 *
	 * @since 3.3.5
	 * @return array The payment method configuration for the Stripe checkout form.
	 */
	private function get_payment_method_configuration() {
		$type = '';
		if ( $this->cart_contains_subscription() ) {
			$type = 'subscriptions';
			if ( $this->cart_has_free_trial() ) {
				$type = 'trials';
			}
		}

		return \EDD\Gateways\Stripe\PaymentMethods::get_configuration_id( $type );
	}

	/**
	 * Checks if a mandate is required for the Stripe checkout form.
	 *
	 * @since 3.3.6
	 * @param array $payment_method The payment method.
	 * @return bool Returns true if a mandate is required, false otherwise.
	 */
	private function is_mandate_required( $payment_method ) {
		/**
		 * Filters whether a mandate is required for the Stripe checkout form.
		 *
		 * @since 3.3.6
		 * @param bool  $mandate_required Whether a mandate is required.
		 * @param array $payment_method   The payment method.
		 */
		return apply_filters( 'edds_mandate_required', 'card' === $payment_method['type'], $payment_method );
	}
}
