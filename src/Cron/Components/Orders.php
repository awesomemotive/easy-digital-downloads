<?php
/**
 * Handles the Order cron events.
 *
 * @package EDD
 * @subpackage Cron/Components
 * @since 3.3.0
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Orders Class
 *
 * @since 3.3.0
 */
class Orders extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'orders';

	/**
	 * Register the event to run.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_weekly_scheduled_events'          => 'mark_abandoned_orders',
			'edd/discounts/release_expired_claims' => 'release_expired_discount_claims',
		);
	}

	/**
	 * Mark any orders over a week old as abandoned.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function mark_abandoned_orders() {
		// Get EDD orders over a week old that are pending.
		$before_date = new \DateTime( '-1 week', new \DateTimeZone( 'UTC' ) );
		$orders      = edd_get_orders(
			array(
				'status'             => 'pending',
				'type'               => 'sale',
				'number'             => 9999999,
				'date_created_query' => array(
					'before'    => $before_date->format( 'Y-m-d H:i:s' ),
					'inclusive' => false,
				),
				'fields'             => array( 'id', 'status' ),
			)
		);

		if ( $orders ) {
			foreach ( $orders as $order ) {
				// Just to make sure, only update orders that are pending.
				if ( 'pending' === $order->status ) {
					edd_update_order_status( $order->id, 'abandoned' );
				}
			}
		}
	}

	/**
	 * Gives back discount uses held by unpaid PayPal orders left pending for over an hour.
	 *
	 * @since 3.7.2
	 *
	 * @return void
	 */
	public function release_expired_discount_claims() {
		$before = gmdate( 'Y-m-d H:i:s', time() - \EDD\Discounts\Claims::STALE_AGE );

		// Each pass releases the orders it finds, so the next pass finds the following batch.
		for ( $pass = 0; $pass < 20; $pass++ ) {
			$order_ids = \EDD\Discounts\Claims::get_releasable_orders( $before );

			if ( empty( $order_ids ) ) {
				break;
			}

			foreach ( $order_ids as $order_id ) {
				\EDD\Discounts\Claims::release( $order_id, 'pending' );
			}
		}
	}
}
