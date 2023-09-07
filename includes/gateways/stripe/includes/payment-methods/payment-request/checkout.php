<?php
/**
 * Payment Request Button: Checkout
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Registers "Express" (via Apple Pay/Google Pay) gateway.
 *
 * @since 2.8.0
 *
 * @param array $gateways Registered payment gateways.
 * @return array
 */
function edds_prb_shim_gateways( $gateways ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $gateways;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'edd_payment_gateways', 'edds_prb_shim_gateways' );
	remove_filter( 'edd_enabled_payment_gateways', 'edds_prb_shim_gateways' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === edds_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Track default gateway so we can resort the list.
	$default_gateway_id = edd_get_default_gateway();

	if ( 'stripe-prb' === $default_gateway_id ) {
		$default_gateway_id = 'stripe';
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'edd_payment_gateways', 'edds_prb_shim_gateways' );
	add_filter( 'edd_enabled_payment_gateways', 'edds_prb_shim_gateways' );

	if ( false === $enabled ) {
		return $gateways;
	}

	// Ensure default gateway is considered registered at this point.
	if ( isset( $gateways[ $default_gateway_id ] ) ) {
		$default_gateway = array(
			$default_gateway_id => $gateways[ $default_gateway_id ],
		);

		// Fall back to first gateway in the list.
	} else {
		if ( function_exists( 'array_key_first' ) ) {
			$first_gateway_id = array_key_first( $gateways );
		} else {
			$gateway_keys     = array_keys( $gateways );
			$first_gateway_id = reset( $gateway_keys );
		}
		$default_gateway  = array(
			$first_gateway_id => $gateways[ $first_gateway_id ],
		);
	}

	unset( $gateways[ $default_gateway_id ] );

	return array_merge(
		array(
			'stripe-prb' => array(
				'admin_label'    => __( 'Express Checkout (Apple Pay/Google Pay)', 'easy-digital-downloads' ),
				'checkout_label' => __( 'Express Checkout', 'easy-digital-downloads' ),
				'supports'       => array(),
			),
		),
		$default_gateway,
		$gateways
	);
}
add_filter( 'edd_payment_gateways', 'edds_prb_shim_gateways' );
add_filter( 'edd_enabled_payment_gateways', 'edds_prb_shim_gateways' );

/**
 * Enables the shimmed `stripe-prb` gateway.
 *
 * @since 2.8.0
 *
 * @param array $gateways Enabled payment gateways.
 * @return array
 */
function edds_prb_enable_shim_gateway( $gateways ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $gateways;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'edd_get_option_gateways', 'edds_prb_enable_shim_gateway' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === edds_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'edd_get_option_gateways', 'edds_prb_enable_shim_gateway' );

	if ( false === $enabled ) {
		return $gateways;
	}

	$gateways['stripe-prb'] = 1;

	return $gateways;
}
add_filter( 'edd_get_option_gateways', 'edds_prb_enable_shim_gateway' );

/**
 * Ensures the base `stripe` gateway is used as an ID _only_ when generating
 * the hidden `input[name="edd-gateway"]` field.
 *
 * @since 2.8.0
 */
function edds_prb_shim_active_gateways() {
	add_filter( 'edd_chosen_gateway', 'edds_prb_set_base_gateway' );
	add_filter( 'edd_is_gateway_active', 'edds_prb_is_gateway_active', 10, 2 );
}
add_action( 'edd_purchase_form_before_submit', 'edds_prb_shim_active_gateways' );

/**
 * Removes conversion of `stripe-prb` to `stripe` after the `input[name="edd-gateway"]`
 * hidden input is generated.
 *
 * @since 2.8.0
 */
function edds_prb_unshim_active_gateways() {
	remove_filter( 'edd_chosen_gateway', 'edds_prb_set_base_gateway' );
	remove_filter( 'edd_is_gateway_active', 'edds_prb_is_gateway_active', 10, 2 );
}
add_action( 'edd_purchase_form_after_submit', 'edds_prb_unshim_active_gateways' );

/**
 * Ensures the "Express Checkout" gateway is considered active if the setting
 * is enabled.
 *
 * @since 2.8.0
 *
 * @param bool   $active Determines if the gateway is considered active.
 * @param string $gateway The gateway ID to check.
 * @return bool
 */
function edds_prb_is_gateway_active( $active, $gateway ) {
	remove_filter( 'edd_is_gateway_active', 'edds_prb_is_gateway_active', 10, 2 );

	if (
		'stripe-prb' === $gateway &&
		true === edds_prb_is_enabled( 'checkout' )
	) {
		$active = true;
	}

	add_filter( 'edd_is_gateway_active', 'edds_prb_is_gateway_active', 10, 2 );

	return $active;
}

/**
 * Transforms the found active `stripe-prb` Express Checkout gateway back
 * to the base `stripe` gateway ID.
 *
 * @param string $gateway Chosen payment gateway.
 * @return string
 */
function edds_prb_set_base_gateway( $gateway ) {
	if ( 'stripe-prb' === $gateway ) {
		$gateway = 'stripe';
	}

	return $gateway;
}

/**
 * Filters the default gateway.
 *
 * Sets the Payment Request Button (Express Checkout) as default
 * when enabled for the context.
 *
 * @since 2.8.0
 *
 * @param string $default Default gateway.
 * @return string
 */
function edds_prb_default_gateway( $default ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $default;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'edd_default_gateway', 'edds_prb_default_gateway' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === edds_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'edd_default_gateway', 'edds_prb_default_gateway' );

	if ( false === $enabled ) {
		return $default;
	}

	return 'stripe' === $default
		? 'stripe-prb'
		: $default;
}
add_filter( 'edd_default_gateway', 'edds_prb_default_gateway' );

/**
 * Adds Payment Request-specific overrides when processing a single Download.
 *
 * Disables all required fields.
 *
 * @since 2.8.0
 */
function edds_prb_process_overrides() {
	if ( ! isset( $_POST ) ) {
		return;
	}

	if ( ! isset( $_POST['edds-gateway'] ) ) {
		return;
	}

	if ( 'payment-request' !== $_POST['edds-gateway'] ) {
		return;
	}

	if ( 'download' !== $_POST['edds-prb-context'] ) {
		return;
	}

	// Ensure Billing Address and Name Fields are not required.
	add_filter( 'edd_require_billing_address', '__return_false' );

	// Require email address.
	add_filter( 'edd_purchase_form_required_fields', 'edds_prb_purchase_form_required_fields', 9999 );

	// Remove 3rd party validations.
	remove_all_actions( 'edd_checkout_error_checks' );
	remove_all_actions( 'edd_checkout_user_error_checks' );
}
add_action( 'edd_pre_process_purchase', 'edds_prb_process_overrides' );

/**
 * Filters the purchase form's required field to only
 * require an email address.
 *
 * @since 2.8.0
 *
 * @return array
 */
function edds_prb_purchase_form_required_fields() {
	return array(
		'edd_email' => array(
			'error_id' => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'easy-digital-downloads' )
		),
	);
}

/**
 * Adds a note and metadata to Payments made with a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param \EDD_Payment                              $payment EDD Payment.
 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
 */
function edds_prb_payment_created( $payment, $intent ) {
	if ( false === isset( $intent['metadata']['edds_prb'] ) ) {
		return;
	}

	$payment->update_meta( '_edds_stripe_prb', 1 );
	$payment->add_note( 'Purchase completed with Express Checkout (Apple Pay/Google Pay)' );
}
add_action( 'edds_payment_created', 'edds_prb_payment_created', 10, 2 );

/**
 * Creates an empty Credit Card form to ensure core actions are still called.
 *
 * @since 2.8.0
 */
function edds_prb_cc_form() {
	/* This action is documented in easy-digital-downloads/includes/checkout/template.php */
	do_action( 'edd_before_cc_fields' );

	/* This action is documented in easy-digital-downloads/includes/checkout/template.php */
	do_action( 'edd_after_cc_fields' );
}

/**
 * Loads the Payment Request gateway.
 *
 * This fires before core's callbacks to avoid firing additional
 * actions (and therefore creating extra output) when using the Payment Request.
 *
 * @since 2.8.0
 */
function edds_prb_load_gateway() {
	if ( ! isset( $_POST['nonce'] ) ) {
		edd_debug_log(
			__( 'Missing nonce when loading the gateway fields. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ),
			true
		);
	}

	if ( isset( $_POST['edd_payment_mode'] ) && isset( $_POST['nonce'] ) ) {
		$payment_mode = sanitize_text_field( $_POST['edd_payment_mode'] );
		$nonce        = sanitize_text_field( $_POST['nonce'] );

		$nonce_verified = wp_verify_nonce( $nonce, 'edd-gateway-selected-' . $payment_mode );

		if ( false !== $nonce_verified ) {
			// Load the "Express" gateway.
			if ( 'stripe-prb' === $payment_mode ) {
				// Remove credit card fields.
				remove_action( 'edd_stripe_cc_form', 'edds_credit_card_form' );
				remove_action( 'edd_cc_form', 'edd_get_cc_form' );

				// Hide "Billing Details" which are populated by the Payment Method.
				add_filter( 'edd_require_billing_address', '__return_true' );
				remove_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

				remove_action( 'edd_after_cc_fields', 'edd_stripe_zip_and_country', 9 );
				remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );

				// Remove "Update billing address" checkbox. All Payment Requests create
				// a new source.
				remove_action( 'edd_cc_billing_top', 'edd_stripe_update_billing_address_field', 10 );

				// Output a Payment Request-specific credit card form (empty).
				add_action( 'edd_stripe_cc_form', 'edds_prb_cc_form' );

				// Swap purchase button with Payment Request button.
				add_filter( 'edd_checkout_button_purchase', 'edds_prb_checkout_button_purchase', 10000 );

				/**
				 * Allows further adjustments to made before the "Express Checkout"
				 * gateway is loaded.
				 *
				 * @since 2.8.0
				 */
				do_action( 'edds_prb_before_purchase_form' );
			}

			/* This action is documented in easy-digital-downloads/includes/checkout/template.php */
			do_action( 'edd_purchase_form' );

			// Ensure core callbacks are fired.
			add_action( 'wp_ajax_edd_load_gateway', 'edd_load_ajax_gateway' );
			add_action( 'wp_ajax_nopriv_edd_load_gateway', 'edd_load_ajax_gateway' );
		}

		exit();
	}
}
add_action( 'wp_ajax_edd_load_gateway', 'edds_prb_load_gateway', 5 );
add_action( 'wp_ajax_nopriv_edd_load_gateway', 'edds_prb_load_gateway', 5 );
