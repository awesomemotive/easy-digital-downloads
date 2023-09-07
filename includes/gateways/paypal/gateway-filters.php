<?php
/**
 * PayPal Commerce Gateway Filters
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 */

namespace EDD\Gateways\PayPal;

/**
 * Removes PayPal Standard from the list of available gateways while we're on the EDD Settings page.
 * This prevents PayPal Standard from being enabled as a gateway if:
 *
 *      - The store owner has never used PayPal Standard; or
 *      - The store Owner used PayPal Standard previously but has now been onboarded to PayPal Commerce.
 *
 * @param array $gateways
 *
 * @since 2.11
 * @return array
 */
function maybe_remove_paypal_standard( $gateways ) {
	if ( function_exists( 'edd_is_admin_page' ) && edd_is_admin_page( 'settings' ) && ! paypal_standard_enabled() ) {
		unset( $gateways['paypal'] );
	}

	// Ensures we don't show the PayPal Standard option in Site Health.
	if ( did_action( 'admin_head-site-health.php' ) && ! paypal_standard_enabled() ) {
		unset( $gateways['paypal'] );
	}

	return $gateways;
}

add_filter( 'edd_payment_gateways', __NAMESPACE__ . '\maybe_remove_paypal_standard' );

/**
 * Creates a link to the transaction within PayPal.
 *
 * @param string $transaction_id PayPal transaction ID.
 * @param int    $order_id     ID of the order.
 *
 * @since 2.11
 * @return string
 */
function link_transaction_id( $transaction_id, $order_id ) {
	if ( empty( $transaction_id ) ) {
		return $transaction_id;
	}

	$order = edd_get_order( $order_id );

	if ( ! $order ) {
		return $transaction_id;
	}

	$subdomain       = ( 'test' === $order->mode ) ? 'sandbox.' : '';
	$transaction_url = 'https://' . urlencode( $subdomain ) . 'paypal.com/activity/payment/' . urlencode( $transaction_id );

	return '<a href="' . esc_url( $transaction_url ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
}

add_filter( 'edd_payment_details_transaction_id-paypal_commerce', __NAMESPACE__ . '\link_transaction_id', 10, 2 );

/**
 * By default, EDD_Payment converts an empty transaction ID to be the ID of the payment.
 * We don't want that to happen... Empty should be empty.
 *
 * @since 2.11
 */
add_filter( 'edd_get_payment_transaction_id-paypal_commerce', '__return_false' );

/**
 * Adds a link to the dispute within PayPal.
 *
 * @since 3.2.0
 * @param string           $dispute_id
 * @param EDD\Orders\Order $order
 * @return string
 */
function link_dispute_id( $dispute_id, $order ) {
	$subdomain = 'test' === $order->mode ? 'sandbox.' : '';
	$url       = 'https://www.' . urlencode( $subdomain ) . 'paypal.com/resolutioncenter/view/' . urlencode( $dispute_id ) . '/inquiry';

	return '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $dispute_id ) . '</a>';
}
add_filter( 'edd_payment_details_dispute_id_paypal_commerce', __NAMESPACE__ . '\link_dispute_id', 10, 2 );

/**
 * Adds PayPal-specific hold reasons.
 *
 * @since 3.2.0
 * @param array $reasons
 * @return array
 */
function add_hold_reasons( $reasons ) {
	$paypal_reasons = array(
		'MERCHANDISE_OR_SERVICE_NOT_RECEIVED'     => __( 'Merchandise or service not received', 'easy-digital-downloads' ),
		'MERCHANDISE_OR_SERVICE_NOT_AS_DESCRIBED' => __( 'Merchandise or service not as described', 'easy-digital-downloads' ),
		'UNAUTHORISED'                            => __( 'Unauthorized', 'easy-digital-downloads' ),
		'ITEM_NOT_RECEIVED'                       => __( 'Item not received', 'easy-digital-downloads' ),
		'UNAUTHORIZED_TRANSACTION'                => __( 'Unauthorized transaction', 'easy-digital-downloads' ),
		'BUYER_COMPLAINT'                         => __( 'Buyer complaint', 'easy-digital-downloads' ),
	);

	return array_merge( $reasons, $paypal_reasons );
}
add_filter( 'edd_order_hold_reasons', __NAMESPACE__ . '\add_hold_reasons' );
