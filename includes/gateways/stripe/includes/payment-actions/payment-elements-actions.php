<?php
/**
 * Payment actions.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * If regional support is enabled, check if the card name field is required.
 */
function edds_maybe_disable_card_name() {
	// We no longer need a card name field for some regions, so remove the requirement if it's not needed.
	if ( false === edd_stripe()->has_regional_support() || false === edd_stripe()->regional_support->requires_card_name ) {
		add_filter(
			'edd_purchase_form_required_fields',
			function ( $required_fields ) {
				unset( $required_fields['card_name'] );
				return $required_fields;
			}
		);
		remove_action( 'edd_checkout_error_checks', 'edds_process_post_data' );
	}
}
add_action( 'edd_pre_process_purchase', 'edds_maybe_disable_card_name' );

/**
 * Starts the process of completing a purchase with Stripe.
 *
 * Generates an intent that can require user authorization before proceeding.
 *
 * @link https://stripe.com/docs/payments/intents
 * @since 2.7.0
 *
 * @param array $purchase_data {
 *   Purchase form data.
 *
 * }
 */
function edds_process_purchase_form( $purchase_data ) {
	// Catch a straight to gateway request.
	// Remove the error set by the "gateway mismatch" and allow the redirect.
	if ( isset( $_REQUEST['edd_action'] ) && 'straight_to_gateway' === $_REQUEST['edd_action'] ) {
		foreach ( $purchase_data['downloads'] as $download ) {
			$options             = isset( $download['options'] ) ? $download['options'] : array();
			$options['quantity'] = isset( $download['quantity'] ) ? $download['quantity'] : 1;

			edd_add_to_cart( $download['id'], $options );
		}

		edd_unset_error( 'edd-straight-to-gateway-error' );
		edd_send_back_to_checkout();

		return;
	}

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception( edd_stripe()->rate_limiting->get_rate_limit_error_message() );
		}

		/**
		 * Allows processing before an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array $purchase_data Purchase data.
		 */
		do_action( 'edds_pre_process_purchase_form', $purchase_data );

		if ( edds_is_zero_decimal_currency() ) {
			$amount = $purchase_data['price'];
		} else {
			$amount = round( $purchase_data['price'] * 100, 0 );
		}

		/**
		 * We need to unhook some of the Recurring Payments actions here as we're handling captures ourselves.
		 *
		 * We're also going to attempt to restrict this to a single subscription and no mixed carts, for the time being.
		 */
		$cart_contains_subscription = (bool) ( function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() );

		if ( $cart_contains_subscription ) {
			if ( ! edd_gateway_supports_cart_contents( 'stripe' ) ) {
				throw new \EDD_Stripe_Gateway_Exception( edds_get_single_subscription_cart_error() );
			}

			if ( edd_recurring()->cart_has_free_trial() ) {
				$amount = 0;
			}

			global $edd_recurring_stripe;
			remove_filter( 'edds_create_payment_intent_args', array( $edd_recurring_stripe, 'create_payment_intent_args' ), 10, 2 );
		}

		$existing_intent = false;
		$customer        = false;

		if ( ! empty( $_REQUEST['intent_id'] ) && ! empty( $_REQUEST['intent_fingerprint'] ) ) {
			$intent = edds_api_request( $_REQUEST['intent_type'], 'retrieve', $_REQUEST['intent_id'] );
			if ( ! empty( $intent->customer ) ) {
				$existing_intent = true;
				$customer        = edds_get_stripe_customer( $intent->customer, array() );
			}
		}

		// We didn't have a customer on the existing intent. Make a new one.
		if ( empty( $customer ) ) {
			// Retrieves or creates a Stripe Customer.
			$customer = edds_checkout_setup_customer( $purchase_data );
		}

		if ( ! $customer ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to create customer. Please try again.',
					'easy-digital-downloads'
				)
			);
		}

		/**
		 * Allows processing before an Intent is created, but
		 * after a \Stripe\Customer is available.
		 *
		 * @since 2.7.0
		 *
		 * @param array            $purchase_data Purchase data.
		 * @param \Stripe\Customer $customer Stripe Customer object.
		 */
		do_action( 'edds_process_purchase_form_before_intent', $purchase_data, $customer );

		// Create a list of {$download_id}_{$price_id}.
		$payment_items = array();

		foreach ( $purchase_data['cart_details'] as $item ) {
			$price_id = isset( $item['item_number']['options']['price_id'] )
				? $item['item_number']['options']['price_id']
				: null;

			$payment_items[] = $item['id'] . ( ! empty( $price_id ) ? ( '_' . $price_id ) : '' );
		}

		// Shared Intent arguments.
		$intent_args = array(
			'customer' => $customer->id,
			'metadata' => array(
				'email'                => esc_html( $purchase_data['user_info']['email'] ),
				'edd_payment_subtotal' => esc_html( $purchase_data['subtotal'] ),
				'edd_payment_discount' => esc_html( $purchase_data['discount'] ),
				'edd_payment_tax'      => esc_html( $purchase_data['tax'] ),
				'edd_payment_tax_rate' => esc_html( $purchase_data['tax_rate'] ),
				'edd_payment_fees'     => esc_html( edd_get_cart_fee_total() ),
				'edd_payment_total'    => esc_html( $purchase_data['price'] ),
				'edd_payment_items'    => esc_html( implode( ', ', $payment_items ) ),
				'zero_decimal_amount'  => $amount,
			),
		);

		$payment_method = $_REQUEST['payment_method'];

		// Attach the payment method.
		$intent_args['payment_method'] = sanitize_text_field( $payment_method['id'] );

		// Set to automatic payment methods so any of the supported methods can be used here.
		$intent_args['automatic_payment_methods'] = array( 'enabled' => true );

		// We need the intent type later, so we'll set it here.
		$intent_type = ( edds_is_preapprove_enabled() || 0 === $amount ) ? 'SetupIntent' : 'PaymentIntent';

		// Create a SetupIntent for a non-payment carts.
		if ( 'SetupIntent' === $intent_type ) {
			$intent_args = array_merge(
				array(
					'description' => edds_get_payment_description( $purchase_data['cart_details'] ),
					'usage'       => 'off_session',
				),
				$intent_args
			);

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
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'edds_create_setup_intent_args', $intent_args, $purchase_data );
		} else {
			$purchase_summary = edds_get_payment_description( $purchase_data['cart_details'] );

			// If this is a card payment method, we need to add the statement descriptor suffix.
			$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $intent_args['payment_method'] );
			if ( 'card' === $payment_method->type ) {
				$statement_descriptor_suffix = EDD\Gateways\Stripe\StatementDescriptor::sanitize_suffix( $purchase_summary );
				if ( ! empty( $statement_descriptor_suffix ) ) {
					$intent_args['statement_descriptor_suffix'] = $statement_descriptor_suffix;
				}
			}

			$intent_args = array_merge(
				array(
					'amount'             => $amount,
					'currency'           => edd_get_currency(),
					'description'        => $purchase_summary,
					'setup_future_usage' => 'off_session',
				),
				$intent_args
			);

			$intent_type = 'PaymentIntent';

			/**
			 * Filters the arguments used to create a SetupIntent.
			 *
			 * @since 2.7.0
			 *
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, $purchase_data );

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
		}

		if ( edd_stripe()->application_fee->has_application_fee() ) {
			$application_fee = edd_stripe()->application_fee->get_application_fee_amount( $amount );
			if ( ! empty( $application_fee ) ) {
				$intent_args['application_fee_amount'] = $application_fee;
			}
		}

		$new_fingerprint = md5( json_encode( $intent_args ) );

		// Only update the intent, and process this further if we've made changes to the intent.
		if ( ! empty( $_REQUEST['intent_id'] ) && ! empty( $_REQUEST['intent_fingerprint'] ) ) {
			if ( hash_equals( $_REQUEST['intent_fingerprint'], $new_fingerprint ) ) {
				return wp_send_json_success(
					array(
						'intent_id'          => $intent->id,
						'client_secret'      => $intent->client_secret,
						'intent_type'        => $intent_type,
						'token'              => wp_create_nonce( 'edd-process-checkout' ),
						'intent_fingerprint' => $new_fingerprint,
						'intent_changed'     => 0,
					)
				);
			}
		}

		/**
		 * If purchasing a subscription with a card, we need to add the subscription mandate data.
		 *
		 * This will ensure that any cards that require mandates like INR payments or India based cards will correctly add
		 * the mandates necessary for recurring payments.
		 *
		 * We do this after we check for an existing intent ID, because the mandate data will change depending on the 'timestamp'.
		 */
		if ( 'card' === $payment_method['type'] && true === $cart_contains_subscription ) {
			require_once EDDS_PLUGIN_DIR . 'includes/utils/class-edd-stripe-mandates.php';
			$mandates        = new EDD_Stripe_Mandates( $purchase_data, $intent_type );
			$mandate_options = $mandates->mandate_options;

			// Add the mandate options to the intent arguments.
			$intent_args['payment_method_options']['card']['mandate_options'] = $mandate_options;
		}

		if ( ! empty( $existing_intent ) ) {
			// Existing intents need to not have the automatic_payment_methods flag set.
			if ( ! empty( $intent_args['automatic_payment_methods'] ) ) {
				unset( $intent_args['automatic_payment_methods'] );
			}

			edds_api_request( $intent_type, 'update', $intent->id, $intent_args );
			$intent = edds_api_request( $intent_type, 'retrieve', $intent->id );
		} else {
			$intent = edds_api_request( $intent_type, 'create', $intent_args );
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
		do_action( 'edds_process_purchase_form', $purchase_data, $intent );

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

	} catch ( \Stripe\Exception\ApiErrorException $e ) {
		$error = $e->getJsonBody()['error'];

		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error 002', 'easy-digital-downloads' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Order data: %s', 'easy-digital-downloads' ),
				wp_json_encode( $error )
			),
			0
		);

		return wp_send_json_error(
			array(
				'message' => esc_html(
					edds_get_localized_error_message( $error['code'], $error['message'] )
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
add_action( 'edd_gateway_stripe', 'edds_process_purchase_form' );

/**
 * Create an \EDD\Orders\Order.
 *
 * @since 2.9.0
 */
function edds_create_and_complete_order() {
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
				'Rate limit reached during payment creation.'
			);
		}

		// This must happen in the Checkout flow, so validate the Checkout nonce.
		if ( false === edds_verify() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1002: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during payment creation.'
			);
		}

		$intent_id = isset( $_REQUEST['intent_id'] ) ? $_REQUEST['intent_id'] : '';

		if ( ! isset( $intent_id ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1003: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve Intent data during payment creation.'
			);
		}

		$purchase_data = edd_get_purchase_session();

		if ( false === $purchase_data ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1004: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve purchase data during payment creation.'
			);
		}

		// Ensure Intent has transitioned to the correct status.
		if ( 'SetupIntent' === $_REQUEST['intent_type'] ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		}

		if ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1005: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Invalid Intent status ' . $intent->status . ' during order creation.'
			);
		}

		$order_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => edd_get_currency(),
			'downloads'    => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info'    => $purchase_data['user_info'],
			'status'       => 'pending',
			'gateway'      => 'stripe',
		);

		// Ensure $_COOKIE is available without a new HTTP request.
		if ( class_exists( 'EDD_Auto_Register' ) ) {
			add_action( 'set_logged_in_cookie', 'edds_set_logged_in_cookie_global' );
			add_filter( 'edd_get_option_edd_auto_register_complete_orders_only', '__return_false' );
		}

		// Record the pending order.
		$order_id = edd_build_order( $order_data );

		if ( false === $order_id ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Error 1006: An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to insert order record.'
			);
		}

		// Now get the newly created order.
		$order = edd_get_order( $order_id );

		// Retrieve the relevant Intent.
		if ( 'setup_intent' === $intent->object ) {
			$order_transaction_id = false;

			$intent = edds_api_request(
				'SetupIntent',
				'update',
				$intent->id,
				array(
					'metadata' => array(
						'edd_payment_id' => $order->id,
					),
				)
			);

			edd_add_note(
				array(
					'object_id'   => $order->id,
					'content'     => 'Payment Elements - Stripe SetupIntent ID: ' . $intent->id,
					'user_id'     => is_admin() ? get_current_user_id() : 0,
					'object_type' => 'order',
				)
			);

			edd_add_order_meta(
				$order->id,
				'_edds_stripe_setup_intent_id',
				$intent->id
			);
		} else {
			$intent_update_args = array(
				'metadata' => array(
					'edd_payment_id' => $order->id,
				),
			);

			$intent = edds_api_request(
				'PaymentIntent',
				'update',
				$intent->id,
				$intent_update_args
			);

			edd_add_note(
				array(
					'object_id'   => $order->id,
					'content'     => 'Payment Elements - Stripe PaymentIntent ID: ' . $intent->id,
					'user_id'     => is_admin() ? get_current_user_id() : 0,
					'object_type' => 'order',
				)
			);

			edd_add_order_meta(
				$order->id,
				'_edds_stripe_payment_intent_id',
				$intent->id
			);

			// Use Intent ID for temporary transaction ID.
			// It will be updated when a charge is available.
			$order_transaction_id = edd_add_order_transaction(
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

		// Retrieves or creates a Stripe Customer.
		edd_update_order_meta( $order->id, '_edds_stripe_customer_id', $intent->customer );

		edd_add_note(
			array(
				'object_id'   => $order->id,
				'content'     => 'Stripe Customer ID: ' . $intent->customer,
				'user_id'     => is_admin() ? get_current_user_id() : 0,
				'object_type' => 'order',
			)
		);

		// The returned Intent charges might contain a mandate ID, so let's save that and make a note.
		if ( ! empty( $intent->charges->data ) ) {
			foreach ( $intent->charges->data as $charge ) {
				if ( empty( $charge->payment_method_details->card->mandate ) ) {
					continue;
				}

				$mandate_id = $charge->payment_method_details->card->mandate;
				edd_update_order_meta( $order->id, '_edds_stripe_mandate', $mandate_id );

				edd_add_note(
					array(
						'object_id'   => $order->id,
						'content'     => 'Stripe Mandate ID: ' . $mandate_id,
						'user_id'     => is_admin() ? get_current_user_id() : 0,
						'object_type' => 'order',
					)
				);
			}
		}

		// Attach the \Stripe\Customer ID to the \EDD_Customer meta if one exists.
		$edd_customer = new EDD_Customer( $purchase_data['user_email'] );

		if ( $edd_customer->id > 0 ) {
			$edd_customer->update_meta( edd_stripe_get_customer_key(), $intent->customer );
		}

		if ( class_exists( 'EDD_Auto_Register' ) ) {
			remove_action( 'set_logged_in_cookie', 'edds_set_logged_in_cookie_global' );
		}

		if ( has_action( 'edds_payment_created' ) ) {
			// Load up an EDD Payment record here, in the event there is something hooking into it.
			$payment = new EDD_Payment( $order->id );

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

		// Now we need to mark the order as complete.
		$final_status = edds_is_preapprove_enabled() ? 'preapproval' : 'complete';
		$updated      = edd_update_order_status( $order->id, $final_status );

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

				if ( ! empty( $order_transaction_id ) ) {
					edd_update_order_transaction(
						$order_transaction_id,
						array(
							'transaction_id' => sanitize_text_field( $charge_id ),
							'status'         => 'complete',
						)
					);
				}
			}

			if ( has_action( 'edds_payment_complete' ) ) {
				// Load up an EDD Payment record here, in the event there is something hooking into it.
				$payment = new EDD_Payment( $order->id );

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
				'Unable to insert order record.'
			);
		}

		return wp_send_json_success(
			array(
				'intent' => $intent,
				'order'  => $order,
				// Send back a new nonce because the user might have logged in via Auto Register.
				'nonce'  => wp_create_nonce( 'edd-process-checkout' ),
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
add_action( 'wp_ajax_edds_create_and_complete_order', 'edds_create_and_complete_order' );
add_action( 'wp_ajax_nopriv_edds_create_and_complete_order', 'edds_create_and_complete_order' );

/**
 * Uptick the rate limit card error count when a failure happens.
 *
 * @since 2.9.0
 */
function edds_payment_elements_rate_limit_tick() {
	// Increase the card error count.
	edd_stripe()->rate_limiting->increment_card_error_count();

	wp_send_json_success(
		array(
			'is_at_limit' => edd_stripe()->rate_limiting->has_hit_card_error_limit(),
			'message'     => edd_stripe()->rate_limiting->get_rate_limit_error_message(),
		)
	);
}
add_action( 'wp_ajax_edds_payment_elements_rate_limit_tick', 'edds_payment_elements_rate_limit_tick' );
add_action( 'wp_ajax_nopriv_edds_payment_elements_rate_limit_tick', 'edds_payment_elements_rate_limit_tick' );

/**
 * Generates a description based on the cart details.
 *
 * @param array $cart_details {
 *
 * }
 * @return string
 */
function edds_get_payment_description( $cart_details ) {
	$purchase_summary = '';

	if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
		foreach ( $cart_details as $item ) {
			$purchase_summary .= $item['name'];
			$price_id          = isset( $item['item_number']['options']['price_id'] )
				? absint( $item['item_number']['options']['price_id'] )
				: false;

			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}

			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );
	}

	// Stripe has a maximum of 999 characters in the charge description.
	$purchase_summary = substr( $purchase_summary, 0, 1000 );

	return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
}
