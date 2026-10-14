<?php
/**
 * Handles deferring the store earnings recalculation.
 *
 * @package     EDD\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Orders;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Cron\Schedulers\Handler;

/**
 * Recalculations class.
 *
 * @since 3.7.2
 */
class Recalculations implements SubscriberInterface {

	/**
	 * The cron hook that rebuilds the store earnings cache.
	 *
	 * @since 3.7.2
	 * @var string
	 */
	const STORE_EARNINGS_HOOK = 'edd/orders/recalculate_store_earnings';

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.7.2
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			self::STORE_EARNINGS_HOOK => 'recalculate_store_earnings',
		);
	}

	/**
	 * Invalidates the store earnings cache and schedules it to be rebuilt.
	 *
	 * @since 3.7.2
	 * @return void
	 */
	public static function schedule_store_earnings() {
		delete_transient( 'edd_earnings_total' );
		delete_transient( 'edd_earnings_total_without_tax' );

		self::queue_rebuild();
	}

	/**
	 * Rebuilds both store earnings caches.
	 *
	 * The forced read also refreshes the legacy `edd_earnings_total` options, which otherwise refresh only through the API or the recount tool.
	 *
	 * @since 3.7.2
	 * @return void
	 */
	public function recalculate_store_earnings() {
		edd_get_total_earnings( true, true );
		edd_get_total_earnings( false, true );
	}

	/**
	 * Queues one store earnings rebuild, unless one is already pending.
	 *
	 * @since 3.7.2
	 * @return void
	 */
	private static function queue_rebuild() {
		$scheduler = Handler::get_scheduler();
		if ( $scheduler->has_pending( self::STORE_EARNINGS_HOOK ) ) {
			return;
		}

		// A running rebuild may have read the totals before this change, so queue another behind it.
		if ( $scheduler->has_scheduled( self::STORE_EARNINGS_HOOK ) ) {
			$scheduler->enqueue_async( self::STORE_EARNINGS_HOOK );
			return;
		}

		// A cold cache is rebuilt by the next read, so never run the aggregate inline, even without cron.
		$scheduler->schedule_single( self::STORE_EARNINGS_HOOK, time() + ( 5 * MINUTE_IN_SECONDS ) );
	}
}
