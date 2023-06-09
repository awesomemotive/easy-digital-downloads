<?php
/**
 * Recalculation functions for downloads.
 * @package     EDD
 * @subpackage  Downloads
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * The hook is registered here because it's specifically for the cron job,
 * but it calls the general download recalculation function defined in download-functions.php.
 *
 * @since 3.1
 */
add_action( 'edd_recalculate_download_sales_earnings_deferred', 'edd_recalculate_download_sales_earnings' );

/**
 * Handles scheduling the recalculation for a download.
 * If cron is not disabled, the download's sales and earnings data will be recalculated five minutes after this runs.
 * Note: recalculating download stats is an expensive query, so it's deferred intentionally.
 *
 * @since 3.1
 * @param int $download_id
 * @return void
 */
function edd_maybe_schedule_download_recalculation( $download_id ) {
	$is_scheduled = wp_next_scheduled( 'edd_recalculate_download_sales_earnings_deferred', array( $download_id ) );
	$bypass_cron  = apply_filters( 'edd_recalculate_bypass_cron', false );

	// Check if the recalculation has already been scheduled.
	if ( $is_scheduled && ! $bypass_cron ) {
		edd_debug_log( 'Recalculation is already scheduled for product ' . $download_id . ' at ' . edd_date_i18n( $is_scheduled, 'datetime' ) );
		return;
	}

	// If we are intentionally bypassing cron somehow, recalculate now and return.
	if ( $bypass_cron || ( defined( 'EDD_DOING_TESTS' ) && EDD_DOING_TESTS ) || ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
		edd_recalculate_download_sales_earnings( $download_id );
		return;
	}

	edd_debug_log( 'Scheduling recalculation for product ' . $download_id );
	wp_schedule_single_event(
		time() + ( 5 * MINUTE_IN_SECONDS ),
		'edd_recalculate_download_sales_earnings_deferred',
		array( $download_id )
	);
}

add_action( 'edd_order_item_added', 'edd_recalculate_order_item_download', 10, 2 );
add_action( 'edd_order_item_updated', 'edd_recalculate_order_item_download', 10, 3 );
add_action( 'edd_order_item_deleted', 'edd_recalculate_order_item_download' );
/**
 * Attempts to schedule download sales and earnings when order items are changed.
 *
 * @since 3.1
 * @param int $order_item_id                              The order item ID being added, updated, or deleted.
 * @param array $data                                     The array of order item data being added or updated.
 * @param bool\EDD\Orders\Order_Item $previous_order_item When updating an order, this is the original order item object.
 * @return void
 */
function edd_recalculate_order_item_download( $order_item_id, $data = array(), $previous_order_item = false ) {

	if ( get_option( '_edd_v30_doing_order_migration', false ) ) {
		return;
	}

	// Recalculations do not need to run when the order item is first being added to the database if it's pending.
	if ( 'edd_order_item_added' === current_action() && ( empty( $data['status'] ) || 'pending' === $data['status'] ) ) {
		return;
	}

	// If the order item data being updated doesn't affect sales/earnings, recalculations do not need to be run.
	if ( $previous_order_item instanceof EDD\Orders\Order_Item ) {
		$columns_affecting_stats = array( 'status', 'quantity', 'total', 'subtotal', 'discount', 'tax', 'rate', 'product_id' );

		// If the data being updated isn't one of these columns then we don't need to recalculate.
		if ( empty( array_intersect( array_keys( $data ), $columns_affecting_stats ) ) ) {
			return;
		}

		// If the data exists but matches, we don't need to recalculate.
		if (
			( empty( $data['status'] ) || $previous_order_item->status === $data['status'] ) &&
			( empty( $data['quantity'] ) || $previous_order_item->quantity === $data['quantity'] ) &&
			( ! isset( $data['total'] ) || $previous_order_item->total == $data['total'] ) &&
			( ! isset( $data['subtotal'] ) || $previous_order_item->subtotal == $data['subtotal'] ) &&
			( ! isset( $data['discount'] ) || $previous_order_item->discount == $data['discount'] ) &&
			( ! isset( $data['tax'] ) || $previous_order_item->tax == $data['tax'] ) &&
			( ! isset( $data['rate'] ) || $previous_order_item->rate == $data['rate'] ) &&
			( empty( $data['product_id'] ) || $previous_order_item->product_id == $data['product_id'] )
			) {
			return;
		}

		// Recalculate the previous product values if the product ID has changed.
		if ( ! empty( $data['product_id'] ) && $previous_order_item->product_id != $data['product_id'] ) {
			edd_maybe_schedule_download_recalculation( $previous_order_item->product_id );
		}
	}

	$order_item = edd_get_order_item( $order_item_id );
	if ( empty( $order_item->product_id ) ) {
		return;
	}

	edd_maybe_schedule_download_recalculation( $order_item->product_id );
}

add_action( 'edd_order_adjustment_added', 'edd_recalculate_order_adjustment_download', 10, 2 );
add_action( 'edd_order_adjustment_updated', 'edd_recalculate_order_adjustment_download', 10, 3 );
/**
 * Attempts to reschedule download recalculations when an order adjustment is added or updated.
 *
 * @since 3.1
 * @param int                              $order_adjustment_id       The order adjustment ID.
 * @param array                            $data                      The array of data for the new/updated order adjustment.
 * @param bool|EDD\Orders\Order_Adjustment $previous_order_adjustment The previous order adjustment object.
 * @return void
 */
function edd_recalculate_order_adjustment_download( $order_adjustment_id, $data = array(), $previous_order_adjustment = false ) {
	if ( get_option( '_edd_v30_doing_order_migration', false ) ) {
		return;
	}

	if ( $previous_order_adjustment instanceof EDD\Orders\Order_Adjustment ) {
		$columns_affecting_stats = array( 'total', 'subtotal', 'object_id', 'object_type' );

		// If the data being updated isn't one of these columns then we don't need to recalculate.
		if ( empty( array_intersect( array_keys( $data ), $columns_affecting_stats ) ) ) {
			return;
		}

		// If the data exists but matches, we don't need to recalculate.
		if (
			( ! isset( $data['total'] ) || $previous_order_adjustment->total == $data['total'] ) &&
			( ! isset( $data['subtotal'] ) || $previous_order_adjustment->subtotal == $data['subtotal'] ) &&
			( empty( $data['object_id'] ) || $previous_order_adjustment->object_id == $data['object_id'] ) &&
			( empty( $data['object_type'] ) || $previous_order_adjustment->object_type == $data['object_type'] )
			) {
			return;
		}
	}

	$order_adjustment = edd_get_order_adjustment( $order_adjustment_id );
	if ( empty( $order_adjustment->object_type ) || 'order_item' !== $order_adjustment->object_type ) {
		return;
	}

	$order_item = edd_get_order_item( $order_adjustment->object_id );
	if ( ! empty( $order_item->product_id ) ) {
		edd_maybe_schedule_download_recalculation( $order_item->product_id );
	}
}
