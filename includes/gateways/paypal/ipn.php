<?php
/**
 * IPN Functions
 *
 * This serves as a fallback for the webhooks in the event that the app becomes disconnected.
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal\IPN;

/**
 * Listens for an IPN call from PayPal
 *
 * This is intended to be a 'backup' listener, for if the webhook is no longer connected for a specific PayPal object.
 *
 * @since 3.1.0.3
 * @since 3.2.0 Uses the new PayPal IPN class.
 */
function listen_for_ipn() {
	if ( empty( $_GET['edd-listener'] ) || 'eppe' !== $_GET['edd-listener'] ) {
		return;
	}

	new \EDD\Gateways\PayPal\IPN( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}
add_action( 'init', __NAMESPACE__ . '\listen_for_ipn' );

/**
 * Helper method to prefix any calls to edd_debug_log
 *
 * @since 3.1.0.3
 * @uses edd_debug_log
 *
 * @param string $message The message to send to the debug logging.
 */
function ipn_debug_log( $message ) {
	edd_debug_log( 'PayPal Commerce IPN: ' . $message );
}
