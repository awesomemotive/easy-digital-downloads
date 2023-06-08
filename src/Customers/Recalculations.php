<?php
/**
 * Handles scheduling recalculations for a customer.
 */
namespace EDD\Customers;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

class Recalculations implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_order_added'                   => array( 'maybe_schedule_recalculation', 10, 2 ),
			'edd_order_updated'                 => array( 'maybe_schedule_recalculation', 10, 3 ),
			'edd_order_deleted'                 => 'maybe_schedule_recalculation',
			'edd_recalculate_customer_deferred' => 'recalculate',
		);
	}

	/**
	 * When an order is added, updated, or changed, the customer stats may need to be recalculated.
	 *
	 * @param int   $order_id                       The order ID.
	 * @param array $data                           The array of order data.
	 * @param bool|EDD\Orders|Order $previous_order The previous order object (when updating).
	 * @return void
	 */
	public function maybe_schedule_recalculation( $order_id, $data = array(), $previous_order = false ) {

		if ( get_option( '_edd_v30_doing_order_migration', false ) ) {
			return;
		}

		// Recalculations do not need to run when the order item is first being added to the database if it's pending.
		if ( 'edd_order_added' === current_action() && ( empty( $data['status'] ) || 'pending' === $data['status'] ) ) {
			return;
		}

		// If the order item data being updated doesn't affect sales/earnings, recalculations do not need to be run.
		if ( $previous_order instanceof EDD\Orders\Order ) {
			$columns_affecting_stats = array( 'status', 'total', 'subtotal', 'discount', 'tax', 'rate', 'customer_id' );

			// If the data being updated isn't one of these columns then we don't need to recalculate.
			if ( empty( array_intersect( array_keys( $data ), $columns_affecting_stats ) ) ) {
				return;
			}

			// If the data exists but matches, we don't need to recalculate.
			if (
			( empty( $data['status'] ) || $previous_order->status === $data['status'] ) &&
			( ! isset( $data['total'] ) || $previous_order->total == $data['total'] ) &&
			( ! isset( $data['subtotal'] ) || $previous_order->subtotal == $data['subtotal'] ) &&
			( ! isset( $data['discount'] ) || $previous_order->discount == $data['discount'] ) &&
			( ! isset( $data['tax'] ) || $previous_order->tax == $data['tax'] ) &&
			( ! isset( $data['rate'] ) || $previous_order->rate == $data['rate'] ) &&
			( empty( $data['customer_id'] ) || $previous_order->customer_id == $data['customer_id'] )
			) {
				return;
			}

			// Recalculate the previous product values if the product ID has changed.
			if ( ! empty( $data['customer_id'] ) && $previous_order->customer_id != $data['customer_id'] ) {
				$this->schedule_recalculation( $previous_order->customer_id );
			}
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order->customer_id ) ) {
			return;
		}

		$this->schedule_recalculation( $order->customer_id );
	}

	/**
	 * Recalculate the value of a customer.
	 *
	 * @since 3.1.1.4
	 * @param int $customer_id
	 * @return void
	 */
	public function recalculate( $customer_id ) {
		$customer = edd_get_customer( $customer_id );
		if ( ! $customer instanceof \EDD_Customer ) {
			return;
		}
		$customer->recalculate_stats();
	}

	/**
	 * Maybe schedule the customer recalculation--it will be skipped if already scheduled.
	 *
	 * @since 3.1.1.4
	 * @param int $customer_id
	 * @return void
	 */
	private function schedule_recalculation( $customer_id ) {
		$is_scheduled = wp_next_scheduled( 'edd_recalculate_customer_deferred', array( $customer_id ) );
		$bypass_cron  = apply_filters( 'edd_recalculate_bypass_cron', false );

		// Check if the recalculation has already been scheduled.
		if ( $is_scheduled && ! $bypass_cron ) {
			edd_debug_log( 'Recalculation is already scheduled for customer ' . $customer_id . ' at ' . edd_date_i18n( $is_scheduled, 'datetime' ) );
			return;
		}

		// If we are intentionally bypassing cron somehow, recalculate now and return.
		if ( $bypass_cron || ( defined( 'EDD_DOING_TESTS' ) && EDD_DOING_TESTS ) || ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
			$this->recalculate( $customer_id );
			return;
		}

		edd_debug_log( 'Scheduling recalculation for customer ' . $customer_id );
		wp_schedule_single_event(
			time() + ( 5 * MINUTE_IN_SECONDS ),
			'edd_recalculate_customer_deferred',
			array( $customer_id )
		);
	}
}
