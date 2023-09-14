<?php
/**
 * Buy Now: Checkout
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

// Hook in global card validation to this flow.
add_action( 'edds_buy_now_checkout_error_checks', 'edds_process_post_data' );

/**
 * Adds Buy Now-specific overrides when processing a Buy Now purchase.
 *
 * @since 2.8.0
 */
function edds_buy_now_process_overrides() {
	if ( ! isset( $_POST ) ) {
		return;
	}

	if ( ! isset( $_POST['edds-gateway'] ) ) {
		return;
	}

	if ( 'buy-now' !== $_POST['edds-gateway'] ) {
		return;
	}

	// Ensure Billing Address and Name Fields are not required.
	add_filter( 'edd_require_billing_address', '__return_false' );

	// Require email address.
	add_filter( 'edd_purchase_form_required_fields', 'edds_buy_now_purchase_form_required_fields', 9999 );

	// Remove 3rd party validations.
	remove_all_actions( 'edd_checkout_error_checks' );
	remove_all_actions( 'edd_checkout_user_error_checks' );

	// Validate the form $_POST data.
	$valid_data = edd_purchase_form_validate_fields();

	/**
	 * Allows Buy Now-specific checkout validations.
	 *
	 * @since 2.8.0
	 *
	 * @param array $valid_data Validated checkout data.
	 * @param array $_POST Global $_POST data.
	 */
	do_action( 'edds_buy_now_checkout_error_checks', $valid_data, $_POST );

	// Validate the user
	$user = edd_get_purchase_form_user( $valid_data );

	/**
	 * Allows Buy Now-specific user validations.
	 *
	 * @since 2.8.0
	 *
	 * @param array $user Validated user data.
	 * @param array $valid_data Validated checkout data.
	 * @param array $_POST Global $_POST data.
	 */
	do_action( 'edds_buy_now_checkout_user_error_checks', $user, $valid_data, $_POST );
}
add_action( 'edd_pre_process_purchase', 'edds_buy_now_process_overrides' );

/**
 * Filters the purchase form's required field to only
 * require an email address.
 *
 * @since 2.8.0
 *
 * @return array
 */
function edds_buy_now_purchase_form_required_fields() {
	return array(
		'edd_email' => array(
			'error_id' => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'easy-digital-downloads' )
		),
	);
}
