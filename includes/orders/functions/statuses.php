<?php
/**
 * Order Status Functions.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2022, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Get the order status array keys that can be used to run reporting related to gross reporting.
 *
 * @since 3.0
 *
 * @return array An array of order status array keys that can be related to gross reporting.
 */
function edd_get_gross_order_statuses() {
	$statuses = array(
		'complete',
		'refunded',
		'partially_refunded',
		'revoked',
		'on_hold',
	);

	/**
	 * Statuses that affect gross order statistics.
	 *
	 * This filter allows extensions and developers to alter the statuses that can affect the reporting of gross
	 * sales statistics.
	 *
	 * @since 3.0
	 *
	 * @param array $statuses {
	 *     An array of order status array keys.
	 */
	return apply_filters( 'edd_gross_order_statuses', $statuses );
}

/**
 * Get the order status array keys that can be used to run reporting related to net reporting.
 *
 * @since 3.0
 *
 * @return array An array of order status array keys that can be related to net reporting.
 */
function edd_get_net_order_statuses() {
	$statuses = array(
		'complete',
		'partially_refunded',
		'revoked',
		'on_hold',
	);

	/**
	 * Statuses that affect net order statistics.
	 *
	 * This filter allows extensions and developers to alter the statuses that can affect the reporting of net
	 * sales statistics.
	 *
	 * @since 3.0
	 *
	 * @param array $statuses {
	 *     An array of order status array keys.
	 */
	return apply_filters( 'edd_net_order_statuses', $statuses );
}

/**
 * Get the order status array keys which are considered recoverable.
 *
 * @since 3.0
 * @param bool $include_labels Whether to return a multidimensional array including status labels.
 * @return array An array of order status keys which are considered recoverable.
 */
function edd_recoverable_order_statuses( $include_labels = false ) {
	$statuses = array( 'pending', 'abandoned', 'failed' );

	/**
	 * Order statuses which are considered recoverable.
	 *
	 * @param $statuses {
	 *        An array of order status array keys.
	 * }
	 */
	$statuses = apply_filters( 'edd_recoverable_payment_statuses', $statuses );

	return $include_labels ? array_intersect_key( edd_get_payment_statuses(), array_flip( $statuses ) ) : $statuses;
}

/**
 * Get the order status keys which are considered complete.
 *
 * @since 3.0
 * @param bool $include_labels Whether to return a multidimensional array including status labels.
 * @return array An array of order status keys which are considered completed.
 */
function edd_get_complete_order_statuses( $include_labels = false ) {
	$statuses = array( 'publish', 'complete', 'completed', 'partially_refunded', 'revoked', 'refunded' );

	/**
	 * Order statuses which are considered completed or at their final state.
	 *
	 * @param $statuses {
	 *        An array of order status array keys.
	 * }
	 */
	$statuses = apply_filters( 'edd_complete_order_statuses', $statuses );

	return $include_labels ? array_intersect_key( edd_get_payment_statuses(), array_flip( $statuses ) ) : $statuses;
}

/**
 * Get the order status array keys which are considered incomplete.
 *
 * @since 3.0
 * @param bool $include_labels Whether to return a multidimensional array including status labels.
 * @return array An array of order status keys which are considered incomplete.
 */
function edd_get_incomplete_order_statuses( $include_labels = false ) {
	$statuses = array( 'pending', 'abandoned', 'processing', 'failed', 'cancelled' );

	/**
	 * Filters the payment key
	 * Order statuses which are considered incomplete.
	 *
	 * @param $statuses {
	 *        An array of order status array keys.
	 * }
	 */
	$statuses = apply_filters( 'edd_incomplete_order_statuses', $statuses );

	return $include_labels ? array_intersect_key( edd_get_payment_statuses(), array_flip( $statuses ) ) : $statuses;
}

/**
 * Returns an array of order statuses that support refunds.
 *
 * @since 3.0
 * @return array
 */
function edd_get_refundable_order_statuses() {
	$refundable_order_statuses = array( 'complete', 'publish', 'partially_refunded', 'on_hold' );

	/**
	 * Filters the order statuses that are allowed to be refunded.
	 *
	 * @param array $refundable_order_statuses
	 *
	 * @since 3.0
	 */
	return (array) apply_filters( 'edd_refundable_order_statuses', $refundable_order_statuses );
}

/**
 * Returns an array of order item statuses that allow assets to be delivvered.
 *
 * @since 3.0
 * @return array
 */
function edd_get_deliverable_order_item_statuses() {
	$deliverable_order_item_statuses = array( 'complete', 'partially_refunded' );

	/**
	 * Filters the order item statuses that aallow assets to be delivered.
	 *
	 * @param array $refundable_order_statuses
	 *
	 * @since 3.0
	 */
	return (array) apply_filters( 'edd_deliverable_order_item_statuses', $deliverable_order_item_statuses );
}
