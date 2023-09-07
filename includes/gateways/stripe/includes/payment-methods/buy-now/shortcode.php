<?php
/**
 * Buy Now: Shortcode
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Sets the stripe-checkout parameter if the direct parameter is present in the [purchase_link] short code.
 *
 * @since 2.0
 *
 * @param array $out
 * @param array $pairs
 * @param array $atts
 * @return array
 */
function edd_stripe_purchase_link_shortcode_atts( $out, $pairs, $atts ) {

	if ( ! edds_buy_now_is_enabled() ) {
		return $out;
	}

	$direct = false;

	// [purchase_link direct=true]
	if ( isset( $atts['direct'] ) && true === edds_truthy_to_bool( $atts['direct'] ) ) {
		$direct = true;

	// [purchase_link stripe-checkout]
	} else if ( isset( $atts['stripe-checkout'] ) || false !== array_search( 'stripe-checkout', $atts, true ) ) {
		$direct = true;
	}

	$out['direct'] = $direct;

	if ( true === $direct ) {
		$out['stripe-checkout'] = $direct;
	} else {
		unset( $out['stripe-checkout'] );
	}

	return $out;
}
add_filter( 'shortcode_atts_purchase_link', 'edd_stripe_purchase_link_shortcode_atts', 10, 3 );

/**
 * Sets the stripe-checkout parameter if the direct parameter is present in edd_get_purchase_link()
 *
 * @since 2.0
 * @since 2.8.0 Adds `.edds-buy-now` to the class list.
 *
 * @param array $arg Purchase link shortcode attributes.
 * @return array
 */
function edd_stripe_purchase_link_atts( $args ) {
	global $edds_has_buy_now;

	if ( ! edds_buy_now_is_enabled() ) {
		return $args;
	}

	// Don't use modal if "Free Downloads" is active and available for this download.
	// https://easydigitaldownloads.com/downloads/free-downloads/
	if ( function_exists( 'edd_free_downloads_use_modal' ) ) {
		if ( edd_free_downloads_use_modal( $args['download_id'] ) && ! edd_has_variable_prices( $args['download_id'] ) ) {
			return $args;
		}
	}

	$direct = edds_truthy_to_bool( $args['direct'] );

	$args['direct'] = $direct;

	if ( true === $direct ) {
		$args['stripe-checkout'] = true;
		$args['class']          .= ' edds-buy-now';

		if ( false === edd_item_in_cart( $args['download_id'] ) ) {
			$edds_has_buy_now = $direct;
		}
	} else {
		unset( $args['stripe-checkout'] );
	}

	return $args;
}
add_filter( 'edd_purchase_link_args', 'edd_stripe_purchase_link_atts', 10 );
