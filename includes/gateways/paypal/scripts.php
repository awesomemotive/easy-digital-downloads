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

	wp_enqueue_script( 'wp-polyfill' );
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

	if ( ! ready_to_accept_payments() ) {
		return;
	}

	try {
		$api = new API();
	} catch ( Authentication_Exception $e ) {
		return;
	}

	/**
	 * Filters the query arguments added to the SDK URL.
	 *
	 * @link  https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
	 *
	 * @since 2.11
	 */
	$sdk_query_args = apply_filters(
		'edd_paypal_js_sdk_query_args',
		array(
			'client-id'       => urlencode( $api->client_id ),
			'currency'        => urlencode( strtoupper( edd_get_currency() ) ),
			'intent'          => 'capture',
			'disable-funding' => 'card,credit,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo',
		)
	);

	wp_register_script(
		'sandhills-paypal-js-sdk',
		esc_url_raw( add_query_arg( array_filter( $sdk_query_args ), 'https://www.paypal.com/sdk/js' ) )
	);

	wp_register_script(
		'edd-paypal',
		EDD_PLUGIN_URL . 'assets/js/paypal-checkout.js',
		array(
			'sandhills-paypal-js-sdk',
			'jquery',
			'edd-ajax',
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
			'defaultError'   => edd_build_errors_html(
				array(
					'paypal-error' => esc_html__( 'An unexpected error occurred. Please try again.', 'easy-digital-downloads' ),
				)
			),
			'intent'         => ! empty( $sdk_query_args['intent'] ) ? $sdk_query_args['intent'] : 'capture',
			'style'          => get_button_styles(),
		);

		wp_localize_script( 'edd-paypal', 'eddPayPalVars', $paypal_script_vars );
	}
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_js', 100 );

/**
 * Removes the "?ver=" query arg from the PayPal JS SDK URL, because PayPal will throw an error
 * if it's included.
 *
 * @param string $url The URL for the script source.
 *
 * @since 2.11
 * @return string
 */
function remove_ver_query_arg( $url ) {
	// Account for a possibly empty URL here.
	if ( empty( $url ) ) {
		return $url;
	}

	$sdk_url = 'https://www.paypal.com/sdk/js';

	if ( false !== strpos( $url, $sdk_url ) ) {
		$new_url = preg_split( '/(&ver|\?ver)/', $url );

		return $new_url[0];
	}

	return $url;
}

add_filter( 'script_loader_src', __NAMESPACE__ . '\remove_ver_query_arg', 100 );

/**
 * Adds data attributes to the PayPal JS SDK <script> tag.
 *
 * @link  https://developer.paypal.com/docs/checkout/reference/customize-sdk/#script-parameters
 *
 * @since 2.11
 *
 * @param string $script_tag HTML <script> tag.
 * @param string $handle     Registered handle.
 * @param string $src        Script SRC value.
 *
 * @return string
 */
function add_data_attributes( $script_tag, $handle, $src ) {
	if ( 'sandhills-paypal-js-sdk' !== $handle ) {
		return $script_tag;
	}

	/**
	 * Filters the data attributes to add to the <script> tag.
	 *
	 * @since 2.11
	 *
	 * @param array $data_attributes
	 */
	$data_attributes = apply_filters(
		'edd_paypal_js_sdk_data_attributes',
		array(
			'partner-attribution-id' => EDD_PAYPAL_PARTNER_ATTRIBUTION_ID,
		)
	);

	if ( empty( $data_attributes ) || ! is_array( $data_attributes ) ) {
		return $script_tag;
	}

	$formatted_attributes = array_map(
		function ( $key, $value ) {
			return sprintf( 'data-%s="%s"', sanitize_html_class( $key ), esc_attr( $value ) );
		},
		array_keys( $data_attributes ),
		$data_attributes
	);

	return str_replace( ' src', ' ' . implode( ' ', $formatted_attributes ) . ' src', $script_tag );
}

add_filter( 'script_loader_tag', __NAMESPACE__ . '\add_data_attributes', 10, 3 );
