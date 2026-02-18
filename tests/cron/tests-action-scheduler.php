<?php
namespace EDD\Tests\Cron;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Schedulers\ActionScheduler as ActionSchedulerClass;

/**
 * ActionScheduler Tests
 *
 * Tests for the ActionScheduler wrapper class, focusing on edge cases
 * and return value handling.
 *
 * @group edd_cron
 * @group edd_cron_action_scheduler
 */
class ActionScheduler extends EDD_UnitTestCase {

	/**
	 * ActionScheduler instance.
	 *
	 * @var ActionSchedulerClass
	 */
	protected $scheduler;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! ActionSchedulerClass::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$this->scheduler = new ActionSchedulerClass();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clean up any test actions.
		if ( ActionSchedulerClass::is_available() ) {
			as_unschedule_all_actions( '', null, 'edd' );
		}
	}

	/**
	 * Test that schedule_single prevents duplicate scheduling when unique=true.
	 */
	public function test_schedule_single_prevents_duplicates() {
		$hook      = 'edd_test_unique_single';
		$timestamp = time() + 3600;
		$args      = array( 'test_id' => 123 );

		// First scheduling should succeed.
		$result1 = $this->scheduler->schedule_single( $hook, $timestamp, $args );
		$this->assertTrue( $result1, 'First scheduling should succeed' );

		// Second scheduling with same hook/args should return true (already scheduled).
		$result2 = $this->scheduler->schedule_single( $hook, $timestamp, $args );
		$this->assertTrue( $result2, 'Should return true for already scheduled action' );

		// Verify only one action exists.
		$actions = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'args'   => $args,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);

		$this->assertCount( 1, $actions, 'Should only have one scheduled action' );
	}

	/**
	 * Test that schedule_recurring prevents duplicate scheduling.
	 */
	public function test_schedule_recurring_prevents_duplicates() {
		$hook      = 'edd_test_unique_recurring';
		$timestamp = time() + 3600;
		$interval  = HOUR_IN_SECONDS;

		// First scheduling should succeed.
		$result1 = $this->scheduler->schedule_recurring( $hook, $timestamp, $interval );
		$this->assertTrue( $result1, 'First scheduling should succeed' );

		// Second scheduling should return true (already scheduled).
		$result2 = $this->scheduler->schedule_recurring( $hook, $timestamp, $interval );
		$this->assertTrue( $result2, 'Should return true for already scheduled action' );

		// Verify only one action exists.
		$actions = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);

		$this->assertCount( 1, $actions, 'Should only have one scheduled recurring action' );
	}

	/**
	 * Test that next_scheduled returns correct timestamp for pending actions.
	 */
	public function test_next_scheduled_returns_timestamp_for_pending_action() {
		$hook      = 'edd_test_next_scheduled';
		$timestamp = time() + 3600;

		$this->scheduler->schedule_single( $hook, $timestamp );

		$next = $this->scheduler->next_scheduled( $hook );

		$this->assertIsInt( $next, 'next_scheduled should return an integer' );
		$this->assertEqualsWithDelta( $timestamp, $next, 5, 'Timestamp should match scheduled time' );
	}

	/**
	 * Test that next_scheduled returns false when no action is scheduled.
	 */
	public function test_next_scheduled_returns_false_when_not_scheduled() {
		$hook = 'edd_test_nonexistent';

		$next = $this->scheduler->next_scheduled( $hook );

		$this->assertFalse( $next, 'next_scheduled should return false for non-existent action' );
	}

	/**
	 * Test that next_scheduled handles different argument combinations.
	 */
	public function test_next_scheduled_respects_arguments() {
		$hook      = 'edd_test_args';
		$timestamp = time() + 3600;
		$args1     = array( 'type' => 'email1' );
		$args2     = array( 'type' => 'email2' );

		// Schedule two actions with different args.
		$this->scheduler->schedule_single( $hook, $timestamp, $args1 );
		$this->scheduler->schedule_single( $hook, $timestamp + 100, $args2 );

		// Verify each returns correct timestamp.
		$next1 = $this->scheduler->next_scheduled( $hook, $args1 );
		$next2 = $this->scheduler->next_scheduled( $hook, $args2 );

		$this->assertIsInt( $next1 );
		$this->assertIsInt( $next2 );
		$this->assertNotEquals( $next1, $next2, 'Different args should have different timestamps' );
	}

	/**
	 * Test that has_scheduled is more efficient than next_scheduled.
	 */
	public function test_has_scheduled_returns_boolean() {
		$hook      = 'edd_test_has_scheduled';
		$timestamp = time() + 3600;

		// Before scheduling.
		$has_before = $this->scheduler->has_scheduled( $hook );
		$this->assertFalse( $has_before, 'Should return false before scheduling' );

		// After scheduling.
		$this->scheduler->schedule_single( $hook, $timestamp );
		$has_after = $this->scheduler->has_scheduled( $hook );
		$this->assertTrue( $has_after, 'Should return true after scheduling' );
	}

	/**
	 * Test that unschedule removes the correct action.
	 */
	public function test_unschedule_removes_correct_action() {
		$hook      = 'edd_test_unschedule';
		$timestamp = time() + 3600;
		$args1     = array( 'id' => 1 );
		$args2     = array( 'id' => 2 );

		// Schedule two actions.
		$this->scheduler->schedule_single( $hook, $timestamp, $args1 );
		$this->scheduler->schedule_single( $hook, $timestamp, $args2 );

		// Unschedule first one.
		$result = $this->scheduler->unschedule( $hook, $args1 );
		$this->assertTrue( $result, 'Unschedule should return true' );

		// Verify first is gone, second remains.
		$this->assertFalse( $this->scheduler->has_scheduled( $hook, $args1 ), 'First action should be unscheduled' );
		$this->assertTrue( $this->scheduler->has_scheduled( $hook, $args2 ), 'Second action should remain' );
	}

	/**
	 * Test that unschedule returns false for non-existent action.
	 */
	public function test_unschedule_returns_false_for_nonexistent_action() {
		$hook = 'edd_test_nonexistent_unschedule';

		$result = $this->scheduler->unschedule( $hook );

		// as_unschedule_action returns null when nothing is found, which maps to false in our wrapper.
		$this->assertFalse( $result, 'Unschedule should return false for non-existent action' );
	}

	/**
	 * Test that unschedule_all removes all matching actions.
	 */
	public function test_unschedule_all_removes_all_matching_actions() {
		$hook      = 'edd_test_unschedule_all';
		$timestamp = time() + 3600;

		// Schedule multiple actions with same hook.
		$this->scheduler->schedule_single( $hook, $timestamp, array( 'id' => 1 ) );
		$this->scheduler->schedule_single( $hook, $timestamp + 100, array( 'id' => 2 ) );
		$this->scheduler->schedule_single( $hook, $timestamp + 200, array( 'id' => 3 ) );

		// Verify all are scheduled.
		$actions_before = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);
		$this->assertCount( 3, $actions_before, 'Should have 3 scheduled actions' );

		// Unschedule all.
		$result = $this->scheduler->unschedule_all( $hook );
		$this->assertTrue( $result, 'unschedule_all should return true' );

		// Verify all are gone.
		$actions_after = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);
		$this->assertCount( 0, $actions_after, 'Should have 0 scheduled actions after unschedule_all' );
	}

	/**
	 * Test that unschedule_all with specific args only removes matching actions.
	 */
	public function test_unschedule_all_respects_arguments() {
		$hook      = 'edd_test_unschedule_all_args';
		$timestamp = time() + 3600;
		$args1     = array( 'type' => 'keep' );
		$args2     = array( 'type' => 'remove' );

		// Schedule actions with different args.
		$this->scheduler->schedule_single( $hook, $timestamp, $args1 );
		$this->scheduler->schedule_single( $hook, $timestamp + 100, $args2 );
		$this->scheduler->schedule_single( $hook, $timestamp + 200, $args2 );

		// Unschedule only args2.
		$this->scheduler->unschedule_all( $hook, $args2 );

		// Verify args1 remains, args2 are gone.
		$this->assertTrue( $this->scheduler->has_scheduled( $hook, $args1 ), 'Action with args1 should remain' );
		$this->assertFalse( $this->scheduler->has_scheduled( $hook, $args2 ), 'Actions with args2 should be removed' );
	}

	/**
	 * Test behavioral difference between null and empty array in unschedule_all.
	 *
	 * This test documents the semantic difference between passing null (match any args)
	 * and array() (match empty args) to unschedule_all(). This is important for
	 * Action Scheduler where the distinction matters, though WP-Cron treats both the same
	 * due to its use of empty() check.
	 */
	public function test_unschedule_all_null_vs_empty_array() {
		$hook      = 'edd_test_args_semantics';
		$timestamp = time() + 3600;

		// Schedule actions: one with empty args, two with specific args.
		$this->scheduler->schedule_single( $hook, $timestamp, array() );
		$this->scheduler->schedule_single( $hook, $timestamp + 100, array( 'id' => 1 ) );
		$this->scheduler->schedule_single( $hook, $timestamp + 200, array( 'id' => 2 ) );

		// Verify all three are scheduled.
		$count_before = count(
			as_get_scheduled_actions(
				array(
					'hook'   => $hook,
					'group'  => 'edd',
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				)
			)
		);
		$this->assertEquals( 3, $count_before, 'Should have 3 actions before unscheduling' );

		// Unschedule with null (should remove ALL actions regardless of args).
		$this->scheduler->unschedule_all( $hook, null );

		// Verify all are gone.
		$count_after_null = count(
			as_get_scheduled_actions(
				array(
					'hook'   => $hook,
					'group'  => 'edd',
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				)
			)
		);
		$this->assertEquals( 0, $count_after_null, 'null should remove all actions regardless of args' );

		// Now test with array() - schedule actions again.
		$this->scheduler->schedule_single( $hook, $timestamp, array() );
		$this->scheduler->schedule_single( $hook, $timestamp + 100, array( 'id' => 1 ) );

		// Unschedule with array() (should remove only actions with empty args).
		$this->scheduler->unschedule_all( $hook, array() );

		// Verify only the empty-args action is gone, specific-args action remains.
		$remaining = as_get_scheduled_actions(
			array(
				'hook'   => $hook,
				'group'  => 'edd',
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			),
			OBJECT
		);

		$this->assertCount( 1, $remaining, 'array() should only remove empty-args action' );

		// Verify the remaining action has specific args.
		$remaining_action = reset( $remaining );
		$remaining_args   = $remaining_action->get_args();
		$this->assertEquals( array( 'id' => 1 ), $remaining_args, 'Remaining action should have specific args' );
	}

	/**
	 * Test that get_scheduled_hooks returns hook names.
	 */
	public function test_get_scheduled_hooks_returns_hook_names() {
		$hook1     = 'edd_test_hook_1';
		$hook2     = 'edd_test_hook_2';
		$timestamp = time() + 3600;

		// Schedule actions with different hooks.
		$this->scheduler->schedule_single( $hook1, $timestamp );
		$this->scheduler->schedule_single( $hook2, $timestamp );

		$hooks = $this->scheduler->get_scheduled_hooks();

		$this->assertIsArray( $hooks );
		$this->assertContains( $hook1, $hooks );
		$this->assertContains( $hook2, $hooks );
	}

	/**
	 * Test that get_scheduled_hooks filters by prefix.
	 */
	public function test_get_scheduled_hooks_filters_by_prefix() {
		$hook1     = 'edd_email_summary';
		$hook2     = 'edd_email_reminder';
		$hook3     = 'edd_prune_logs';
		$timestamp = time() + 3600;

		// Schedule actions.
		$this->scheduler->schedule_single( $hook1, $timestamp );
		$this->scheduler->schedule_single( $hook2, $timestamp );
		$this->scheduler->schedule_single( $hook3, $timestamp );

		// Get only email hooks.
		$email_hooks = $this->scheduler->get_scheduled_hooks( 'edd_email_' );

		$this->assertIsArray( $email_hooks );
		$this->assertContains( $hook1, $email_hooks );
		$this->assertContains( $hook2, $email_hooks );
		$this->assertNotContains( $hook3, $email_hooks, 'Non-matching prefix should be excluded' );
	}

	/**
	 * Test that scheduling with same timestamp but different args creates separate actions.
	 */
	public function test_scheduling_same_timestamp_different_args() {
		$hook      = 'edd_test_same_timestamp';
		$timestamp = time() + 3600;
		$args1     = array( 'cart_id' => 1 );
		$args2     = array( 'cart_id' => 2 );

		// Schedule both at same timestamp.
		$result1 = $this->scheduler->schedule_single( $hook, $timestamp, $args1 );
		$result2 = $this->scheduler->schedule_single( $hook, $timestamp, $args2 );

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );

		// Verify both are scheduled.
		$this->assertTrue( $this->scheduler->has_scheduled( $hook, $args1 ) );
		$this->assertTrue( $this->scheduler->has_scheduled( $hook, $args2 ) );

		// Verify both have same timestamp.
		$next1 = $this->scheduler->next_scheduled( $hook, $args1 );
		$next2 = $this->scheduler->next_scheduled( $hook, $args2 );

		$this->assertEquals( $next1, $next2, 'Both actions should have same timestamp' );
	}

	/**
	 * Test that get_actions returns action objects.
	 */
	public function test_get_actions_returns_action_objects() {
		$hook      = 'edd_test_get_actions';
		$timestamp = time() + 3600;

		// Schedule some actions.
		$this->scheduler->schedule_single( $hook, $timestamp );

		$actions = ActionSchedulerClass::get_actions();

		$this->assertIsArray( $actions );
		$this->assertNotEmpty( $actions );

		// Verify action objects have expected methods.
		$action = reset( $actions );
		$this->assertTrue( is_object( $action ) );
		$this->assertTrue( method_exists( $action, 'get_hook' ) );
	}

	/**
	 * Test group defaulting behavior.
	 */
	public function test_default_group_is_applied() {
		$hook      = 'edd_test_default_group';
		$timestamp = time() + 3600;

		// Schedule without explicit group.
		$this->scheduler->schedule_single( $hook, $timestamp );

		// Verify it's in the 'edd' group.
		$this->assertTrue(
			as_has_scheduled_action( $hook, array(), 'edd' ),
			'Action should be in edd group by default'
		);

		// Verify it's NOT in a different group.
		$this->assertFalse(
			as_has_scheduled_action( $hook, array(), 'some-other-group' ),
			'Action should not be in other groups'
		);
	}

	/**
	 * Test custom group can be specified.
	 */
	public function test_custom_group_can_be_specified() {
		$hook      = 'edd_test_custom_group';
		$timestamp = time() + 3600;
		$group     = 'edd-custom';

		// Schedule with custom group.
		$this->scheduler->schedule_single( $hook, $timestamp, array(), $group );

		// Verify it's in the custom group.
		$this->assertTrue(
			as_has_scheduled_action( $hook, array(), $group ),
			'Action should be in custom group'
		);

		// Verify it's NOT in the default group.
		$this->assertFalse(
			as_has_scheduled_action( $hook, array(), 'edd' ),
			'Action should not be in default group'
		);

		// Clean up custom group.
		as_unschedule_all_actions( $hook, null, $group );
	}
}
