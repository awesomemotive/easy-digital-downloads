<?php
/**
 * PayPal Commerce "Buy Now" Buttons
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 */

namespace EDD\PayPal;

/**
 * Determines whether or not Buy Now is enabled for PayPal.
 *
 * @since 2.11
 * @return bool
 */
function is_buy_now_enabled() {
	return edd_shop_supports_buy_now() && array_key_exists( 'paypal_commerce', edd_get_enabled_payment_gateways() );
}

/**
 * Adds the `edd-paypal-checkout-buy-now` class to qualified shortcodes.
 *
 * @param array $args
 *
 * @since 2.11
 * @return array
 */
function maybe_add_purchase_link_class( $args ) {
	if ( ! is_buy_now_enabled() ) {
		return $args;
	}

	// Don't add class if "Free Downloads" is active and available for this download.
	if ( function_exists( 'edd_free_downloads_use_modal' ) ) {
		if ( edd_free_downloads_use_modal( $args['download_id'] ) && ! edd_has_variable_prices( $args['download_id'] ) ) {
			return $args;
		}
	}

	// Don't add class if Recurring is enabled for this download.
	if ( function_exists( 'edd_recurring' ) ) {
		// Overall download is recurring.
		if ( edd_recurring()::is_recurring( $args['download_id'] ) ) {
			return $args;
		}

		// Price ID is recurring.
		if ( ! empty( $args['price_id'] ) && edd_recurring()::is_price_recurring( $args['download_id'], $args['price_id'] ) ) {
			return $args;
		}
	}

	if ( ! empty( $args['direct'] ) ) {
		$args['class'] .= ' edd-paypal-checkout-buy-now';
	}

	return $args;
}

add_filter( 'edd_purchase_link_args', __NAMESPACE__ . '\maybe_add_purchase_link_class' );

function maybe_enable_buy_now_js( $download_id, $args ) {
	if ( ! empty( $args['direct'] ) && is_buy_now_enabled() ) {
		register_js( true );
		// @todo error container `edd-paypal-checkout-buy-now-error-wrapper`
	}
}

add_action( 'edd_purchase_link_end', __NAMESPACE__ . '\maybe_enable_buy_now_js', 10, 2 );
