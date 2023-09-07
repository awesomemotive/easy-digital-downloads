<?php

namespace EDD\Orders;

use EDD\Utils\Date;
use EDD\EventManagement\SubscriberInterface;

class DeferredActions implements SubscriberInterface {

	/**
	 * Hook into actions and filters.
	 *
	 * @since  3.2
	 * @return void
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_complete_purchase'               => array( 'schedule_deferred_actions', 10, 1 ),
			'edd_after_payment_scheduled_actions' => array( 'run_deferred_actions', 10, 1 ),
		);
	}

	/**
	 * Schedules the one time event via WP_Cron to fire after purchase actions.
	 *
	 * Is run on the edd_complete_purchase action.
	 *
	 * @since 3.2.0
	 * @param $payment_id
	 */
	public static function schedule_deferred_actions( $payment_id ) {
		edd_debug_log( 'Scheduling after order actions for order ID ' . $payment_id );

		$use_cron = apply_filters( 'edd_use_after_payment_actions', true, $payment_id );
		if ( $use_cron ) {
			$after_payment_delay = apply_filters( 'edd_after_payment_actions_delay', 30, $payment_id );

			// Use time() instead of current_time( 'timestamp' ) to avoid scheduling the event in the past when server time
			// and WordPress timezone are different.
			wp_schedule_single_event( time() + $after_payment_delay, 'edd_after_payment_scheduled_actions', array( $payment_id, false ) );
		}
	}

	/**
	 * Runs the deferred actions.
	 *
	 * Is run on the edd_after_payment_scheduled_actions action.
	 *
	 * @since 3.2.0
	 * @param $payment_id The payment ID being processed.
	 * @param bool $force If we should run these actions, even if they've been run before.
	 *
	 * @return void
	 */
	public function run_deferred_actions( $payment_id = 0, $force = false ) {
		if ( empty( $payment_id ) ) {
			return;
		}

		$order = edd_get_order( $payment_id );
		if ( ! empty( $order->date_actions_run ) && false === $force ) {
			return;
		}

		/**
		 * In the event that during the order completion process, a timeout happens,
		 * ensure that all the order items have the correct status, to match the order itself.
		 *
		 * @see https://github.com/awesomemotive/easy-digital-downloads-pro/issues/77
		 */
		$order_items = edd_get_order_items(
			array(
				'order_id'       => $payment_id,
				'status__not_in' => edd_get_deliverable_order_item_statuses(),
				'number'         => 200,
			)
		);

		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $order_item ) {
				edd_update_order_item(
					$order_item->id,
					array(
						'status' => $order->status,
					)
				);
			}
		}

		$customer = edd_get_customer( $order->customer_id );

		// If someone has hooked into the old action, we need to run it.
		$this->maybe_trigger_legacy_action( $payment_id, $customer );

		edd_debug_log( 'Running after order actions for order ID ' . $payment_id );

		/**
		 * Runs **after** a purchase is marked as "complete".
		 *
		 * @since 3.2.0
		 *
		 * @param int          $order->id The order ID.
		 * @param EDD_Order    $order     The EDD_Order object containing all order data.
		 * @param EDD_Customer $customer  The EDD_Customer object containing all customer data.
		 */
		do_action( 'edd_after_order_actions', $order->id, $order, $customer );

		// Update the order with the date the actions were run in UTC.
		$date = new Date( 'now', 'GMT' );
		edd_update_order( $order->id, array( 'date_actions_run' => $date->format( 'mysql' ) ) );

		edd_debug_log( 'After order actions completed for order ID ' . $payment_id );
	}

	/**
	 * Runs the legacy action, if it's hooked.
	 *
	 * If you are calling this hook, you should move to edd_after_order_actions, which passes in an order object instead.
	 *
	 * @since 3.2.0
	 *
	 * @param int $payment_id        The payment ID being processed.
	 * @param EDD_Customer $customer The EDD_Customer object containing all customer data.
	 */
	private function maybe_trigger_legacy_action( $payment_id, $customer ) {
		if ( has_action( 'edd_after_payment_actions' ) ) {
			$payment = edd_get_payment( $payment_id );
			/**
			 * Runs **after** a purchase is marked as "complete".
			 * This is only run if something is hooked on it.
			 *
			 * @since 2.8 - Added EDD_Payment and EDD_Customer object to action.
			 *
			 * @param int          $payment_id Payment ID.
			 * @param EDD_Payment  $payment    EDD_Payment object containing all payment data.
			 * @param EDD_Customer $customer   EDD_Customer object containing all customer data.
			 */
			do_action( 'edd_after_payment_actions', $payment_id, $payment, $customer );
		}
	}

}
