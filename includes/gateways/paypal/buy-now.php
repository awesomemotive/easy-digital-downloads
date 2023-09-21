<?php
/**
 * PayPal Commerce "Buy Now" Buttons
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 */

namespace EDD\Gateways\PayPal;

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
 * Sets the gateway to `paypal_commerce` when building straight to gateway data.
 * This is technically already set via `edd_build_straight_to_gateway_data()` but we want
 * to make 100% sure we override PayPal Standard when PayPal Commerce is enabled.
 *
 * @param array $purchase_data
 *
 * @since 2.11
 * @return array
 */
function straight_to_gateway_data( $purchase_data ) {
	if ( is_buy_now_enabled() ) {
		$_REQUEST['edd-gateway']  = 'paypal_commerce';
		$purchase_data['gateway'] = 'paypal_commerce';
	}

	return $purchase_data;
}

add_filter( 'edd_straight_to_gateway_purchase_data', __NAMESPACE__ . '\straight_to_gateway_data' );

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

	$download = new \EDD_Download( $args['download_id'] );
	if ( empty( $download ) ) {
		return $args;
	}

	// Use the Download Class to determine if it supports Buy Now.
	$price_id         = is_numeric( $args['price_id'] ) ? absint( $args['price_id'] ) : null;
	$supports_buy_now = $download->supports_buy_now( $price_id );

	if ( false === $supports_buy_now ) {
		return $args;
	}

	if ( ! empty( $args['direct'] ) ) {
		$args['class'] .= ' edd-paypal-checkout-buy-now';
	}

	return $args;
}

add_filter( 'edd_purchase_link_args', __NAMESPACE__ . '\maybe_add_purchase_link_class' );

/**
 * Registers PayPal Commerce JavaScript if using "direct" buy now links.
 *
 * @param int   $download_id ID of the download.
 * @param array $args        Purchase link arguments.
 *
 * @since 2.11
 */
function maybe_enable_buy_now_js( $download_id, $args ) {
	if ( empty( $args['direct'] ) || ! is_buy_now_enabled() ) {
		return;
	}

	$download = new \EDD_Download( $download_id );
	if ( empty( $download ) ) {
		return;
	}

	// Use the Download Class to determine if it supports Buy Now.
	$price_id         = is_numeric( $args['price_id'] ) ? absint( $args['price_id'] ) : null;
	$supports_buy_now = $download->supports_buy_now( $price_id );

	if ( false === $supports_buy_now ) {
		return;
	}

	register_js( true );
	$timestamp = time();
	?>
	<input type="hidden" name="edd_process_paypal_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_process_paypal' ) ); ?>">
	<input type="hidden" name="edd-process-paypal-token" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>" />
	<?php
}

add_action( 'edd_purchase_link_end', __NAMESPACE__ . '\maybe_enable_buy_now_js', 10, 2 );
