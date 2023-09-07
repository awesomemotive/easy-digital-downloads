<?php
/**
 * Stripe Card Elements functionality.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Legacy Card Elements helper functions
 *
 * Note: These are not used in the Payment Elements integration.
 */

/**
 * Retrieves the styles passed to the Stripe Elements instance.
 *
 * @since 2.7.0
 *
 * @link https://stripe.com/docs/stripe-js
 * @link https://stripe.com/docs/stripe-js/reference#elements-create
 * @link https://stripe.com/docs/stripe-js/reference#element-options
 *
 * @return array
 */
function edds_get_stripe_elements_styles() {
	$elements_styles = array();

	/**
	 * Filters the styles used to create the Stripe Elements card field.
	 *
	 * @since 2.7.0
	 *
	 * @link https://stripe.com/docs/stripe-js/reference#element-options
	 *
	 * @param array $elements_styles Styles used to create Stripe Elements card field.
	 */
	$elements_styles = apply_filters( 'edds_stripe_elements_styles', $elements_styles );

	return $elements_styles;
}

/**
 * Retrieves the options passed to the Stripe Elements instance.
 *
 * @since 2.7.0
 *
 * @link https://stripe.com/docs/stripe-js
 * @link https://stripe.com/docs/stripe-js/reference#elements-create
 * @link https://stripe.com/docs/stripe-js/reference#element-options
 *
 * @return array
 */
function edds_get_stripe_elements_options() {
	$elements_options = array(
		'hidePostalCode' => true,
		'i18n'           => array(
			'errorMessages' => edds_get_localized_error_messages(),
		),
	);
	$elements_styles  = edds_get_stripe_elements_styles();

	if ( ! empty( $elements_styles ) ) {
		$elements_options['style'] = $elements_styles;
	}

	/**
	 * Filters the options used to create the Stripe Elements card field.
	 *
	 * @since 2.7.0
	 *
	 * @link https://stripe.com/docs/stripe-js/reference#element-options
	 *
	 * @param array $elements_options Options used to create Stripe Elements card field.
	 */
	$elements_options = apply_filters( 'edds_stripe_elements_options', $elements_options );

	// Elements doesn't like an empty array (which won't be converted to an object in JS).
	if ( empty( $elements_options ) ) {
		return null;
	}

	return $elements_options;
}

/**
 * Add the Card Elements values to the Stripe Localized variables.
 *
 * @since 2.9.0
 *
 * @param array $stripe_vars The array of values to localize for Stripe.
 *
 * @return array Includes any Card Elements values to the array of localized variables.
 */
function edds_card_element_js_vars( $stripe_vars ) {
	$stripe_vars['elementsOptions']           = edds_get_stripe_elements_options();
	$stripe_vars['elementsSplitFields']       = '1' === edd_get_option( 'stripe_split_payment_fields', false ) ? 'true' : 'false';
	$stripe_vars['checkoutHasPaymentRequest'] = edds_prb_is_enabled( 'checkout' ) ? 'true' : 'false';

	return $stripe_vars;
}
add_filter( 'edd_stripe_js_vars', 'edds_card_element_js_vars', 10, 1 );
