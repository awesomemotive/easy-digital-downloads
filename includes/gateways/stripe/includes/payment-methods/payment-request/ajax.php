<?php
/**
 * Payment Request Button: AJAX
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Starts the Checkout process for a Payment Request.
 *
 * This needs to be used instead of `_edds_process_purchase_form()` so
 * Checkout form data can be shimmed or faked to prevent getting hung
 * up on things like privacy policy, terms of service, etc.
 *
 * @since 2.8.0
 */
function edds_prb_ajax_process_checkout() {
	// Clear any errors that might be used as a reason to attempt a redirect in the following action.
	edd_clear_errors();

	$download_id = isset( $_POST['downloadId'] )
		? intval( $_POST['downloadId'] )
		: 0;

	$email = isset( $_POST['email'] )
		? sanitize_text_field( $_POST['email'] )
		: '';

	$name = isset( $_POST['name'] )
		? sanitize_text_field( $_POST['name'] )
		: '';

	$payment_method = isset( $_POST['paymentMethod'] )
		? $_POST['paymentMethod']
		: '';

	$context = isset( $_POST['context'] )
		? $_POST['context']
		: 'checkout';

	// Add a Download to the cart if we are not processing the full cart.
	if ( 'download' === $context ) {
		$price_id = isset( $_POST['priceId'] )
			? intval( $_POST['priceId'] )
			: false;

		$quantity = isset( $_POST['quantity'] )
			? intval( $_POST['quantity'] )
			: 1;

		// Empty cart.
		edd_empty_cart();

		// Add individual item.
		edd_add_to_cart( $download_id, array(
			'quantity' => $quantity,
			'price_id' => $price_id,
		) );

		// Refilter guest checkout when the item is added to the cart dynamically.
		// This is a duplicate of EDD_Recurring_Gateway::require_login().
		if ( defined( 'EDD_RECURRING_VERSION' ) ) {
			$cart_items    = edd_get_cart_contents();
			$has_recurring = false;
			$auto_register = class_exists( 'EDD_Auto_Register' );

			if ( ! empty( $cart_items ) ) {
				foreach ( $cart_items as $item ) {
					if ( ! isset( $item['options']['recurring'] ) ) {
						continue;
					}

					$has_recurring = true;
				}

				if ( $has_recurring && ! $auto_register ) {
					add_filter( 'edd_no_guest_checkout', '__return_true' );
					add_filter( 'edd_logged_in_only', '__return_true' );
				}
			}
		}
	}

	try {
		$data = array(
			// Mark "sub-gateway" for Stripe. This represents the Payment Method
			// currently being used. e.g `ideal`, `wepay`, `payment-request`, etc.
			//
			// This is used to filter field requirements via `edd_pre_process_purchase` hook.
			'edds-gateway'     => 'payment-request',
			'edds-prb-context' => $context,
		);

		// Checkout-specific data.
		if ( 'checkout' === $context ) {
			$form_data = isset( $_POST['form_data'] )
				? $_POST['form_data']
				: array();

			// Use the Payment Method's billing details.
			$card_name = (
				! empty( $payment_method['billing_details'] ) &&
				! empty( $payment_method['billing_details']['name'] )
			)
			? $payment_method['billing_details']['name']
			: $name;

			$billing_details = ! empty( $payment_method['billing_details'] )
				? array(
					'card_name'       => $card_name,
					'card_address'    => $payment_method['billing_details']['address']['line1'],
					'card_address_2'  => $payment_method['billing_details']['address']['line2'],
					'card_city'       => $payment_method['billing_details']['address']['city'],
					'card_zip'        => $payment_method['billing_details']['address']['postal_code'],
					'billing_country' => $payment_method['billing_details']['address']['country'],
					'card_state'      => $payment_method['billing_details']['address']['state'],
				)
				: array(
					'card_name'       => $card_name,
					'card_address'    => '',
					'card_address_2'  => '',
					'card_city'       => '',
					'card_zip'        => '',
					'billing_country' => '',
					'card_state'      => '',
				);

			// Add the Payment Request's name as the card name.
			$_POST['form_data'] = add_query_arg(
				$billing_details,
				$form_data
			);

		// Single-download data.
		} else {
			// Fake checkout form data.
			$_POST['form_data'] = http_build_query(
				array_merge(
					$data,
					array(
						// Use Email from Payment Request.
						'edd_email'                   => $email,

						'edd-user-id'                 => get_current_user_id(),
						'edd_action'                  => 'purchase',
						'edd-gateway'                 => 'stripe',
						'edd_agree_to_terms'          => '1',
						'edd_agree_to_privacy_policy' => '1',
						'edd-process-checkout-nonce'  => wp_create_nonce( 'edd-process-checkout' ),
					)
				)
			);
		}

		$_POST['payment_method_id'] = isset( $payment_method['id'] )
			? sanitize_text_field( $payment_method['id'] )
			: '';

		$_POST['payment_method_exists'] = false;

		// Adjust PaymentIntent creation for PRB flow.
		add_filter( 'edds_create_payment_intent_args', 'edds_prb_create_payment_intent_args', 20 );
		add_filter( 'edds_create_setup_intent_args', 'edds_prb_create_setup_intent_args', 20 );

		// This will send a JSON response.
		_edds_process_purchase_form();
	} catch ( \Exception $e ) {
		wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_prb_ajax_process_checkout', 'edds_prb_ajax_process_checkout' );
add_action( 'wp_ajax_nopriv_edds_prb_ajax_process_checkout', 'edds_prb_ajax_process_checkout' );

/**
 * Filters the arguments used when creating a PaymentIntent while
 * using a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param array $args {
 *   PaymentIntent arguments.
 *
 *   @link https://stripe.com/docs/api/payment_intents/create
 * }
 * @return array
 */
function edds_prb_create_payment_intent_args( $args ) {
	$args['confirmation_method']  = 'automatic';
	$args['confirm']              = false;
	$args['capture_method']       = 'automatic';
	$args['metadata']['edds_prb'] = '1';

	return $args;
}

/**
 * Filters the arguments used when creating a SetupIntent while
 * using a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param array $args {
 *   SetupIntent arguments.
 *
 *   @link https://stripe.com/docs/api/setup_intents/create
 * }
 * @return array
 */
function edds_prb_create_setup_intent_args( $args ) {
	$args['confirm']              = false;
	$args['metadata']['edds_prb'] = '1';

	return $args;
}

/**
 * Gathers Payment Request options based on the current context.
 *
 * @since 2.8.0
 */
function edds_prb_ajax_get_options() {
	$download_id = isset( $_POST['downloadId'] )
		? intval( $_POST['downloadId'] )
		: 0;

	// Single Download.
	if ( ! empty( $download_id ) ) {
		$price_id = isset( $_POST['priceId'] ) && 'false' !== $_POST['priceId']
			? intval( $_POST['priceId'] )
			: false;

		$quantity = isset( $_POST['quantity'] )
			? intval( $_POST['quantity'] )
			: 1;

		$data = edds_prb_get_download_data( $download_id, $price_id, $quantity );

	// Handle cart eventually?
	} else {
		$data = edds_prb_get_cart_data();
	}

	// Country is not valid at this point.
	// https://stripe.com/docs/js/payment_request/update
	unset( $data['country'] );

	wp_send_json_success( $data );
}
add_action( 'wp_ajax_edds_prb_ajax_get_options', 'edds_prb_ajax_get_options' );
add_action( 'wp_ajax_nopriv_edds_prb_ajax_get_options', 'edds_prb_ajax_get_options' );
