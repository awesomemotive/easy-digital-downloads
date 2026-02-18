<?php
namespace EDD\Tests\Cron;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Schedulers\WPCronScheduler as WPCronSchedulerClass;

/**
 * WPCronScheduler Tests
 *
 * Tests for the WP-Cron scheduler implementation.
 *
 * @group edd_cron
 * @group edd_cron_wp_cron
 */
class WPCronScheduler extends EDD_UnitTestCase {

	/**
	 * WPCronSchedulerClass instance.
	 *
	 * @var WPCronSchedulerClass
	 */
	protected $scheduler;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->scheduler = new WPCronSchedulerClass();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clean up any scheduled events.
		wp_clear_scheduled_hook( 'edd_test_hook' );
		wp_clear_scheduled_hook( 'edd_test_single_hook' );
	}

	/**
	 * Test that is_available always returns true.
	 */
	public function test_is_available_returns_true() {
		$this->assertTrue( WPCronSchedulerClass::is_available() );
	}

	/**
	 * Test that schedule_recurring schedules a recurring event.
	 */
	public function test_schedule_recurring() {
		$timestamp = time() + 3600;

		$result = $this->scheduler->schedule_recurring(
			'edd_test_hook',
			$timestamp,
			HOUR_IN_SECONDS
		);

		$this->assertTrue( $result );

		$next = wp_next_scheduled( 'edd_test_hook' );
		$this->assertIsInt( $next );
		$this->assertEqualsWithDelta( $timestamp, $next, 5 );
	}

	/**
	 * Test that schedule_single schedules a single event.
	 */
	public function test_schedule_single() {
		$timestamp = time() + 3600;

		$result = $this->scheduler->schedule_single(
			'edd_test_single_hook',
			$timestamp
		);

		$this->assertTrue( $result );

		$next = wp_next_scheduled( 'edd_test_single_hook' );
		$this->assertIsInt( $next );
		$this->assertEqualsWithDelta( $timestamp, $next, 5 );
	}

	/**
	 * Test that next_scheduled returns timestamp of next scheduled event.
	 */
	public function test_next_scheduled() {
		$timestamp = time() + 3600;

		wp_schedule_single_event( $timestamp, 'edd_test_hook' );

		$next = $this->scheduler->next_scheduled( 'edd_test_hook' );

		$this->assertIsInt( $next );
		$this->assertEqualsWithDelta( $timestamp, $next, 5 );
	}

	/**
	 * Test that next_scheduled returns false when nothing is scheduled.
	 */
	public function test_next_scheduled_returns_false_when_not_scheduled() {
		$next = $this->scheduler->next_scheduled( 'edd_nonexistent_hook' );

		$this->assertFalse( $next );
	}

	/**
	 * Test that unschedule removes a scheduled event.
	 */
	public function test_unschedule() {
		$timestamp = time() + 3600;

		wp_schedule_single_event( $timestamp, 'edd_test_hook' );
		$this->assertIsInt( wp_next_scheduled( 'edd_test_hook' ) );

		$result = $this->scheduler->unschedule( 'edd_test_hook' );

		$this->assertTrue( $result );
		$this->assertFalse( wp_next_scheduled( 'edd_test_hook' ) );
	}

	/**
	 * Test that unschedule_all removes all scheduled events for a hook.
	 */
	public function test_unschedule_all() {
		// Schedule multiple events.
		wp_schedule_single_event( time() + 3600, 'edd_test_hook' );
		wp_schedule_single_event( time() + 7200, 'edd_test_hook' );

		$this->assertIsInt( wp_next_scheduled( 'edd_test_hook' ) );

		$result = $this->scheduler->unschedule_all( 'edd_test_hook' );

		$this->assertTrue( $result );
		$this->assertFalse( wp_next_scheduled( 'edd_test_hook' ) );
	}

	/**
	 * Test behavioral difference between null and empty array in unschedule_all.
	 *
	 * This test verifies that WP-Cron correctly distinguishes between null (match any args)
	 * and array() (match empty args), consistent with Action Scheduler's behavior.
	 */
	public function test_unschedule_all_null_vs_empty_array() {
		$hook      = 'edd_test_args_semantics';
		$timestamp = time() + 3600;

		// Schedule actions: one with empty args, one with specific args.
		wp_schedule_single_event( $timestamp, $hook, array() );
		wp_schedule_single_event( $timestamp + 100, $hook, array( 'id' => 1 ) );

		// Verify both are scheduled.
		$this->assertIsInt( wp_next_scheduled( $hook, array() ), 'Empty-args action should be scheduled' );
		$this->assertIsInt( wp_next_scheduled( $hook, array( 'id' => 1 ) ), 'Specific-args action should be scheduled' );

		// Unschedule with array() (should remove only the empty-args action).
		$result = $this->scheduler->unschedule_all( $hook, array() );
		$this->assertTrue( $result );

		// Verify empty-args action is gone, specific-args action remains.
		$this->assertFalse( wp_next_scheduled( $hook, array() ), 'Empty-args action should be removed' );
		$this->assertIsInt( wp_next_scheduled( $hook, array( 'id' => 1 ) ), 'Specific-args action should remain' );

		// Clean up the remaining action.
		wp_unschedule_event( wp_next_scheduled( $hook, array( 'id' => 1 ) ), $hook, array( 'id' => 1 ) );

		// Now test null behavior - schedule both again.
		wp_schedule_single_event( $timestamp, $hook, array() );
		wp_schedule_single_event( $timestamp + 100, $hook, array( 'id' => 1 ) );

		// Unschedule with null (should remove ALL actions regardless of args).
		$result = $this->scheduler->unschedule_all( $hook, null );
		$this->assertTrue( $result );

		// Verify both are gone.
		$this->assertFalse( wp_next_scheduled( $hook, array() ), 'Empty-args action should be removed' );
		$this->assertFalse( wp_next_scheduled( $hook, array( 'id' => 1 ) ), 'Specific-args action should be removed' );
	}

	/**
	 * Test that scheduling with arguments works correctly.
	 */
	public function test_schedule_with_arguments() {
		$timestamp = time() + 3600;
		$args      = array( 'test_arg' => 'test_value' );

		$this->scheduler->schedule_single(
			'edd_test_hook',
			$timestamp,
			$args
		);

		$this->assertIsInt( wp_next_scheduled( 'edd_test_hook', $args ) );
		$this->assertFalse( wp_next_scheduled( 'edd_test_hook', array() ) );
	}

	/**
	 * Test that unscheduling with specific arguments only removes matching events.
	 */
	public function test_unschedule_with_arguments() {
		$timestamp = time() + 3600;
		$args1     = array( 'test_arg' => 'value1' );
		$args2     = array( 'test_arg' => 'value2' );

		wp_schedule_single_event( $timestamp, 'edd_test_hook', $args1 );
		wp_schedule_single_event( $timestamp, 'edd_test_hook', $args2 );

		$this->scheduler->unschedule( 'edd_test_hook', $args1 );

		$this->assertFalse( wp_next_scheduled( 'edd_test_hook', $args1 ) );
		$this->assertIsInt( wp_next_scheduled( 'edd_test_hook', $args2 ) );
	}

	/**
	 * Test that schedule_recurring checks for existing events.
	 */
	public function test_schedule_recurring_prevents_duplicates() {
		$timestamp = time() + 3600;

		// Schedule the first time.
		$result1 = $this->scheduler->schedule_recurring(
			'edd_test_hook',
			$timestamp,
			HOUR_IN_SECONDS
		);

		$this->assertTrue( $result1, 'First schedule_recurring call should return true' );

		// Get the scheduled timestamp.
		$first_scheduled = wp_next_scheduled( 'edd_test_hook' );
		$this->assertIsInt( $first_scheduled, 'Event should be scheduled after first call' );

		// Attempt to schedule the same event again (should be prevented).
		$result2 = $this->scheduler->schedule_recurring(
			'edd_test_hook',
			$timestamp + 1000, // Different timestamp, but same hook.
			HOUR_IN_SECONDS
		);

		$this->assertTrue( $result2, 'Second schedule_recurring call should return true (idempotent)' );

		// Verify only ONE event exists (timestamp should be unchanged).
		$second_scheduled = wp_next_scheduled( 'edd_test_hook' );
		$this->assertEquals( $first_scheduled, $second_scheduled, 'Second call should not create duplicate event' );
	}

	/**
	 * Test that schedule_single checks for existing events.
	 */
	public function test_schedule_single_prevents_duplicates() {
		$timestamp = time() + 3600;

		// Schedule the first time.
		$result1 = $this->scheduler->schedule_single(
			'edd_test_hook',
			$timestamp
		);

		$this->assertTrue( $result1, 'First schedule_single call should return true' );

		// Get the scheduled timestamp.
		$first_scheduled = wp_next_scheduled( 'edd_test_hook' );
		$this->assertIsInt( $first_scheduled, 'Event should be scheduled after first call' );

		// Attempt to schedule the same event again (should be prevented).
		$result2 = $this->scheduler->schedule_single(
			'edd_test_hook',
			$timestamp + 1000 // Different timestamp, but same hook.
		);

		$this->assertTrue( $result2, 'Second schedule_single call should return true (idempotent)' );

		// Verify only ONE event exists (timestamp should be unchanged).
		$second_scheduled = wp_next_scheduled( 'edd_test_hook' );
		$this->assertEquals( $first_scheduled, $second_scheduled, 'Second call should not create duplicate event' );
	}
}
