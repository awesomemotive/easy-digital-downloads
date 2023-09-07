<?php
/**
 * Order Dispute Functions
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Records an order dispute by changing the order status to on hold
 * and adding the dispute ID and reason to the order meta.
 *
 * @since 3.2.0
 * @param int          $order_id   The ID of the order to add the dispute ID to.
 * @param string       $dispute_id The ID of the dispute.
 * @param string|array $reason     The reason for the dispute.
 * @return void
 */
function edd_record_order_dispute( $order_id, $dispute_id = '', $reason = '' ) {

	edd_update_order_status( $order_id, 'on_hold' );
	if ( ! empty( $dispute_id ) ) {
		edd_update_order_meta( $order_id, 'dispute_id', sanitize_text_field( $dispute_id ) );
	}

	if ( ! empty( $reason ) ) {
		$reason = is_array( $reason ) ? array_map( 'sanitize_text_field', $reason ) : sanitize_text_field( $reason );
		edd_update_order_meta( $order_id, 'hold_reason', $reason );
	}
}

/**
 * Gets the dispute ID for an order.
 *
 * @since 3.2.0
 * @param int $order_id The ID of the order for which to get the dispute ID.
 * @return string|false The dispute ID.
 */
function edd_get_order_dispute_id( $order_id ) {
	return edd_get_order_meta( $order_id, 'dispute_id', true );
}

/**
 * Gets the hold reason for an order.
 *
 * @since 3.2.0
 * @param int $order_id The ID of the order for which to get the hold reason.
 * @return string|array|false The hold reason.
 */
function edd_get_order_hold_reason( $order_id ) {
	return edd_get_order_meta( $order_id, 'hold_reason', true );
}

/**
 * Gets the label for a hold reason.
 *
 * @since 3.2.0
 * @param string $reason The reason for the hold.
 * @return string The label for the hold reason.
 */
function edd_get_order_hold_reason_label( $reason ) {
	$reasons = apply_filters( 'edd_order_hold_reasons', array() );

	return isset( $reasons[ $reason ] ) ? $reasons[ $reason ] : $reason;
}
