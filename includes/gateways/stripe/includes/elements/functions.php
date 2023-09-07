<?php

/**
 * Determines if the Stripe API keys can be managed manually.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function edds_stripe_connect_can_manage_keys() {

	$secret = edd_is_test_mode() ? edd_get_option( 'test_secret_key' ) : edd_get_option( 'live_secret_key' );

	return $secret && empty( edd_stripe()->connect()->get_connect_id() );
}

/**
 * Get the current elements mode.
 *
 * If the user is gated into the legacy mode, set the default to card-elements.
 *
 * @since 2.9.0
 * @since 2.9.5.1 We're now listening for an elements_mode flag in POST requests.
 *
 * @return string The elements mode string.
 */
function edds_get_elements_mode() {
	$default = _edds_legacy_elements_enabled() ? 'card-elements' : 'payment-elements';

	/**
	 * Because we use the deferred payment intents beta, only connected accounts can use Payment Elements
	 * for now, so we'll force them to be in `card-elements`.
	 */
	if ( edds_stripe_connect_can_manage_keys() ) {
		return 'card-elements';
	}

	/**
	 * Recurring Subscription payment method updates need to still run card elements for now.
	 */
	if (
		function_exists( 'edd_recurring' ) &&
		( isset( $_GET['action'] ) && 'update' === $_GET['action'] ) &&
		( isset( $_GET['subscription_id'] ) && is_numeric( $_GET['subscription_id'] ) )
	) {
		add_filter( 'edd_get_option_stripe_split_payment_fields', '__return_false' );
		return 'card-elements';
	}

	/**
	 * Card elements does a lot with AJAX requests, which will lose the context of being on the Subscription update form, so
	 * we are sending in a flag for using card elements with the elements_mode equal to 'card-elements' in those POST requests.
	 *
	 * @since 2.9.5.1
	 */
	if ( isset( $_POST['elements_mode'] ) && 'card-elements' === $_POST['elements_mode'] ) {
		return 'card-elements';
	}

	return edd_get_option( 'stripe_elements_mode', $default );
}

/**
 * INTERNAL ONLY: Determines if the user is gated into using the legacy card-elements.
 *
 * This is a transitionary function, intentded to allow us to later remove it. Do not
 * use this function in any extending of EDD or Stripe.
 *
 * @since 2.9.0
 *
 * @return bool If the user is gated into using the legacy card-elements.
 */
function _edds_legacy_elements_enabled() {
	return get_option( '_edds_legacy_elements_enabled', false );
}
