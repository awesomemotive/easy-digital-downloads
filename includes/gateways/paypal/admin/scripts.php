<?php
/**
 * PayPal Commerce Admin Scripts
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal\Admin;

/**
 * Enqueue PayPal connect admin JS.
 *
 * @since 2.11
 */
function enqueue_connect_scripts() {
	if ( edd_is_admin_page( 'settings' ) && isset( $_GET['section'] ) && 'paypal_commerce' === $_GET['section'] ) {
		\EDD\Gateways\PayPal\maybe_enqueue_polyfills();

		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$subdomain = edd_is_test_mode() ? 'sandbox.' : '';

		wp_enqueue_script(
			'sandhills-paypal-partner-js',
			'https://www.' . $subdomain . 'paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
			array(),
			null,
			true
		);

		wp_enqueue_script(
			'edd-paypal-connect',
			EDD_PLUGIN_URL . 'assets/js/admin-paypal-connect' . $suffix . '.js',
			array( 'jquery' ),
			EDD_VERSION,
			true
		);

		wp_localize_script( 'edd-paypal-connect', 'eddPayPalConnectVars', array(
			'defaultError' => esc_html__( 'An unexpected error occurred. Please refresh the page and try again.', 'easy-digital-downloads' )
		) );
	}
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_connect_scripts' );
