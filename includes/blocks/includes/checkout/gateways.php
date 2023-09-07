<?php
/**
 * Gateway functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Gateways;

defined( 'ABSPATH' ) || exit;

/**
 * Gets the accepted payment icons.
 *
 * @since 2.0
 * @return false|array
 */
function get_payment_icons() {
	$payment_icons = edd_get_option( 'accepted_cards', false );
	if ( empty( $payment_icons ) ) {
		return false;
	}

	$order = edd_get_option( 'payment_icons_order', '' );

	if ( empty( $order ) ) {
		return $payment_icons;
	}

	$order = array_flip( explode( ',', $order ) );
	$order = array_intersect_key( $order, $payment_icons );

	return array_merge( $order, $payment_icons );
}

/**
 * Outputs the payment icons for a given gateway.
 *
 * @since 2.0
 * @param array $payment_icons The array of accepted cards for the site.
 * @param array $gateway_icons The accepted icons for the specific gateway.
 * @return void
 */
function do_payment_icons( $payment_icons, $gateway_icons ) {
	echo '<div class="edd-payment-icons">';
	foreach ( $payment_icons as $key => $option ) {
		if ( ! in_array( $key, $gateway_icons, true ) ) {
			continue;
		}
		echo edd_get_payment_image( $key, $option );
	}
	echo '</div>';
}

/**
 * Gets the accepted payment icons for a gateway.
 *
 * @since 2.0
 * @param array $gateway
 * @return array|false
 */
function get_gateway_icons( $gateway_id, $gateway ) {
	if ( ! empty( $gateway['icons'] ) ) {
		return $gateway['icons'];
	}

	if ( 'stripe' === $gateway_id ) {
		return array(
			'mastercard',
			'visa',
			'discover',
			'americanexpress',
		);
	}

	if ( false !== strpos( $gateway_id, 'paypal' ) ) {
		return array( 'paypal' );
	}

	return false;
}
