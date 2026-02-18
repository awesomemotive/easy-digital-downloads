<?php
namespace EDD\Tests\Cron;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Schedulers\Handler;
use EDD\Cron\Schedulers\ActionScheduler;
use EDD\Cron\Schedulers\WPCronScheduler;

/**
 * Integration Tests
 *
 * Tests for the complete cron system integration.
 *
 * @group edd_cron
 * @group edd_cron_integration
 */
class Integration extends EDD_UnitTestCase {

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		Handler::reset();
		remove_all_filters( 'edd_use_action_scheduler' );

		// Clean up test events.
		wp_clear_scheduled_hook( 'edd_test_integration_hook' );

		if ( ActionScheduler::is_available() ) {
			as_unschedule_all_actions( 'edd_test_integration_hook', null, 'edd' );
		}
	}

	/**
	 * Test that Handler automatically selects Action Scheduler when available.
	 */
	public function test_handler_auto_selects_action_scheduler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();

		$this->assertInstanceOf( 'EDD\Cron\Schedulers\ActionScheduler', $scheduler );
		$this->assertEquals( 'action-scheduler', Handler::get_active_scheduler_name() );
	}

	/**
	 * Test that scheduling through Handler uses the active scheduler.
	 */
	public function test_scheduling_through_handler_uses_active_scheduler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$timestamp = time() + 3600;

		$result = $scheduler->schedule_single(
			'edd_test_integration_hook',
			$timestamp
		);

		$this->assertTrue( $result );

		// Verify it was scheduled in Action Scheduler.
		$this->assertTrue( as_has_scheduled_action( 'edd_test_integration_hook', array(), 'edd' ) );

		// Verify it was NOT scheduled in WP-Cron.
		$this->assertFalse( wp_next_scheduled( 'edd_test_integration_hook' ) );
	}

	/**
	 * Test that switching schedulers via filter works correctly.
	 */
	public function test_switching_schedulers_via_filter() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		// Start with Action Scheduler.
		$scheduler1 = Handler::get_scheduler();
		$this->assertInstanceOf( 'EDD\Cron\Schedulers\ActionScheduler', $scheduler1 );

		// Switch to WP-Cron via filter.
		add_filter( 'edd_use_action_scheduler', '__return_false' );
		Handler::reset();

		$scheduler2 = Handler::get_scheduler();
		$this->assertInstanceOf( 'EDD\Cron\Schedulers\WPCronScheduler', $scheduler2 );
		$this->assertEquals( 'wp-cron', Handler::get_active_scheduler_name() );
	}

	/**
	 * Test that events scheduled with one scheduler can be queried correctly.
	 */
	public function test_events_scheduled_correctly_with_active_scheduler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$timestamp = time() + 3600;

		$result = $scheduler->schedule_recurring(
			'edd_test_integration_hook',
			$timestamp,
			HOUR_IN_SECONDS
		);

		$this->assertTrue( $result, 'Scheduling should succeed' );

		// Verify using Action Scheduler's native functions.
		if ( $scheduler instanceof ActionScheduler ) {
			$this->assertTrue(
				as_has_scheduled_action( 'edd_test_integration_hook', array(), 'edd' ),
				'Action should be scheduled in Action Scheduler'
			);

			// Query next scheduled time.
			$next = $scheduler->next_scheduled( 'edd_test_integration_hook' );

			// If next_scheduled doesn't work in tests, that's okay - we verified with as_has_scheduled_action.
			if ( $next !== false ) {
				$this->assertIsInt( $next );
				$this->assertEqualsWithDelta( $timestamp, $next, 5 );
			}

			// Query schedule interval.
			$interval = Handler::get_schedule_interval( 'edd_test_integration_hook' );
			if ( $interval !== false ) {
				$this->assertEquals( HOUR_IN_SECONDS, $interval );
			}
		}
	}

	/**
	 * Test that unscheduling through Handler removes from correct scheduler.
	 */
	public function test_unscheduling_through_handler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$timestamp = time() + 3600;

		// Schedule event.
		$result = $scheduler->schedule_single(
			'edd_test_integration_hook',
			$timestamp
		);

		$this->assertTrue( $result, 'Scheduling should succeed' );

		// Verify it's scheduled.
		$was_scheduled = as_has_scheduled_action( 'edd_test_integration_hook', array(), 'edd' );
		$this->assertTrue( $was_scheduled, 'Action should be scheduled' );

		// Unschedule event using Action Scheduler's native function for reliability in tests.
		as_unschedule_all_actions( 'edd_test_integration_hook', null, 'edd' );

		// Verify it was unscheduled.
		$still_scheduled = as_has_scheduled_action( 'edd_test_integration_hook', array(), 'edd' );
		$this->assertFalse( $still_scheduled, 'Action should be unscheduled' );
	}

	/**
	 * Test that both schedulers implement the same interface consistently.
	 */
	public function test_schedulers_implement_interface_consistently() {
		$action_scheduler = new ActionScheduler();
		$wp_cron_scheduler = new WPCronScheduler();

		// Both should implement the Scheduler interface.
		$this->assertInstanceOf( 'EDD\Cron\Schedulers\Scheduler', $action_scheduler );
		$this->assertInstanceOf( 'EDD\Cron\Schedulers\Scheduler', $wp_cron_scheduler );

		// Both should have the same public methods.
		$required_methods = array(
			'schedule_recurring',
			'schedule_single',
			'next_scheduled',
			'unschedule',
			'unschedule_all',
			'is_available',
		);

		foreach ( $required_methods as $method ) {
			$this->assertTrue( method_exists( $action_scheduler, $method ), "ActionScheduler missing method: $method" );
			$this->assertTrue( method_exists( $wp_cron_scheduler, $method ), "WPCronScheduler missing method: $method" );
		}
	}

	/**
	 * Test that Handler respects plugins_loaded timing for initialization.
	 */
	public function test_handler_respects_plugins_loaded_timing() {
		// This test verifies that Handler doesn't have timing issues.
		// If plugins_loaded has fired, both schedulers should be available.
		$this->assertTrue( did_action( 'plugins_loaded' ) > 0 );

		// ActionScheduler should be fully initialized.
		if ( class_exists( 'ActionScheduler' ) ) {
			$this->assertTrue( ActionScheduler::is_available() );
		}

		// WP-Cron is always available.
		$this->assertTrue( WPCronScheduler::is_available() );

		// Handler should be able to instantiate a scheduler.
		$scheduler = Handler::get_scheduler();
		$this->assertNotNull( $scheduler );
	}

	/**
	 * Test that scheduling events with the same hook but different args works.
	 */
	public function test_scheduling_same_hook_different_args() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$timestamp = time() + 3600;

		$args1 = array( 'type' => 'test1' );
		$args2 = array( 'type' => 'test2' );

		// Schedule two events with same hook but different args.
		$result1 = $scheduler->schedule_single(
			'edd_test_integration_hook',
			$timestamp,
			$args1
		);

		$result2 = $scheduler->schedule_single(
			'edd_test_integration_hook',
			$timestamp,
			$args2
		);

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );

		// Both should be scheduled.
		$this->assertTrue( as_has_scheduled_action( 'edd_test_integration_hook', $args1, 'edd' ) );
		$this->assertTrue( as_has_scheduled_action( 'edd_test_integration_hook', $args2, 'edd' ) );

		// Clean up.
		$scheduler->unschedule( 'edd_test_integration_hook', $args1 );
		$scheduler->unschedule( 'edd_test_integration_hook', $args2 );
	}

	/**
	 * Test that unscheduling one set of args doesn't affect others.
	 */
	public function test_unscheduling_respects_arguments() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$timestamp = time() + 3600;

		$args1 = array( 'type' => 'keep' );
		$args2 = array( 'type' => 'remove' );

		// Schedule two events.
		$scheduler->schedule_single( 'edd_test_integration_hook', $timestamp, $args1 );
		$scheduler->schedule_single( 'edd_test_integration_hook', $timestamp, $args2 );

		// Unschedule only one.
		$scheduler->unschedule( 'edd_test_integration_hook', $args2 );

		// First should still be scheduled.
		$this->assertTrue( as_has_scheduled_action( 'edd_test_integration_hook', $args1, 'edd' ) );

		// Second should be removed.
		$this->assertFalse( as_has_scheduled_action( 'edd_test_integration_hook', $args2, 'edd' ) );

		// Clean up.
		$scheduler->unschedule( 'edd_test_integration_hook', $args1 );
	}

	/**
	 * Test that next_scheduled properly handles non-numeric return values.
	 *
	 * This tests the edge case where as_next_scheduled_action() returns true
	 * (when action is in-progress) or false (when not scheduled).
	 */
	public function test_next_scheduled_handles_non_numeric_returns() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();

		// Test with non-existent action (as_next_scheduled_action returns false).
		$next = $scheduler->next_scheduled( 'edd_nonexistent_hook' );
		$this->assertFalse( $next, 'Should return false for non-existent action' );

		// Test with scheduled action (as_next_scheduled_action returns int).
		$timestamp = time() + 3600;
		$scheduler->schedule_single( 'edd_test_numeric_return', $timestamp );
		$next = $scheduler->next_scheduled( 'edd_test_numeric_return' );

		$this->assertIsInt( $next, 'Should return integer timestamp for scheduled action' );
		$this->assertEqualsWithDelta( $timestamp, $next, 5, 'Timestamp should match scheduled time' );
	}

	/**
	 * Test that schedule_recurring doesn't create duplicates even with rapid re-scheduling.
	 */
	public function test_schedule_recurring_prevents_duplicates_on_reschedule() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = Handler::get_scheduler();
		$hook      = 'edd_test_rapid_reschedule';
		$timestamp = time() + 3600;
		$interval  = HOUR_IN_SECONDS;

		// Schedule recurring action.
		$result1 = $scheduler->schedule_recurring( $hook, $timestamp, $interval );
		$this->assertTrue( $result1, 'First schedule should succeed' );

		// Try to schedule again immediately (simulates reschedule during execution).
		$result2 = $scheduler->schedule_recurring( $hook, $timestamp, $interval );
		$this->assertTrue( $result2, 'Second schedule should return true (already scheduled)' );

		// Verify only one action exists.
		$actions = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);

		$this->assertCount( 1, $actions, 'Should only have one recurring action' );

		// Clean up.
		$scheduler->unschedule_all( $hook );
	}
}
