<?php
/**
 * Payment Request Button: Shortcode
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Allows `payment-request` to be passed to [purchase-link]
 * to disable the Payment Request button.
 *
 * @since 2.8.0
 *
 * @param array $out Sanitized shortcode attributes.
 * @param array $pairs Entire list of supported attributes and their defaults.
 * @param array $atts User defined attributes in shortcode tag.
 * @return array Combined and filtered attribute list.
 */
function edds_prb_shortcode_atts( $out, $pairs, $atts ) {
	if ( false === edd_is_gateway_active( 'stripe' ) ) {
		return $out;
	}

	if ( isset( $atts['payment-request'] ) ) {
		$out['payment-request'] = $atts['payment-request'];
	}

	return $out;
}
add_filter( 'shortcode_atts_purchase_link', 'edds_prb_shortcode_atts', 10, 3 );
