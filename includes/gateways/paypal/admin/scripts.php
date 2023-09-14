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

use EDD\Gateways\PayPal;

/**
 * Enqueue PayPal connect admin JS.
 *
 * @since 2.11
 */
function enqueue_connect_scripts() {
	if ( edd_is_admin_page( 'settings' ) && isset( $_GET['section'] ) && 'paypal_commerce' === $_GET['section'] ) { /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		PayPal\maybe_enqueue_polyfills();

		wp_localize_script(
			'edd-admin-settings',
			'eddPayPalConnectVars',
			array(
				'defaultError' => esc_html__( 'An unexpected error occurred. Please refresh the page and try again.', 'easy-digital-downloads' ),
				'isConnected'  => PayPal\has_rest_api_connection(),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_connect_scripts' );

/**
 * Forces the Cache-Control header on the PayPal Commerce settings page to send the no-store header
 * which prevents the back-forward cache (bfcache) from storing a copy of this page in local
 * cache. This helps make sure that page elements modified via AJAX and DOM manipulations aren't
 * incorrectly shown as if they never changed.
 *
 * See: https://github.com/easydigitaldownloads/EDD-Software-Licensing/issues/1346#issuecomment-382159918
 *
 * @since 3.6
 * @param array $headers An array of nocache headers.
 *
 * @return array
 */
function _bfcache_buster( $headers ) {
	if ( ! is_admin() ) {
		return $headers;
	}

	if ( edd_is_admin_page( 'settings' ) && isset( $_GET['section'] ) && 'paypal_commerce' === $_GET['section'] ) { /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$headers['Cache-Control'] = 'no-cache, must-revalidate, max-age=0, no-store';
	}

	return $headers;
}
add_filter( 'nocache_headers', __NAMESPACE__ . '\_bfcache_buster', 10, 1 );
