<?php

/**
 * Load our javascript
 *
 * The Stripe JS is by default, loaded on every page as suggested by Stripe. This can be overridden by using the Restrict Stripe Assets
 * setting within the admin, and the Stripe Javascript resources will only be loaded when necessary.
 * @link https://stripe.com/docs/web/setup
 *
 * The custom Javascript for EDD is loaded if the page is checkout. If checkout, the function is called directly with
 * `true` set for the `$force_load_scripts` argument.
 *
 * @access      public
 * @since       1.0
 *
 * @param bool $force_load_scripts Allows registering our Javascript files on pages other than is_checkout().
 *                                 This argument allows the `edd_stripe_js` function to be called directly, outside of
 *                                 the context of checkout, such as the card management or update subscription payment method
 *                                 UIs. Sending in 'true' will ensure that the Javascript resources are enqueued when you need them.
 *
 *
 * @return      void
 */
function edd_stripe_js( $force_load_scripts = false ) {
	if ( false === edds_is_gateway_active() ) {
		return;
	}

	wp_register_script(
		'sandhills-stripe-js-v3',
		'https://js.stripe.com/v3/',
		array(),
		'v3'
	);

	$is_checkout     = edd_is_checkout() && 0 < edd_get_cart_total();
	$restrict_assets = edd_get_option( 'stripe_restrict_assets', false );

	if ( $is_checkout || $force_load_scripts || false === $restrict_assets ) {
		wp_enqueue_script( 'sandhills-stripe-js-v3' );
	}

	if ( $is_checkout || $force_load_scripts ) {
		$publishable_key_option = edd_is_test_mode() ? 'test_publishable_key' : 'live_publishable_key';
		$publishable_key        = edd_get_option( $publishable_key_option, '' );

		// We're going to assume Payment Elements needs to load...
		$script_source = EDD_PLUGIN_URL . 'assets/js/stripe-paymentelements.js';
		$script_deps   = array(
			'sandhills-stripe-js-v3',
			'jquery',
			'edd-ajax',
		);

		// But if the user has Card Elements, we need to load that instead.
		$elements_mode = edds_get_elements_mode();
		if ( 'card-elements' === $elements_mode ) {
			$script_source = EDD_PLUGIN_URL . 'assets/js/stripe-cardelements.js';
			$script_deps[] = 'jQuery.payment';
		}

		wp_register_script(
			'edd-stripe-js',
			$script_source,
			$script_deps,
			EDD_VERSION . '-' . $elements_mode,
			true
		);

		wp_enqueue_script( 'edd-stripe-js' );

		$stripe_localized_vars = array(
			'publishable_key'                => trim( $publishable_key ),
			'isTestMode'                     => edd_is_test_mode() ? 'true' : 'false',
			'elementsMode'                   => $elements_mode,
			'is_ajaxed'                      => edd_is_ajax_enabled() ? 'true' : 'false',
			'currency'                       => edd_get_currency(),
			// @todo Replace with country code derived from Stripe Account information if available.
			// @link https://github.com/easydigitaldownloads/edd-stripe/issues/654
			'country'                        => edd_get_option( 'base_country', 'US' ),
			'locale'                         => edds_get_stripe_checkout_locale(),
			'is_zero_decimal'                => edds_is_zero_decimal_currency() ? 'true' : 'false',
			'checkout'                       => edd_get_option( 'stripe_checkout' ) ? 'true' : 'false',
			'store_name'                     => ! empty( edd_get_option( 'entity_name' ) ) ? edd_get_option( 'entity_name' ) : get_bloginfo( 'name' ),
			'submit_text'                    => edd_get_option( 'stripe_checkout_button_text', __( 'Next', 'easy-digital-downloads' ) ),
			'image'                          => edd_get_option( 'stripe_checkout_image' ),
			'zipcode'                        => edd_get_option( 'stripe_checkout_zip_code', false ) ? 'true' : 'false',
			'billing_address'                => edd_get_option( 'stripe_checkout_billing', false ) ? 'true' : 'false',
			'remember_me'                    => edd_get_option( 'stripe_checkout_remember', false ) ? 'true' : 'false',
			'no_key_error'                   => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'easy-digital-downloads' ),
			'checkout_required_fields_error' => __( 'Please fill out all required fields to continue your purchase.', 'easy-digital-downloads' ),
			'checkout_agree_to_terms'        => __( 'Please agree to the terms to complete your purchase.', 'easy-digital-downloads' ),
			'checkout_agree_to_privacy'      => __( 'Please agree to the privacy policy to complete your purchase.', 'easy-digital-downloads' ),
			'generic_error'                  => __( 'Unable to complete your request. Please try again.', 'easy-digital-downloads' ),
			'prepaid'                        => edd_get_option( 'stripe_allow_prepaid', false ) ? 'true' : 'false',
			'successPageUri'                 => edd_get_success_page_uri(),
			'failurePageUri'                 => edd_get_failed_transaction_uri(),
			'debuggingEnabled'               => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'true' : 'false',
			'formLoadingText'                => __( 'Please wait...', 'easy-digital-downloads' ),
			'cartHasSubscription'            => function_exists( 'edd_recurring' ) && edd_recurring()->cart_contains_recurring() ? 'true' : 'false',
		);

		$stripe_vars = apply_filters(
			'edd_stripe_js_vars',
			$stripe_localized_vars
		);

		wp_localize_script( 'edd-stripe-js', 'edd_stripe_vars', $stripe_vars );

	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_js', 100 );

/**
 * Conditionally load the Stripe CSS
 *
 * @param bool $force_load_scripts If we should forece loading the scripts outside of checkout.
 */
function edd_stripe_css( $force_load_scripts = false ) {
	if ( false === edds_is_gateway_active() ) {
		return;
	}

	if ( edd_is_checkout() || $force_load_scripts ) {
		$deps = array( 'edd-styles' );

		if ( ! wp_script_is( 'edd-styles', 'enqueued' ) ) {
			$deps = array();
		}

		$rtl = is_rtl() ? '-rtl' : '';
		// We're going to assume Payment Elements needs to load...
		$style_src = EDD_PLUGIN_URL . "assets/css/stripe-paymentelements{$rtl}.min.css";

		// But if the user has Card Elements, we need to load that instead.
		$elements_mode = edds_get_elements_mode();
		if ( 'card-elements' === $elements_mode ) {
			$style_src = EDD_PLUGIN_URL . "assets/css/stripe-cardelements{$rtl}.min.css";
		}

		wp_register_style(
			'edd-stripe',
			$style_src,
			$deps,
			EDD_VERSION . '-' . $elements_mode
		);

		wp_enqueue_style( 'edd-stripe' );
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_css', 100 );

/**
 * Loads the javascript for the Stripe Connect functionality in the settings page.
 *
 * @param string $hook The current admin page.
 */
function edd_stripe_connect_admin_script( $hook ) {

	if ( 'download_page_edd-settings' !== $hook ) {
		return;
	}

	edd_stripe_connect_admin_style();

	wp_enqueue_script( 'edd-stripe-admin-scripts', EDD_PLUGIN_URL . 'assets/js/stripe-admin.js', array( 'jquery' ), EDD_VERSION, true );

	$test_key = edd_get_option( 'test_publishable_key' );
	$live_key = edd_get_option( 'live_publishable_key' );

	wp_localize_script(
		'edd-stripe-admin-scripts',
		'edd_stripe_admin',
		array(
			'stripe_enabled'  => array_key_exists( 'stripe', edd_get_enabled_payment_gateways() ),
			'test_mode'       => (int) edd_is_test_mode(),
			'test_key_exists' => ! empty( $test_key ) ? 'true' : 'false',
			'live_key_exists' => ! empty( $live_key ) ? 'true' : 'false',
			'ajaxurl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'edd_stripe_connect_admin_script' );

/**
 * Enqueues the Stripe admin style.
 *
 * @since 2.9.3
 *
 * @return void
 */
function edd_stripe_connect_admin_style() {
	$rtl = is_rtl() ? '-rtl' : '';

	wp_enqueue_style(
		'edd-stripe-admin-styles',
		EDD_PLUGIN_URL . "assets/css/stripe-admin{$rtl}.min.css",
		array(),
		EDD_VERSION
	);
}
