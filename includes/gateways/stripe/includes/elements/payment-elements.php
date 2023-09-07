<?php
/**
 * Payments Elements helper functions
 *
 * Note: These are not used in the Card Elements integration.
 */

/**
 * Retreives and allows filtering the payout elements theme.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_theme() {
	$payment_elements_theme = 'stripe';

	/**
	 * Filters the theme used for the Payment Elements object
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/elements/appearance-api#theme
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $payment_elements_theme The theme to use for the Payment Element object.
	 */
	return apply_filters( 'edds_stripe_payment_elements_theme', $payment_elements_theme );
}

/**
 * Retreives and allows filtering the payout elements variables.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_variables() {
	$payment_elements_variables = array();

	/**
	 * Filters the variables used for the Payment Elements object
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/elements/appearance-api?platform=web#variables
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $payment_elements_variables Variables used for the Payment Elements.
	 */
	return apply_filters( 'edds_stripe_payment_elements_variables', $payment_elements_variables );
}

/**
 * Retreives and allows filtering the payout elements rules.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_rules() {
	$payment_elements_rules = array();

	/**
	 * Filters the rules used for the Payment Elements object
	 *
	 * @example
	 * To match styles as closely as possible, EDD's imlementation does not use base 'border' declartations in our rules.
	 * If you want to customize the border behavior of inputs, you need to delcare a rule for each cardinal location:
	 * borderTop, borderRight, borderBottom, and borderLeft, with the full border color, width and style. The border radius
	 * is defined as borderRadius.
	 *
	 * @see assets/js/src/frontend/payment-elements/index.php::generateElementStyles
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/elements/appearance-api?platform=web#rules
	 *
	 * @param array $payment_elements_rules Rules used for the Payment Elements.
	 */
	return apply_filters( 'edds_stripe_payment_elements_rules', $payment_elements_rules );
}

/**
 * Retreives and allows filtering the layout array sent to the Payment Elements.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_layout() {
	$payment_elements_layout = array(
		'type'             => 'tabs',
		'defaultCollapsed' => false,
	);

	/**
	 * Filters the layout variables passed to the Stripe Payment Elements.
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/js/elements_object/create_payment_element#payment_element_create-options-layout
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $payment_elements_layout Layout values used to create Stripe Elements object.
	 */
	return apply_filters( 'edds_stripe_payment_elements_layout', $payment_elements_layout );
}

/**
 * Retreives and allows filtering the wallets sent to the Payment Elements.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_wallets() {

	$default_wallet_behavior = 'auto';

	/**
	 * Allows the ability to completely disable wallets (Google Pay, Apple Pay, etc) with a single filter.
	 *
	 * @example
	 * add_filter( 'edds_stripe_payment_elements_disable_wallets', '__return_true' );
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/js/elements_object/create_payment_element#payment_element_create-options-wallets
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param bool If wallets should be disabled.
	 */
	if ( apply_filters( 'edds_stripe_payment_elements_disable_wallets', false ) ) {
		$default_wallet_behavior = 'never';
	}

	$enabled_wallets = array(
		'applePay'  => $default_wallet_behavior,
		'googlePay' => $default_wallet_behavior,
	);

	/**
	 * Filters the wallets that the Payment Element will load.
	 *
	 * If you want to disable these, set their values to `never`
	 *
	 * @example
	 * array(
	 *     'applePay'  => 'never',
	 *     'googlePay' => 'auto',
	 * )
	 *
	 * This uses an array_merge to ensure that if someone supplies a partial array we don't lose the default behavior.
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/js/elements_object/create_payment_element#payment_element_create-options-wallets
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $enabled_wallets Allowed wallets payment methods ot use on the Payment Element.
	 */
	$enabld_wallets = array_merge( $enabled_wallets, apply_filters( 'edds_stripe_payment_elements_wallets', $enabled_wallets ) );

	return $enabld_wallets;
}

/**
 * Retreives and allows filtering the label style sent to the Payment Elements.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_label_style() {
	$label_style = 'above';

	/**
	 * Filters the label appearance option.
	 *
	 * This can be set to either 'above' or 'floating'
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/elements/appearance-api?platform=web#others
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $label_style The style to use for the Payment Elements labels.
	 */
	return apply_filters( 'edds_stripe_payment_elements_label_style', $label_style );

}

/**
 * Allows passing custom fonts into the Stripe Payment Elements.
 *
 * @since 2.9.0
 */
function edds_get_stripe_payment_elements_fonts() {
	$fonts = array();

	/**
	 * Allows passing custom font objects into the Stripe Elements.
	 *
	 * This either needs to be a CSS font soruce object, or a custom font source object.
	 * You can see the format and requiremnts for these in the links below. We default to none.
	 *
	 * @since 2.9.0
	 *
	 * @link https://stripe.com/docs/js/appendix/css_font_source_object
	 * @link https://stripe.com/docs/js/appendix/custom_font_source_object
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::generateElementStyles
	 *
	 * @param array $fonts The style to use for the Payment Elements labels.
	 */
	return apply_filters( 'edds_stripe_payment_elements_fonts', $fonts );

}

/**
 * Allows passing custom fields into the Stripe Elements.
 *
 * @since 2.9.2.2
 */
function edds_get_stripe_payment_elements_fields() {
	$default_fields = array(
		'billingDetails' => array(
			'name'    => 'auto',
			'email'   => 'never', // It is not advised to change this to auto, as it will create duplicate email fields on checkout.
			'phone'   => 'never',
			'address' => 'never',
		),
	);

	// By default, if the store has the address fields required, don't include them in the Payment Element.
	if ( 'none' !== edd_get_option( 'stripe_billing_fields', 'none' ) ) {
		$default_fields['billingDetails']['address'] = 'never';
	}

	/**
	 * Allows passing custom fields into the Stripe Elements.
	 *
	 * This needs to be an array. The default fields hold our values for the billingDetails fields. Fields can have a value of
	 * either 'auto' or 'never'. If you want to disable a field, set it to 'never'. When set to 'auto', Stripe will attempt to
	 * determine if the field is necessary based on a combination of currency, country, and account.
	 *
	 * @since 2.9.2.2
	 *
	 * @link https://stripe.com/docs/js/elements_object/create_payment_element#payment_element_create-options-fields
	 *
	 * @see assets/js/src/frontend/payment-elements/index.js::createAndMountElement
	 *
	 * @param array $default_fields The default fields and their values.
	 */
	return apply_filters( 'edds_stripe_payment_elements_fields', $default_fields );
}

/**
 * Returns an array of terms for the payment elements.
 *
 * @since 2.9.4
 *
 * @return array The terms array by payment method.
 */
function edds_get_stripe_payment_elements_terms() {
	$terms = array( 'card' => 'auto' );

	/**
	 * Allows filtering the payment elements terms.
	 *
	 * @see https://stripe.com/docs/js/elements_object/create_payment_element#payment_element_create-options-terms
	 *
	 * @since 2.9.4
	 * @param array The terms array by payment method.
	 */

	return apply_filters( 'edds_stripe_payment_elements_terms', $terms );
}

/**
 * Gathers all the possible customizations for the Stripe Payment Elements.
 *
 * Pulls in the filtered customizations for the theme, variables, rules, and layout items
 * for the Payment Elements instantiation. This allows developers to make customizations.
 *
 * EDD does attempt to match the input styles on the checkout already, in the Javascript.
 *
 * @since 2.9.0
 *
 * @returns array $customizations The array of customizations.
 */
function edds_gather_payment_element_customizations() {
	$customizations = array(
		'theme'              => edds_get_stripe_payment_elements_theme(),
		'variables'          => edds_get_stripe_payment_elements_variables(),
		'rules'              => edds_get_stripe_payment_elements_rules(),
		'layout'             => edds_get_stripe_payment_elements_layout(),
		'wallets'            => edds_get_stripe_payment_elements_wallets(),
		'labels'             => edds_get_stripe_payment_elements_label_style(),
		'fonts'              => edds_get_stripe_payment_elements_fonts(),
		'paymentMethodTypes' => edds_payment_element_payment_method_types(),
		'fields'             => edds_get_stripe_payment_elements_fields(),
		'terms'              => edds_get_stripe_payment_elements_terms(),
		'i18n'               => array(
			'errorMessages' => edds_get_localized_error_messages(),
		),
	);

	if ( function_exists( 'edd_recurring' ) ) {
		$customizations['cartHasSubscription'] = edd_recurring()->cart_contains_recurring() ? 'true' : 'false';
	}

	return $customizations;
}

/**
 * Add the Payment Elements values to the Stripe Localized variables.
 *
 * @since 2.9.0
 *
 * @param array $stripe_vars The array of values to localize for Stripe.
 *
 * @return array Includes any Payment Elements values to the array of localized variables.
 */
function edds_payment_element_js_vars( $stripe_vars ) {
	$stripe_vars['elementsCustomizations'] = edds_gather_payment_element_customizations();

	return $stripe_vars;
}
add_filter( 'edd_stripe_js_vars', 'edds_payment_element_js_vars', 10, 1 );

/**
 * Returns an array of payment method types.
 *
 * By default this is empty to allow the automatic payment methods to take over, but in the event someone
 * wants to alter this, they can at their own risk as EDD controls what payment methods are availbale
 * at the platform level.
 *
 * As of 2.9.2.1 - EDD only supports 'card' and 'link' as options.
 *
 * @since 2.9.2.1
 *
 * @return array The allowed payment_method_types
 */
function edds_payment_element_payment_method_types() {
	/**
	 * Allows passing payment_method_types to the elements and intent.
	 *
	 * This is by default empty, but you can alter this on an account level if needed.
	 *
	 * @since 2.9.2.1
	 *
	 * @example
	 * add_filter( 'edds_stripe_payment_elements_payment_method_types', function( $payment_method_types ) {
	 *    return array(
	 *        'card',
	 *        'link',
	 *    );
	 * } );
	 *
	 * @param array The allowed payment elements payment method types.
	 */
	return apply_filters( 'edds_stripe_payment_elements_payment_method_types', array() );
}
