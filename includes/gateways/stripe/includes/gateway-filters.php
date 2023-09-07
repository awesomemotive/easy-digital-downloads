<?php

/**
 * Removes Stripe from active gateways if application requirements are not met.
 *
 * @since 2.8.1
 * @deprecated 3.2.0
 * @param array $enabled_gateways Enabled gateways that allow purchasing.
 * @return array
 */
function edds_validate_gateway_requirements( $enabled_gateways ) {
	if ( false === edds_has_met_requirements() ) {
		unset( $enabled_gateways['stripe'] );
	}

	return $enabled_gateways;
}

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since 2.0
 *
 * @param array $purchase_data
 * @return array
 */
function edd_stripe_straight_to_gateway_data( $purchase_data ) {

	$gateways = edd_get_enabled_payment_gateways();

	if ( isset( $gateways['stripe'] ) ) {
		$_REQUEST['edd-gateway']  = 'stripe';
		$purchase_data['gateway'] = 'stripe';
	}

	return $purchase_data;
}
add_filter( 'edd_straight_to_gateway_purchase_data', 'edd_stripe_straight_to_gateway_data' );

/**
 * Process the POST Data for the Credit Card Form, if a token wasn't supplied
 *
 * @since  2.2
 * @return array The credit card data from the $_POST
 */
function edds_process_post_data( $purchase_data ) {
	if ( ! isset( $purchase_data['gateway'] ) || 'stripe' !== $purchase_data['gateway'] ) {
		return;
	}

	$elements_mode = edds_get_elements_mode();
	// These are card-elements validations.
	if ( 'card-elements' === $elements_mode ) {
		if ( isset( $_POST['edd_stripe_existing_card'] ) && 'new' !== $_POST['edd_stripe_existing_card'] ) {
			return;
		}

		// Require a name for new cards.
		if ( ! isset( $_POST['card_name'] ) || strlen( trim( $_POST['card_name'] ) ) === 0 ) {
			edd_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'easy-digital-downloads' ) );
		}
	}
}
add_action( 'edd_checkout_error_checks', 'edds_process_post_data' );

/**
 * Retrieves the locale used for Checkout modal window
 *
 * @since  2.5
 * @return string The locale to use
 */
function edds_get_stripe_checkout_locale() {
	return apply_filters( 'edd_stripe_checkout_locale', 'auto' );
}

/**
 * Sets the $_COOKIE global when a logged in cookie is available.
 *
 * We need the global to be immediately available so calls to wp_create_nonce()
 * within the same session will use the newly available data.
 *
 * @since 2.8.0
 *
 * @link https://wordpress.stackexchange.com/a/184055
 *
 * @param string $logged_in_cookie The logged-in cookie value.
 */
function edds_set_logged_in_cookie_global( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}
