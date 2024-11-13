<?php
/**
 * Payment receipt.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Validates the confirmation data for a Stripe PaymentIntent.
 *
 * @since 3.3.5
 * @return void
 */
function edds_validate_confirmation_data() {
	if ( ! edd_is_success_page() ) {
		return;
	}

	$payment_intent  = filter_input( INPUT_GET, 'payment_intent', FILTER_SANITIZE_SPECIAL_CHARS );
	$redirect_status = filter_input( INPUT_GET, 'redirect_status', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( ! $payment_intent || ! $redirect_status ) {
		return;
	}

	EDD\Gateways\Stripe\Checkout\Confirmation::validate( $payment_intent );
}
add_action( 'template_redirect', 'edds_validate_confirmation_data' );
