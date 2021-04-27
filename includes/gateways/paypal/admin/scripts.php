<?php
/**
 * PayPal Commerce Admin Scripts
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\PayPal\Admin;

/**
 * Enqueue PayPal connect admin JS.
 *
 * @since 2.11
 */
function enqueue_connect_scripts() {
	if ( edd_is_admin_page( 'settings' ) && edd_is_gateway_active( 'paypal_commerce' ) ) {
		\EDD\PayPal\maybe_enqueue_polyfills();

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
			EDD_PLUGIN_URL . 'assets/js/admin-paypal-connect.js', // @todo minify
			array( 'jquery' ),
			time(), // @todo EDD_VERSION
			true
		);
	}
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_connect_scripts' );
