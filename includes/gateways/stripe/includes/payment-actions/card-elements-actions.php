<?php
/**
 * Payment actions.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

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
			$options = isset( $download['options'] ) ? $download['options'] : array();
			$options['quantity'] = isset( $download['quantity'] ) ? $download['quantity'] : 1;

			edd_add_to_cart( $download['id'], $options );
		}

		edd_unset_error( 'edd-straight-to-gateway-error' );
		edd_send_back_to_checkout();

		return;
	}

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'We are unable to process your payment at this time, please try again later or contact support.',
					'easy-digital-downloads'
				)
			);
		}

		/**
		 * Allows processing before an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array $purchase_data Purchase data.
		 */
		do_action( 'edds_pre_process_purchase_form', $purchase_data );

		$payment_method_id     = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : false;
		$payment_method_exists = isset( $_POST['payment_method_exists'] ) ? 'true' == $_POST['payment_method_exists'] : false;

		if ( ! $payment_method_id ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to locate payment method. Please try again with a new payment method.',
					'easy-digital-downloads'
				)
			);
		}

		// Ensure Payment Method is still valid.
		$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
		$card = isset( $payment_method->card ) ? $payment_method->card : null;

		// ...block prepaid cards if option is not enabled.
		if (
			$card &&
			'prepaid' === $card->funding &&
			false === (bool) edd_get_option( 'stripe_allow_prepaid' )
		) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Prepaid cards are not a valid payment method. Please try again with a new payment method.',
					'easy-digital-downloads'
				)
			);
		}

		if ( edds_is_zero_decimal_currency() ) {
			$amount = $purchase_data['price'];
		} else {
			$amount = round( $purchase_data['price'] * 100, 0 );
		}

		// Retrieves or creates a Stripe Customer.
		$customer = edds_checkout_setup_customer( $purchase_data );

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

		// Flag if this is the first card being attached to the Customer.
		$existing_payment_methods = edd_stripe_get_existing_cards( $purchase_data['user_info']['id'] );
		$is_first_payment_method  = empty( $existing_payment_methods );

		$address_info = $purchase_data['user_info']['address'];

		// Update PaymentMethod details if necessary.
		if ( $payment_method_exists && ! empty( $_POST['edd_stripe_update_billing_address'] ) ) {
			$billing_address = array();

			foreach ( $address_info as $key => $value ) {
				// Adjusts address data keys to work with PaymentMethods.
				switch( $key ) {
					case 'zip':
						$key = 'postal_code';
						break;
				}

				$billing_address[ $key ] = ! empty( $value ) ? sanitize_text_field( $value ) : '';
			}

			edds_api_request( 'PaymentMethod', 'update', $payment_method_id, array(
				'billing_details' => array(
					'address' => $billing_address,
				),
			) );
		}

		// Create a list of {$download_id}_{$price_id}
		$payment_items = array();

		foreach ( $purchase_data['cart_details'] as $item ) {
			$price_id = isset( $item['item_number']['options']['price_id'] )
				? $item['item_number']['options']['price_id']
				: null;

			$payment_items[] = $item['id'] . ( ! empty( $price_id ) ? ( '_' . $price_id ) : '' );
		}

		// Shared Intent arguments.
		$intent_args = array(
			'confirm'        => true,
			'payment_method' => $payment_method_id,
			'customer'       => $customer->id,
			'metadata'       => array(
				'email'                => esc_html( $purchase_data['user_info']['email'] ),
				'edd_payment_subtotal' => esc_html( $purchase_data['subtotal'] ),
				'edd_payment_discount' => esc_html( $purchase_data['discount'] ),
				'edd_payment_tax'      => esc_html( $purchase_data['tax'] ),
				'edd_payment_tax_rate' => esc_html( $purchase_data['tax_rate'] ),
				'edd_payment_fees'     => esc_html( edd_get_cart_fee_total() ),
				'edd_payment_total'    => esc_html( $purchase_data['price'] ),
				'edd_payment_items'    => esc_html( implode( ', ', $payment_items ) ),
			),
		);

		// Attempt to map existing charge arguments to PaymentIntents.
		if ( has_filter( 'edds_create_charge_args' ) ) {
			/**
			 * @deprecated 2.7.0 In favor of `edds_create_payment_intent_args`.
			 *
			 * @param array $intent_args
			 */
			$old_charge_args = apply_filters_deprecated(
				'edds_create_charge_args',
				array(
					$intent_args,
				),
				'2.7.0',
				'edds_create_payment_intent_args'
			);

			// Grab a few compatible arguments from the old charges filter.
			$compatible_keys = array(
				'amount',
				'currency',
				'customer',
				'description',
				'metadata',
				'application_fee',
			);

			foreach ( $compatible_keys as $compatible_key ) {
				if ( ! isset( $old_charge_args[ $compatible_key ] ) ) {
					continue;
				}

				$value = $old_charge_args[ $compatible_key ];

				switch ( $compatible_key ) {
					case 'application_fee':
						break;

					default:
						// If a legacy value is an array merge it with the existing values to avoid overriding completely.
						$intent_args[ $compatible_key ] = is_array( $value ) && is_array( $intent_args[ $compatible_key ] )
							? wp_parse_args( $value, $intent_args[ $compatible_key ] )
							: $value;
				}

				edd_debug_log( __( 'Charges are no longer directly created in Stripe. Please read the following for more information: https://easydigitaldownloads.com/development/', 'easy-digital-downloads' ), true );
			}
		}

		// Create a SetupIntent for a non-payment carts.
		if ( edds_is_preapprove_enabled() || 0 === $amount ) {
			$intent_args = array_merge(
				array(
					'usage'       => 'off_session',
					'description' => edds_get_payment_description( $purchase_data['cart_details'] ),
				),
				$intent_args
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
			if ( edd_stripe()->application_fee->has_application_fee() ) {
				$intent_args['application_fee_amount'] = edd_stripe()->application_fee->get_application_fee_amount( $amount );
			}

			$intent = edds_api_request( 'SetupIntent', 'create', $intent_args );

			// Manually attach PaymentMethod to the Customer.
			if ( ! $payment_method_exists && edd_stripe_existing_cards_enabled() ) {
				$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
				$payment_method->attach( array(
					'customer' => $customer->id,
				) );
			}

		// Create a PaymentIntent for an immediate charge.
		} else {
			$purchase_summary     = edds_get_payment_description( $purchase_data['cart_details'] );
			$statement_descriptor = edds_get_statement_descriptor();

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = substr( $purchase_summary, 0, 22 );
			}

			$statement_descriptor = apply_filters( 'edds_statement_descriptor', $statement_descriptor, $purchase_data );
			$statement_descriptor = edds_sanitize_statement_descriptor( $statement_descriptor );

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = null;
			} elseif ( is_numeric( $statement_descriptor ) ) {
				$statement_descriptor = edd_get_label_singular() . ' ' . $statement_descriptor;
			}

			$intent_args = array_merge(
				array(
					'amount'                 => $amount,
					'currency'               => edd_get_currency(),
					'setup_future_usage'     => 'off_session',
					'confirmation_method'    => 'manual',
					'save_payment_method'    => true,
					'description'            => $purchase_summary,
					'statement_descriptor'   => $statement_descriptor,
				),
				$intent_args
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
			$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, $purchase_data );

			if ( edd_stripe()->application_fee->has_application_fee() ) {
				$intent_args['application_fee_amount'] = edd_stripe()->application_fee->get_application_fee_amount( $amount );
			}

			$intent = edds_api_request( 'PaymentIntent', 'create', $intent_args );
		}

		// Set the default payment method when attaching the first one.
		if ( $is_first_payment_method ) {
			edds_api_request( 'Customer', 'update', $customer->id, array(
				'invoice_settings' => array(
					'default_payment_method' => $payment_method_id,
				),
			) );
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

		return wp_send_json_success( array(
			'intent' => $intent,
			// Send back a new nonce because the user might have logged in.
			'nonce'  => wp_create_nonce( 'edd-process-checkout' ),
		) );

	// Catch card-specific errors to handle rate limiting.
	} catch ( \Stripe\Exception\CardException $e ) {
		// Increase the card error count.
		edd_stripe()->rate_limiting->increment_card_error_count();

		$error = $e->getJsonBody()['error'];

		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Payment data: %s', 'easy-digital-downloads' ),
				wp_json_encode( $error )
			),
			0
		);

		$decline_code = ! empty( $error['decline_code'] ) ? $error['decline_code'] : false;

		return wp_send_json_error( array(
			'message' => esc_html(
				edds_get_localized_error_message( $error['code'], $error['message'], $decline_code )
			),
		) );

	// Catch Stripe-specific errors.
	} catch ( \Stripe\Exception\ApiErrorException $e ) {
		$error = $e->getJsonBody()['error'];

		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Payment data: %s', 'easy-digital-downloads' ),
				wp_json_encode( $error )
			),
			0
		);

		return wp_send_json_error( array(
			'message' => esc_html(
				edds_get_localized_error_message( $error['code'], $error['message'] )
			),
		) );

	// Catch gateway processing errors.
	} catch ( \EDD_Stripe_Gateway_Exception $e ) {
		if ( true === $e->hasLogMessage() ) {
			edd_record_gateway_error(
				esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( \Exception $e ) {

		// Safety precaution in case the payment form is submitted directly.
		// Redirects back to the Checkout.
		if ( isset( $_POST['edd_email'] ) && ! isset( $_POST['payment_method_id'] ) ) {
			edd_set_error( $e->getCode(), $e->getMessage() );
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['gateway'] );
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'edd_gateway_stripe', 'edds_process_purchase_form' );

/**
 * Retrieves an Intent.
 *
 * @since 2.7.0
 */
function edds_get_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? sanitize_text_field( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during Intent retrieval.'
			);
		}

		if ( false === edds_verify_payment_form_nonce() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during Intent retrieval.'
			);
		}

		if ( 'setup_intent' === $intent_type ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		}

		return wp_send_json_success( array(
			'intent' => $intent,
		) );
	// Catch gateway processing errors.
	} catch ( \EDD_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		edd_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			edd_record_gateway_error(
				esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( \Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_get_intent', 'edds_get_intent' );
add_action( 'wp_ajax_nopriv_edds_get_intent', 'edds_get_intent' );

/**
 * Confirms a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_confirm_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id   = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? sanitize_text_field( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during Intent confirmation.'
			);
		}

		if ( false === edds_verify_payment_form_nonce() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during Intent confirmation.'
			);
		}

		// SetupIntent was used if the cart total is $0.
		if ( 'setup_intent' === $intent_type ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
			$intent->confirm();
		}

		/**
		 * Allows further processing after an Intent is confirmed.
		 * Runs for all calls to confirm(), regardless of action needed.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Stripe intent.
		 */
		do_action( 'edds_confirm_payment_intent', $intent );

		return wp_send_json_success( array(
			'intent' => $intent,
		) );

	// Catch gateway processing errors.
	} catch ( \EDD_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		edd_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			edd_record_gateway_error(
				esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_confirm_intent', 'edds_confirm_intent' );
add_action( 'wp_ajax_nopriv_edds_confirm_intent', 'edds_confirm_intent' );

/**
 * Capture a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_capture_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during Intent capture.'
			);
		}

		// Verify the checkout session only.
		if ( false === edds_verify() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during Intent capture.'
			);
		}

		$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );

		/**
		 * Allows processing before a PaymentIntent is captured.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
		 */
		do_action( 'edds_capture_payment_intent', $intent );

		// Capture capturable amount if nothing else has captured the intent.
		if ( 'requires_capture' === $intent->status ) {
			$intent->capture( array(
				'amount_to_capture' => $intent->amount_capturable,
			) );
		}

		return wp_send_json_success( array(
			'intent' => $intent,
		) );

	// Catch gateway processing errors.
	} catch ( \EDD_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		edd_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			edd_record_gateway_error(
				esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_capture_intent', 'edds_capture_intent' );
add_action( 'wp_ajax_nopriv_edds_capture_intent', 'edds_capture_intent' );

/**
 * Update a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_update_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during Intent update.'
			);
		}

		if ( false === edds_verify_payment_form_nonce() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during Intent update.'
			);
		}

		$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );

		/**
		 * Allows processing before a PaymentIntent is updated.
		 *
		 * @since 2.7.0
		 *
		 * @param string $intent_id Stripe PaymentIntent ID.
		 */
		do_action( 'edds_update_payment_intent', $intent_id );

		$intent_args           = array();
		$intent_args_whitelist = array(
			'payment_method',
		);

		foreach ( $intent_args_whitelist as $intent_arg ) {
			if ( isset( $_POST[ $intent_arg ] ) ) {
				$intent_args[ $intent_arg ] = sanitize_text_field( $_POST[ $intent_arg ] );
			}
		}

		$intent = edds_api_request( 'PaymentIntent', 'update', $intent_id, $intent_args );

		return wp_send_json_success( array(
			'intent' => $intent,
		) );

	// Catch gateway processing errors.
	} catch ( \EDD_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		edd_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			edd_record_gateway_error(
				esc_html__( 'Stripe Error', 'easy-digital-downloads' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_update_intent', 'edds_update_intent' );
add_action( 'wp_ajax_nopriv_edds_update_intent', 'edds_update_intent' );

/**
 * Create an \EDD_Payment.
 *
 * @since 2.7.0
 */
function edds_create_payment() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	// Simulate being in an `edd_process_purchase_form()` request.
	_edds_fake_process_purchase_step();

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during payment creation.'
			);
		}

		// This must happen in the Checkout flow, so validate the Checkout nonce.
		if ( false === edds_verify() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during payment creation.'
			);
		}

		$intent = isset( $_REQUEST['intent'] ) ? $_REQUEST['intent'] : array();

		if ( ! isset( $intent['id'] ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve Intent data during payment creation.'
			);
		}

		$purchase_data = edd_get_purchase_session();

		if ( false === $purchase_data ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve purchase data during payment creation.'
			);
		}

		// Ensure Intent has transitioned to the correct status.
		if ( 'setup_intent' === $intent['object'] ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent['id'] );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent['id'] );
		}

		if ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Invalid Intent status ' . $intent->status . ' during payment creation.'
			);
		}

		$payment_data = array(
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

		// Record the pending payment.
		$payment_id = edd_insert_payment( $payment_data );

		if ( false === $payment_id ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to insert payment record.'
			);
		}

		// Retrieve created payment.
		$payment = edd_get_payment( $payment_id );

		// Retrieve the relevant Intent.
		if ( 'setup_intent' === $intent->object ) {
			$intent = edds_api_request( 'SetupIntent', 'update', $intent->id, array(
				'metadata' => array(
					'edd_payment_id' => $payment_id,
				),
			) );

			$payment->add_note( 'Stripe SetupIntent ID: ' . $intent->id );
			$payment->update_meta( '_edds_stripe_setup_intent_id', $intent->id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'update', $intent->id, array(
				'metadata' => array(
					'edd_payment_id' => $payment_id,
				),
		  ) );

			$payment->add_note( 'Stripe PaymentIntent ID: ' . $intent->id );
			$payment->update_meta( '_edds_stripe_payment_intent_id', $intent->id );
		}

		// Use Intent ID for temporary transaction ID.
		// It will be updated when a charge is available.
		$payment->transaction_id = $intent->id;

		// Retrieves or creates a Stripe Customer.
		$payment->update_meta( '_edds_stripe_customer_id', $intent->customer );
		$payment->add_note( 'Stripe Customer ID: ' . $intent->customer );

		// Attach the \Stripe\Customer ID to the \EDD_Customer meta if one exists.
		$edd_customer = new EDD_Customer( $purchase_data['user_email'] );

		if ( $edd_customer->id > 0 ) {
			$edd_customer->update_meta( edd_stripe_get_customer_key(), $intent->customer );
		}

		$saved = $payment->save();

		if ( class_exists( 'EDD_Auto_Register' ) ) {
			remove_action( 'set_logged_in_cookie', 'edds_set_logged_in_cookie_global' );
		}

		if ( true === $saved ) {
			/**
			 * Allows further processing after a payment is created.
			 *
			 * @since 2.7.0
			 *
			 * @param \EDD_Payment                              $payment EDD Payment.
			 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
			 */
			do_action( 'edds_payment_created', $payment, $intent );

			return wp_send_json_success( array(
				'intent'  => $intent,
				'payment' => $payment,
				// Send back a new nonce because the user might have logged in via Auto Register.
				'nonce'  => wp_create_nonce( 'edd-process-checkout' ),
			) );
		} else {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to create payment.',
					'easy-digital-downloads'
				),
				'Unable to save payment record.'
			);
		}

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

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( \Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_create_payment', 'edds_create_payment' );
add_action( 'wp_ajax_nopriv_edds_create_payment', 'edds_create_payment' );

/**
 * Completes an \EDD_Payment (via AJAX)
 *
 * @since 2.7.0
 */
function edds_complete_payment() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent = isset( $_REQUEST['intent'] ) ? $_REQUEST['intent'] : array();

	try {
		if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Rate limit reached during payment completion.'
			);
		}

		// Verify the checkout session only.
		if ( false === edds_verify() ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Nonce verification failed during payment completion.'
			);
		}

		if ( ! isset( $intent['id'] ) ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve Intent during payment completion.'
			);
		}

		// Retrieve the intent from Stripe again to verify linked payment.
		if ( 'setup_intent' === $intent['object'] ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent['id'] );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent['id'] );
		}

		$payment = edd_get_payment( $intent->metadata->edd_payment_id );

		if ( ! $payment ) {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to retrieve pending payment record.'
			);
		}

		if ( 'setup_intent' !== $intent['object'] ) {
			$charge_id = sanitize_text_field( current( $intent['charges']['data'] )['id'] );

			$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
			$payment->transaction_id = sanitize_text_field( $charge_id );
		}

		// Mark payment as Preapproved.
		if ( edds_is_preapprove_enabled() ) {
			$payment->status = 'preapproval';

		// Complete payment and transition the Transaction ID to the actual Charge ID.
		} else {
			$payment->status = 'publish';
		}

		if ( $payment->save() ) {
			/**
			 * Allows further processing after a payment is completed.
			 *
			 * Sends back just the Intent ID to avoid needing always retrieve
			 * the intent in this step, which has been transformed via JSON,
			 * and is no longer a \Stripe\PaymentIntent
			 *
			 * @since 2.7.0
			 *
			 * @param \EDD_Payment $payment   EDD Payment.
			 * @param string       $intent_id Stripe Intent ID.
			 */
			do_action( 'edds_payment_complete', $payment, $intent['id'] );

			// Empty cart.
			edd_empty_cart();

			return wp_send_json_success( array(
				'payment' => $payment,
				'intent'  => $intent,
			) );
		} else {
			throw new \EDD_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'easy-digital-downloads'
				),
				'Unable to update payment record to completion.'
			);
		}

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

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );

	// Catch any remaining error.
	} catch( \Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_complete_payment', 'edds_complete_payment' );
add_action( 'wp_ajax_nopriv_edds_complete_payment', 'edds_complete_payment' );

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

	if( is_array( $cart_details ) && ! empty( $cart_details ) ) {
		foreach( $cart_details as $item ) {
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

	// Stripe has a maximum of 999 characters in the charge description
	$purchase_summary = substr( $purchase_summary, 0, 1000 );

	return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
}
