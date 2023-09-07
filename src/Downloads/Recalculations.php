<?php
/**
 * Handles scheduling recalculations for a download.
 */
namespace EDD\Downloads;

defined( 'ABSPATH' ) || exit;

class Recalculations {
	/**
	 * Attempts to schedule download sales and earnings when order items are changed.
	 *
	 * @since 3.2.0
	 * @param int           $order_item_id       The order item ID being added, updated, or deleted.
	 * @param array         $data                The array of order item data being added or updated.
	 * @param bool|stdClass $previous_order_item When updating an order, this is the original order item object.
	 * @return void
	 */
	public function recalculate_order_item( $order_item_id, $data = array(), $previous_order_item = false ) {

		if ( get_option( '_edd_v30_doing_order_migration', false ) ) {
			return;
		}

		// Recalculations do not need to run when the order item is first being added to the database if it's pending.
		if ( 'edd_order_item_added' === current_action() && ( empty( $data['status'] ) || 'pending' === $data['status'] ) ) {
			return;
		}

		// If the order item data being updated doesn't affect sales/earnings, recalculations do not need to be run.
		if ( is_object( $previous_order_item ) ) {
			if ( ! $this->should_recalculate_from_previous( $data, $previous_order_item, array( 'status', 'quantity', 'total', 'subtotal', 'discount', 'tax', 'rate', 'product_id' ) ) ) {
				return;
			}

			// Recalculate the previous product values if the product ID has changed.
			if ( ! empty( $data['product_id'] ) && $previous_order_item->product_id != $data['product_id'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				edd_maybe_schedule_download_recalculation( $previous_order_item->product_id );
			}
		}

		$order_item = edd_get_order_item( $order_item_id );
		if ( empty( $order_item->product_id ) ) {
			return;
		}

		edd_maybe_schedule_download_recalculation( $order_item->product_id );
	}

	/**
	 * Attempts to reschedule download recalculations when an order adjustment is added or updated.
	 *
	 * @since 3.1
	 * @param int           $order_adjustment_id       The order adjustment ID.
	 * @param array         $data                      The array of data for the new/updated order adjustment.
	 * @param bool|stdClass $previous_order_adjustment The previous order adjustment object.
	 * @return void
	 */
	public function recalculate_order_adjustment( $order_adjustment_id, $data = array(), $previous_order_adjustment = false ) {
		if ( get_option( '_edd_v30_doing_order_migration', false ) ) {
			return;
		}

		if ( is_object( $previous_order_adjustment ) ) {
			if ( ! $this->should_recalculate_from_previous( $data, $previous_order_adjustment, array( 'total', 'subtotal', 'object_id', 'object_type' ) ) ) {
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

	/**
	 * Determines if a recalculation should be scheduled based on the previous order item data and the new data.
	 *
	 * @since 3.1
	 * @param array $data                  The new order item data.
	 * @param bool|mixed $previous_item The previous order item object.
	 * @param array $columns_affecting_stats The columns that affect sales/earnings.
	 * @return bool
	 */
	private function should_recalculate_from_previous( $data, $previous_item, $columns_affecting_stats ) {

		// If the data being updated isn't one of these columns then we don't need to recalculate.
		if ( empty( array_intersect( array_keys( $data ), $columns_affecting_stats ) ) ) {
			return false;
		}

		foreach ( $columns_affecting_stats as $key ) {
			if ( isset( $data[ $key ] ) && $previous_item->$key != $data[ $key ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				return true;
			}
		}

		return false;
	}
}
