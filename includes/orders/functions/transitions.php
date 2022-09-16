<?php
/**
 * Order Transition Functions.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2022, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Record order status change
 *
 * @since 3.0
 * @param string $old_status the status of the order prior to this change.
 * @param string $new_status The new order status.
 * @param int    $order_id the ID number of the order.
 * @return void
 */
function edd_record_order_status_change( $old_status, $new_status, $order_id ) {

	// Get the list of statuses so that status in the payment note can be translated.
	$stati      = edd_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf(
		/* translators: %1$s Old order status. %2$s New order status. */
		__( 'Status changed from %1$s to %2$s', 'easy-digital-downloads' ),
		$old_status,
		$new_status
	);

	edd_insert_payment_note( $order_id, $status_change );
}
add_action( 'edd_transition_order_status', 'edd_record_order_status_change', 100, 3 );

/**
 * Triggers `edd_update_payment_status` hook when an order status changes
 * for backwards compatibility.
 *
 * @since 3.0
 * @param string $old_status the status of the order prior to this change.
 * @param string $new_status The new order status.
 * @param int    $order_id the ID number of the order.
 * @return void
 */
add_action( 'edd_transition_order_status', function( $old_status, $new_status, $order_id ) {
	// Trigger the payment status action hook for backwards compatibility.
	do_action( 'edd_update_payment_status', $order_id, $new_status, $old_status );
	if ( 'complete' === $old_status ) {
		// Trigger the action again to account for add-ons listening for status changes from "publish".
		do_action( 'edd_update_payment_status', $order_id, $new_status, 'publish' );
	}
}, 10, 3 );
