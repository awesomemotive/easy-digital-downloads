<?php
/**
 * Card actions.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Process the card update actions from the manage card form.
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_process_card_update() {
	$enabled = edd_stripe_existing_cards_enabled();

	// Feature not enabled.
	if ( ! $enabled ) {
		wp_send_json_error(
			array(
				'message' => __( 'This feature is not available at this time.', 'easy-digital-downloads' ),
			)
		);
	}

	// Source can't be found.
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';

	if ( empty( $payment_method ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Nonce failed.
	if ( ! edds_verify( 'nonce', $payment_method . '_update' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}
	$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );
	if ( empty( $stripe_customer_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	try {
		$card_args   = array();
		$card_fields = array(
			'address_city',
			'address_country',
			'address_line1',
			'address_line2',
			'address_zip',
			'address_state',
			'exp_month',
			'exp_year',
		);

		foreach ( $card_fields as $card_field ) {
			$card_args[ $card_field ] = ( isset( $_POST[ $card_field ] ) && '' !== $_POST[ $card_field ] )
				? sanitize_text_field( $_POST[ $card_field ] )
				: null;
		}

		// Update a PaymentMethod.
		if ( 'pm_' === substr( $payment_method, 0, 3 ) ) {
			$address_args = array(
				'city'        => $card_args['address_city'],
				'country'     => $card_args['address_country'],
				'line1'       => $card_args['address_line1'],
				'line2'       => $card_args['address_line2'],
				'postal_code' => $card_args['address_zip'],
				'state'       => $card_args['address_state'],
			);

			edds_api_request(
				'PaymentMethod',
				'update',
				$payment_method,
				array(
					'billing_details' => array(
						'address' => $address_args,
					),
					'card'            => array(
						'exp_month' => $card_args['exp_month'],
						'exp_year'  => $card_args['exp_year'],
					),
				)
			);

			// Update a legacy Card.
		} else {
			edds_api_request( 'Customer', 'updateSource', $stripe_customer_id, $payment_method, $card_args );
		}

		// Check if customer has default card.
		$existing_cards         = edd_stripe_get_existing_cards( get_current_user_id() );
		$default_payment_method = edds_customer_get_default_payment_method( get_current_user_id(), $existing_cards );
		// If there is no default card, make updated card default.
		if ( null === $default_payment_method ) {
			edds_customer_set_default_payment_method( $stripe_customer_id, current( $existing_cards )['source']->id );
		}

		wp_send_json_success(
			array(
				'message' => esc_html__( 'Card successfully updated.', 'easy-digital-downloads' ),
			)
		);
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_edds_update_payment_method', 'edd_stripe_process_card_update' );

/**
 * Process the set default card action from the manage card form.
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_process_card_default() {
	$enabled = edd_stripe_existing_cards_enabled();

	// Feature not enabled.
	if ( ! $enabled ) {
		wp_send_json_error(
			array(
				'message' => __( 'This feature is not available at this time.', 'easy-digital-downloads' ),
			)
		);
	}

	// Source can't be found.
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';

	if ( empty( $payment_method ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Nonce failed.
	if ( ! edds_verify( 'nonce', $payment_method . '_update' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Customer can't be found.
	$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );

	if ( empty( $stripe_customer_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	try {
		edds_customer_set_default_payment_method( $stripe_customer_id, $payment_method );

		wp_send_json_success(
			array(
				'message' => esc_html__( 'Card successfully set as default.', 'easy-digital-downloads' ),
			)
		);
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_edds_set_payment_method_default', 'edd_stripe_process_card_default' );

/**
 * Process the delete card action from the manage card form.
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_process_card_delete() {
	$enabled = edd_stripe_existing_cards_enabled();

	// Feature not enabled.
	if ( ! $enabled ) {
		wp_send_json_error(
			array(
				'message' => __( 'This feature is not available at this time.', 'easy-digital-downloads' ),
			)
		);
	}

	// Source can't be found.
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';

	if ( empty( $payment_method ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Nonce failed.
	if ( ! edds_verify( 'nonce', $payment_method . '_update' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Customer can't be found.
	$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );

	if ( empty( $stripe_customer_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error updating card.', 'easy-digital-downloads' ),
			)
		);
	}

	// Removal is disabled for this card.
	$should_remove = apply_filters(
		'edd_stripe_should_remove_card',
		array(
			'remove'  => true,
			'message' => '',
		),
		$payment_method,
		$stripe_customer_id
	);

	if ( ! $should_remove['remove'] ) {
		wp_send_json_error(
			array(
				'message' => esc_html__( 'This feature is not available at this time.', 'easy-digital-downloads' ),
			)
		);
	}

	try {
		// Detach a PaymentMethod.
		if ( 'pm_' === substr( $payment_method, 0, 3 ) ) {
			$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $payment_method );
			$payment_method->detach();

			// Delete a Card.
		} else {
			edds_api_request( 'Customer', 'deleteSource', $stripe_customer_id, $payment_method );
		}

		// Retrieve saved cards before checking for default.
		$existing_cards         = edd_stripe_get_existing_cards( get_current_user_id() );
		$default_payment_method = edds_customer_get_default_payment_method( get_current_user_id(), $existing_cards );

		// If there is no default card, make updated card default.
		if ( null === $default_payment_method && ! empty( $existing_cards ) ) {
			edds_customer_set_default_payment_method( $stripe_customer_id, current( $existing_cards )['source']->id );
		}

		wp_send_json_success(
			array(
				'message' => esc_html__( 'Card successfully removed.', 'easy-digital-downloads' ),
			)
		);
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_edds_delete_payment_method', 'edd_stripe_process_card_delete' );

/**
 * Handles adding a new PaymentMethod (via AJAX).
 *
 * @since 2.6
 * @return void
 */
function edds_add_payment_method() {
	$enabled = edd_stripe_existing_cards_enabled();

	// Feature not enabled.
	if ( ! $enabled ) {
		wp_send_json_error(
			array(
				'message' => __( 'This feature is not available at this time.', 'easy-digital-downloads' ),
			)
		);
	}

	if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		// Increase the card error count.
		edd_stripe()->rate_limiting->increment_card_error_count();

		wp_send_json_error(
			array(
				'message' => __( 'Unable to update your account at this time, please try again later', 'easy-digital-downloads' ),
			)
		);
	}

	// PaymentMethod can't be found.
	$payment_method_id = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : false;

	if ( ! $payment_method_id ) {
		wp_send_json_error(
			array(
				'message' => __( 'Missing card ID.', 'easy-digital-downloads' ),
			)
		);
	}

	// Nonce failed.
	if ( ! edds_verify( 'nonce', 'edd-stripe-add-card' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Error adding card.', 'easy-digital-downloads' ),
			)
		);
	}

	$edd_customer = new \EDD_Customer( get_current_user_id(), true );
	if ( 0 === $edd_customer->id ) {
		wp_send_json_error(
			array(
				'message' => __(
					'Unable to retrieve customer.',
					'easy-digital-downloads'
				),
			)
		);
	}

	$stripe_customer_id = edds_get_stripe_customer_id( get_current_user_id() );

	$customer_name = '';
	if ( ! empty( $edd_customer->name ) ) {
		$customer_name = $edd_customer->name;
	}

	$stripe_customer = edds_get_stripe_customer(
		$stripe_customer_id,
		array(
			'email'       => $edd_customer->email,
			'description' => $edd_customer->email,
			'name'        => $customer_name,
		)
	);

	if ( false === $stripe_customer ) {
		wp_send_json_error(
			array(
				'message' => __(
					'Unable to create customer in Stripe.',
					'easy-digital-downloads'
				),
			)
		);
	}

	// Ensure the EDD Customer is has a link to the most up to date Stripe Customer ID.
	$edd_customer->update_meta( edd_stripe_get_customer_key(), $stripe_customer->id );

	try {
		$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
		$payment_method->attach(
			array(
				'customer' => $stripe_customer->id,
			)
		);
		// Check if customer has default card.
		$existing_cards         = edd_stripe_get_existing_cards( get_current_user_id() );
		$default_payment_method = edds_customer_get_default_payment_method( get_current_user_id(), $existing_cards );
		// If there is no default card, make updated card default.
		if ( null === $default_payment_method ) {
			edds_customer_set_default_payment_method( $stripe_customer->id, current( $existing_cards )['source']->id );
		}

		wp_send_json_success(
			array(
				'message' => esc_html__( 'Card successfully added.', 'easy-digital-downloads' ),
			)
		);
	} catch ( \Exception $e ) {
		// Increase the card error count.
		edd_stripe()->rate_limiting->increment_card_error_count();

		wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_edds_add_payment_method', 'edds_add_payment_method' );

/**
 * Sets default payment method if none.
 *
 * @since 2.8
 * @param string $stripe_customer_id Stripe Customer ID. Usually starts with cus_ .
 * @param string $payment_method_id Stripe Payment ID. Usually starts with pm_ .
 * @return \Stripe\Customer $customer Stripe Customer.
 */
function edds_customer_set_default_payment_method( $stripe_customer_id, $payment_method_id ) {
	$customer = edds_api_request(
		'Customer',
		'update',
		$stripe_customer_id,
		array(
			'invoice_settings' => array(
				'default_payment_method' => $payment_method_id,
			),
		)
	);
	return $customer;
}

/**
 * Checks if customer has default payment method.
 *
 * @since 2.8
 * @param int   $user_id WordPress user ID.
 * @param array $payment_methods Array of payment methods for user, default = false will fetch payment methods.
 * @return null|string Payment Method ID if found, else null
 */
function edds_customer_get_default_payment_method( $user_id, $payment_methods = false ) {
	// Retrieve saved cards before checking for default.
	if ( false === $payment_methods ) {
		$payment_methods = edd_stripe_get_existing_cards( $user_id );
	}
	$default_payment_method = null;
	if ( count( $payment_methods ) >= 1 ) {
		// Loop through existing cards for default.
		foreach ( $payment_methods as $card ) {
			if ( true === $card['default'] ) {
				$default_payment_method = $card['source']->id;
				break;
			}
		}
	}
	return $default_payment_method;
}

/**
 * Checks if customer Stripe Customer object exists.
 *
 * @since 2.8
 * @param string $stripe_customer_id Stripe Customer ID. Usually starts with cus_ .
 * @param array  $customer_args {
 *     Arguments to create a Stripe Customer.
 *
 *     @link https://stripe.com/docs/api/customers/create
 * }
 * @return \Stripe\Customer|false $customer Stripe Customer if found or false on error.
 */
function edds_get_stripe_customer( $stripe_customer_id, $customer_args ) {
	$customer = false;
	if ( ! empty( $stripe_customer_id ) ) {
		try {
			$customer = edds_api_request( 'Customer', 'retrieve', $stripe_customer_id );
			if ( isset( $customer->deleted ) && $customer->deleted ) { // If customer was deleted in Stripe, try to create a new one.
				$customer = edds_create_stripe_customer( $customer_args );
			}
		} catch ( \Stripe\Error\Base $e ) {
			$error_code = $e->getStripeCode();
			if ( 'resource_missing' === $error_code ) { // If Stripe returns an error of 'resource_missing', try to create a new Stripe Customer.
				try {
					$customer = edds_create_stripe_customer( $customer_args );
				} catch ( \Exception $e ) {
					// No further actions to take if something causes error.
				}
			}
		}
	} else {
		try {
			$customer = edds_create_stripe_customer( $customer_args );
		} catch ( \Exception $e ) {
			// No further actions to take if something causes error.
		}
	}
	return $customer;
}

/**
 * Creates a new Stripe Customer
 *
 * @since 2.8
 * @param array $customer_args {
 *     Arguments to create a Stripe Customer.
 *
 *     @link https://stripe.com/docs/api/customers/create
 * }
 * @return \Stripe\Customer|false $customer Stripe Customer if one is created or false on error.
 */
function edds_create_stripe_customer( $customer_args = array() ) {
	/**
	 * Filters the arguments used to create a Customer in Stripe.
	 *
	 * @since unknown
	 *
	 * @param array $customer_args {
	 *   Arguments to create a Stripe Customer.
	 *
	 *   @link https://stripe.com/docs/api/customers/create
	 * }
	 * @param array $purchase_data {
	 *   Cart purchase data if in the checkout context. Empty otherwise.
	 * }
	 */
	$customer_args = apply_filters( 'edds_create_customer_args', $customer_args, array() );
	if ( empty( $customer_args['email'] ) || ! is_email( $customer_args['email'] ) ) {
		return false;
	}
	if ( empty( $customer_args['description'] ) ) {
		$customer_args['description'] = $customer_args['email'];
	}

	try {
		$customer = edds_api_request( 'Customer', 'create', $customer_args );
	} catch ( \Exception $e ) {
		$customer = false;
	}

	return $customer;

}
