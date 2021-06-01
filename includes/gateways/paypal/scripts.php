<?php
/**
 * PayPal Commerce Scripts
 *
 * @package    Sandhills Development, LLC
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Ashley Gibson
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;

/**
 * Enqueues polyfills for Promise and Fetch.
 *
 * @since 2.11
 */
function maybe_enqueue_polyfills() {
	/**
	 * Filters whether or not IE11 polyfills should be loaded.
	 * Note: This filter may have its default changed at any time, or may entirely
	 * go away at one point.
	 *
	 * @since 2.11
	 */
	if ( ! apply_filters( 'edd_load_ie11_polyfills', true ) ) {
		return;
	}

	global $wp_version;
	if ( version_compare( $wp_version, '5.0', '>=' ) ) {
		wp_enqueue_script( 'wp-polyfill' );
	} else {
		wp_enqueue_script(
			'wp-polyfill',
			EDD_PLUGIN_URL . 'assets/js/wp-polyfill.min.js',
			array(),
			false,
			false
		);
	}
}

/**
 * Registers PayPal JavaScript
 *
 * @param bool $force_load
 *
 * @since 2.11
 * @return void
 */
function register_js( $force_load = false ) {
	if ( ! edd_is_gateway_active( 'paypal_commerce' ) ) {
		return;
	}

	try {
		$api = new API();
	} catch ( Authentication_Exception $e ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	/**
	 * Filters the query arguments added to the SDK URL.
	 *
	 * @link  https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
	 *
	 * @since 2.11
	 */
	$sdk_query_args = apply_filters( 'edd_paypal_js_sdk_query_args', array(
		'client-id'       => urlencode( $api->client_id ),
		'locale'          => urlencode( get_locale() ),
		'currency'        => urlencode( strtoupper( edd_get_currency() ) ),
		'intent'          => 'capture',
		'disable-funding' => 'card,credit,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo'
	) );

	wp_register_script(
		'sandhills-paypal-js-sdk',
		add_query_arg( array_filter( $sdk_query_args ), 'https://www.paypal.com/sdk/js' )
	);

	wp_register_script(
		'edd-paypal',
		EDD_PLUGIN_URL . 'assets/js/paypal-checkout' . $suffix . '.js',
		array(
			'sandhills-paypal-js-sdk',
			'jquery',
			'edd-ajax'
		),
		EDD_VERSION,
		true
	);

	if ( edd_is_checkout() || $force_load ) {
		maybe_enqueue_polyfills();

		wp_enqueue_script( 'sandhills-paypal-js-sdk' );
		wp_enqueue_script( 'edd-paypal' );

		$paypal_script_vars = array(
			/**
			 * Filters the order approval handler.
			 *
			 * @since 2.11
			 */
			'approvalAction' => apply_filters( 'edd_paypal_on_approve_action', 'edd_capture_paypal_order' ),
			'defaultError'   => edd_build_errors_html( array(
				'paypal-error' => esc_html__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' )
			) ),
			'intent'         => ! empty( $sdk_query_args['intent'] ) ? $sdk_query_args['intent'] : 'capture',

			/**
			 * Filters the button styles.
			 *
			 * @since 2.11
			 */
			'style'          => apply_filters( 'edd_paypal_smart_button_style', array(
				'label' => 'paypal'
			) )
		);

		wp_localize_script( 'edd-paypal', 'eddPayPalVars', $paypal_script_vars );
	}
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_js', 100 );

/**
 * Removes the "?ver=" query arg from the PayPal JS SDK URL, because PayPal will throw an error
 * if it's included.
 *
 * @param string $url
 *
 * @since 2.11
 * @return string
 */
function remove_ver_query_arg( $url ) {
	$sdk_url = 'https://www.paypal.com/sdk/js';

	if ( false !== strpos( $url, $sdk_url ) ) {
		$new_url = preg_split( "/(&ver|\?ver)/", $url );

		return $new_url[0];
	}

	return $url;
}

add_filter( 'script_loader_src', __NAMESPACE__ . '\remove_ver_query_arg', 100 );
