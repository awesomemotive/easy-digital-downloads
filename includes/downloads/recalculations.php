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
 * @since 3.2.0 updated to use the Recalculations class.
 * @param int           $order_item_id       The order item ID being added, updated, or deleted.
 * @param array         $data                The array of order item data being added or updated.
 * @param bool|stdClass $previous_order_item When updating an order, this is the original order item object.
 * @return void
 */
function edd_recalculate_order_item_download( $order_item_id, $data = array(), $previous_order_item = false ) {
	$recalculations = new EDD\Downloads\Recalculations();
	$recalculations->recalculate_order_item( $order_item_id, $data, $previous_order_item );
}

add_action( 'edd_order_adjustment_added', 'edd_recalculate_order_adjustment_download', 10, 2 );
add_action( 'edd_order_adjustment_updated', 'edd_recalculate_order_adjustment_download', 10, 3 );
/**
 * Attempts to reschedule download recalculations when an order adjustment is added or updated.
 *
 * @since 3.1
 * @since 3.2.0 updated to use the Recalculations class.
 * @param int           $order_adjustment_id       The order adjustment ID.
 * @param array         $data                      The array of data for the new/updated order adjustment.
 * @param bool|stdClass $previous_order_adjustment The previous order adjustment object.
 * @return void
 */
function edd_recalculate_order_adjustment_download( $order_adjustment_id, $data = array(), $previous_order_adjustment = false ) {
	$recalculations = new EDD\Downloads\Recalculations();
	$recalculations->recalculate_order_adjustment( $order_adjustment_id, $data, $previous_order_adjustment );
}
