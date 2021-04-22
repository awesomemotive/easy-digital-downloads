<?php
/**
 * PayPal Settings
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\PayPal\Admin;

/**
 * Register the PayPal Standard gateway subsection
 *
 * @param array $gateway_sections Current Gateway Tab subsections
 *
 * @since 2.11
 * @return array                    Gateway subsections with PayPal Standard
 */
function register_paypal_gateway_section( $gateway_sections ) {
	$gateway_sections['paypal_commerce'] = __( 'PayPal', 'easy-digital-downloads' );

	return $gateway_sections;
}

add_filter( 'edd_settings_sections_gateways', __NAMESPACE__ . '\register_paypal_gateway_section', 1, 1 );

/**
 * Registers the PayPal Standard settings for the PayPal Standard subsection
 *
 * @param array $gateway_settings Gateway tab settings
 *
 * @since 2.11
 * @return array Gateway tab settings with the PayPal Standard settings
 */
function register_gateway_settings( $gateway_settings ) {

	$paypal_settings = array(
		'paypal_settings'              => array(
			'id'   => 'paypal_settings',
			'name' => '<strong>' . __( 'PayPal Settings', 'easy-digital-downloads' ) . '</strong>',
			'type' => 'header',
		),
		'paypal_connect_button'        => array(
			'id'    => 'paypal_connect_button',
			'name'  => __( 'Connection Status', 'easy-digital-downloads' ),
			'desc'  => connect_settings_field(),
			'type'  => 'descriptive_text',
			'class' => 'edd-paypal-connect-row'
		),
		// @todo Connection stuff.
		'paypal_sandbox_client_id'     => array(
			'id'   => 'paypal_sandbox_client_id',
			'name' => __( 'Test Client ID', 'easy-digital-downloads' ),
			'desc' => __( 'Enter your test client ID.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular',
			//'class' => 'edd-hidden edd-paypal-credentials-row' // @todo
		),
		'paypal_sandbox_client_secret' => array(
			'id'   => 'paypal_sandbox_client_secret',
			'name' => __( 'Test Client Secret', 'easy-digital-downloads' ),
			'desc' => __( 'Enter your test client secret.', 'easy-digital-downloads' ),
			'type' => 'password',
			'size' => 'regular',
			//'class' => 'edd-hidden edd-paypal-credentials-row' // @todo
		),
		'paypal_live_client_id'        => array(
			'id'   => 'paypal_live_client_id',
			'name' => __( 'Live Client ID', 'easy-digital-downloads' ),
			'desc' => __( 'Enter your live client ID.', 'easy-digital-downloads' ),
			'type' => 'text',
			'size' => 'regular',
			//'class' => 'edd-hidden edd-paypal-credentials-row' // @todo
		),
		'paypal_live_client_secret'    => array(
			'id'   => 'paypal_live_client_secret',
			'name' => __( 'Live Client Secret', 'easy-digital-downloads' ),
			'desc' => __( 'Enter your live client secret.', 'easy-digital-downloads' ),
			'type' => 'password',
			'size' => 'regular',
			//'class' => 'edd-hidden edd-paypal-credentials-row' // @todo
		),
	);

	/**
	 * Filters the PayPal Settings.
	 *
	 * @param array $paypal_settings
	 */
	$paypal_settings                     = apply_filters( 'edd_paypal_settings', $paypal_settings );
	$gateway_settings['paypal_commerce'] = $paypal_settings;

	return $gateway_settings;
}

add_filter( 'edd_settings_gateways', __NAMESPACE__ . '\register_gateway_settings', 1, 1 );
