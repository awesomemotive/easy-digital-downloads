<?php
/**
 * Store Earnings Recalculation Tests.
 *
 * @group edd_orders
 * @group edd_cron
 */
namespace EDD\Tests\Orders;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Orders\Recalculations;
use EDD\Cron\Events\SingleEvent;
use EDD\Cron\Schedulers\Handler;

class StoreEarningsRecalculation extends EDD_UnitTestCase {

	/**
	 * Store earnings aggregate queries captured during a test.
	 *
	 * @var array
	 */
	private $earnings_queries = array();

	/**
	 * Rebuild action a test moved to in-progress, which unscheduling does not remove.
	 *
	 * @var int
	 */
	private $running_action_id = 0;

	public function set_up() {
		parent::set_up();

		Handler::get_scheduler()->unschedule_all( Recalculations::STORE_EARNINGS_HOOK );
		$this->earnings_queries  = array();
		$this->running_action_id = 0;
	}

	public function tear_down() {
		remove_filter( 'query', array( $this, 'capture_earnings_query' ) );
		Handler::get_scheduler()->unschedule_all( Recalculations::STORE_EARNINGS_HOOK );
		if ( $this->running_action_id ) {
			\ActionScheduler_Store::instance()->delete_action( $this->running_action_id );
		}
		delete_transient( 'edd_earnings_total' );
		delete_transient( 'edd_earnings_total_without_tax' );

		parent::tear_down();
	}

	public function test_completing_order_does_not_run_store_earnings_aggregate() {
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		add_filter( 'query', array( $this, 'capture_earnings_query' ) );
		edd_update_order_status( $order_id, 'complete' );

		$this->assertEmpty( $this->earnings_queries );
	}

	public function test_completing_order_invalidates_store_earnings_cache() {
		set_transient( 'edd_earnings_total', 1, DAY_IN_SECONDS );
		set_transient( 'edd_earnings_total_without_tax', 1, DAY_IN_SECONDS );
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertFalse( get_transient( 'edd_earnings_total' ) );
		$this->assertFalse( get_transient( 'edd_earnings_total_without_tax' ) );
	}

	public function test_completing_order_schedules_store_earnings_rebuild() {
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertNotEmpty( SingleEvent::next_scheduled( Recalculations::STORE_EARNINGS_HOOK ) );
	}

	public function test_completing_second_order_queues_no_second_rebuild() {
		$this->require_action_scheduler();
		$first_order_id  = parent::edd()->order->create( array( 'status' => 'pending' ) );
		$second_order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $first_order_id, 'complete' );
		edd_update_order_status( $second_order_id, 'complete' );

		$this->assertSame( 1, $this->count_pending_rebuilds() );
	}

	public function test_completing_order_during_rebuild_queues_another_rebuild() {
		$this->require_action_scheduler();
		Recalculations::schedule_store_earnings();
		$this->running_action_id = (int) current( $this->get_pending_rebuild_ids() );
		\ActionScheduler_Store::instance()->log_execution( $this->running_action_id );
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		edd_update_order_status( $order_id, 'complete' );

		$this->assertSame( 1, $this->count_pending_rebuilds() );
	}

	public function test_refunding_order_invalidates_store_earnings_cache() {
		$order_id = parent::edd()->order->create();
		set_transient( 'edd_earnings_total', 1, DAY_IN_SECONDS );
		set_transient( 'edd_earnings_total_without_tax', 1, DAY_IN_SECONDS );

		edd_refund_order( $order_id );

		$this->assertFalse( get_transient( 'edd_earnings_total' ) );
		$this->assertFalse( get_transient( 'edd_earnings_total_without_tax' ) );
	}

	public function test_refunding_order_schedules_store_earnings_rebuild() {
		$order_id = parent::edd()->order->create();
		Handler::get_scheduler()->unschedule_all( Recalculations::STORE_EARNINGS_HOOK );

		edd_refund_order( $order_id );

		$this->assertNotEmpty( SingleEvent::next_scheduled( Recalculations::STORE_EARNINGS_HOOK ) );
	}

	public function test_rebuild_event_warms_both_earnings_caches() {
		parent::edd()->order->create();
		set_transient( 'edd_earnings_total', 1, DAY_IN_SECONDS );
		set_transient( 'edd_earnings_total_without_tax', 1, DAY_IN_SECONDS );

		do_action( Recalculations::STORE_EARNINGS_HOOK );

		$this->assertEquals( 120.0, (float) get_transient( 'edd_earnings_total' ) );
		$this->assertEquals( 95.0, (float) get_transient( 'edd_earnings_total_without_tax' ) );
	}

	/**
	 * Records the store-wide earnings aggregate run by edd_get_total_earnings().
	 *
	 * @param string $query The SQL query.
	 * @return string
	 */
	public function capture_earnings_query( $query ) {
		if ( false !== stripos( $query, 'SUM((total)' ) ) {
			$this->earnings_queries[] = $query;
		}

		return $query;
	}

	/**
	 * Skips a test that depends on Action Scheduler's pending and in-progress statuses.
	 *
	 * @return void
	 */
	private function require_action_scheduler() {
		if ( 'action-scheduler' !== Handler::get_active_scheduler_name() ) {
			$this->markTestSkipped( 'Action Scheduler is not the active scheduler.' );
		}
	}

	/**
	 * Gets the IDs of the pending store earnings rebuilds.
	 *
	 * @return int[]
	 */
	private function get_pending_rebuild_ids() {
		return Handler::get_scheduler()->search(
			array(
				'hook'     => Recalculations::STORE_EARNINGS_HOOK,
				'status'   => \ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 10,
			),
			'ids'
		);
	}

	/**
	 * Counts the pending store earnings rebuilds.
	 *
	 * @return int
	 */
	private function count_pending_rebuilds() {
		return count( $this->get_pending_rebuild_ids() );
	}
}
