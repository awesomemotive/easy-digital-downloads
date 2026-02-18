<?php
namespace EDD\Tests\Logs\LogPruning;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Events\LogPruning as LogPruningEvent;

/**
 * Log Pruning Event Tests
 *
 * Tests for the LogPruning cron event scheduling.
 *
 * @group edd_logs
 * @group edd_logs_pruning
 * @group edd_logs_pruning_event
 * @group edd_cron
 */
class Event_Tests extends EDD_UnitTestCase {

	/**
	 * Event instance.
	 *
	 * @var LogPruningEvent
	 */
	protected $event;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Load the registry
		// Clear all scheduled hooks before each test
		$this->clear_all_log_pruning_hooks();

		$this->event = new LogPruningEvent();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		$this->clear_all_log_pruning_hooks();
		parent::tearDown();
	}

	/**
	 * Clear all log pruning cron hooks.
	 */
	protected function clear_all_log_pruning_hooks() {
		// Clear old hook if it exists
		$this->clear_scheduled_hook( 'edd_daily_log_pruning' );

		// Clear individual log type hooks
		$log_types = array( 'file_downloads', 'gateway_errors', 'api_requests', 'emails' );
		foreach ( $log_types as $type_id ) {
			$this->clear_scheduled_hook( "edd_prune_logs_{$type_id}" );
		}

		// Clear any additional hooks from the database
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%edd_prune_logs_%'" );
	}

	/**
	 * Enable all core log types for testing.
	 */
	protected function enable_all_core_log_types() {
		edd_update_option( 'edd_log_pruning_settings', array(
			'log_types' => array(
				'file_downloads' => array(
					'enabled' => true,
					'days'    => 90,
				),
				'gateway_errors' => array(
					'enabled' => true,
					'days'    => 30,
				),
				'api_requests' => array(
					'enabled' => true,
					'days'    => 60,
				),
				'emails' => array(
					'enabled' => true,
					'days'    => 30,
				),
			),
		) );
	}

	/**
	 * Test that event has correct hook property.
	 */
	public function test_event_has_hook() {
		$reflection = new \ReflectionClass( $this->event );
		$property = $reflection->getProperty( 'hook' );
		$property->setAccessible( true );
		$hook = $property->getValue( $this->event );

		$this->assertEquals( 'edd_prune_logs', $hook );
	}

	/**
	 * Test that event has correct schedule property.
	 */
	public function test_event_has_schedule() {
		$reflection = new \ReflectionClass( $this->event );
		$property = $reflection->getProperty( 'schedule' );
		$property->setAccessible( true );
		$schedule = $property->getValue( $this->event );

		$this->assertEquals( 'daily', $schedule );
	}

	/**
	 * Test that schedule method creates individual hooks for each log type.
	 */
	public function test_schedule_creates_individual_hooks() {
		// Enable all core log types.
		$this->enable_all_core_log_types();

		$this->event->schedule();

		// Check that individual hooks are scheduled for each log type
		$this->assertNotFalse( $this->get_next_scheduled( 'edd_prune_logs_file_downloads' ) );
		$this->assertNotFalse( $this->get_next_scheduled( 'edd_prune_logs_gateway_errors' ) );
		$this->assertNotFalse( $this->get_next_scheduled( 'edd_prune_logs_api_requests' ) );
		$this->assertNotFalse( $this->get_next_scheduled( 'edd_prune_logs_emails' ) );

		// Clean up.
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test that scheduled events are staggered (different times).
	 */
	public function test_scheduled_events_are_staggered() {
		// Enable all core log types.
		$this->enable_all_core_log_types();

		$this->event->schedule();

		$times = array(
			'file_downloads' => $this->get_next_scheduled( 'edd_prune_logs_file_downloads' ),
			'gateway_errors' => $this->get_next_scheduled( 'edd_prune_logs_gateway_errors' ),
			'api_requests'   => $this->get_next_scheduled( 'edd_prune_logs_api_requests' ),
			'emails'         => $this->get_next_scheduled( 'edd_prune_logs_emails' ),
		);

		// Filter out any false values
		$times = array_filter( $times );

		// All times should be different (staggered)
		$unique_times = array_unique( $times );
		$this->assertCount( 4, $unique_times, 'All log types should have different scheduled times' );

		// Verify times are in the future
		foreach ( $times as $type => $time ) {
			$this->assertGreaterThan( time(), $time, "{$type} should be scheduled in the future" );
		}

		// Clean up.
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test that schedule doesn't reschedule already scheduled events.
	 */
	public function test_schedule_does_not_reschedule_existing() {
		// Enable all core log types.
		$this->enable_all_core_log_types();

		// Schedule the first time
		$this->event->schedule();
		$first_time = $this->get_next_scheduled( 'edd_prune_logs_file_downloads' );

		// Schedule again
		$this->event->schedule();
		$second_time = $this->get_next_scheduled( 'edd_prune_logs_file_downloads' );

		// Time should be the same (not rescheduled)
		$this->assertEquals( $first_time, $second_time );

		// Clean up.
		edd_delete_option( 'edd_log_pruning_settings' );
	}

	/**
	 * Test calculate_base_time returns future timestamp.
	 */
	public function test_calculate_base_time_returns_future() {
		$reflection = new \ReflectionClass( $this->event );
		$method = $reflection->getMethod( 'calculate_base_time' );
		$method->setAccessible( true );

		$base_time = $method->invoke( $this->event );

		$this->assertIsInt( $base_time );
		$this->assertGreaterThan( time(), $base_time );
	}

	/**
	 * Test calculate_base_time returns time within expected range.
	 */
	public function test_calculate_base_time_within_range() {
		$reflection = new \ReflectionClass( $this->event );
		$method = $reflection->getMethod( 'calculate_base_time' );
		$method->setAccessible( true );

		$base_time = $method->invoke( $this->event );
		$current_time = current_time( 'timestamp', true );

		// Should be at least in the future
		$this->assertGreaterThan( $current_time, $base_time );

		// Should be within the next 48 hours (generous range to avoid flaky tests)
		$max_time = $current_time + ( 48 * HOUR_IN_SECONDS );
		$this->assertLessThan( $max_time, $base_time );
	}

	/**
	 * Test Registry::get_additional_log_types returns array.
	 *
	 * Note: This method was moved from Event to Registry for DRY.
	 */
	public function test_get_additional_log_types_returns_array() {
		// Clear transient cache to ensure fresh results.
		delete_transient( 'edd_additional_log_types' );

		$result = \EDD\Logs\Registry::get_additional_log_types( false, false );

		$this->assertIsArray( $result );
	}

	/**
	 * Test Registry::get_additional_log_types excludes registered types.
	 */
	public function test_get_additional_log_types_excludes_registered() {
		global $wpdb;

		// Insert a test log with unregistered type.
		$wpdb->insert(
			"{$wpdb->prefix}edd_logs",
			array(
				'object_id'   => 0,
				'object_type' => 'test',
				'type'        => 'custom_test_type',
				'title'       => 'Test Log',
				'content'     => 'Test content',
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		// Clear transient cache to ensure fresh results.
		delete_transient( 'edd_additional_log_types' );

		$result = \EDD\Logs\Registry::get_additional_log_types( false, false );

		// Should have our custom type.
		$type_ids = array_keys( $result );
		$found_custom = false;
		foreach ( $type_ids as $type_id ) {
			if ( strpos( $type_id, 'custom_test_type' ) !== false ) {
				$found_custom = true;
				break;
			}
		}

		$this->assertTrue( $found_custom, 'Should find custom_test_type in additional types' );

		// Should NOT have registered types like 'gateway_error'.
		foreach ( $type_ids as $type_id ) {
			$this->assertStringNotContainsString( 'gateway_error', $type_id );
		}

		// Clean up
		$wpdb->delete(
			"{$wpdb->prefix}edd_logs",
			array( 'type' => 'custom_test_type' ),
			array( '%s' )
		);
	}

	/**
	 * Test schedule_single_type creates correct hook.
	 */
	public function test_schedule_single_type() {
		$reflection = new \ReflectionClass( $this->event );
		$method = $reflection->getMethod( 'schedule_single_type' );
		$method->setAccessible( true );

		$base_time = time() + DAY_IN_SECONDS;
		$method->invoke( $this->event, 'test_type', $base_time, 900 );

		$scheduled = $this->get_next_scheduled( 'edd_prune_logs_test_type' );
		$this->assertNotFalse( $scheduled );
		$this->assertEqualsWithDelta( $base_time + 900, $scheduled, 5 );

		// Clean up
		$this->clear_scheduled_hook( 'edd_prune_logs_test_type' );
	}

	/**
	 * Test that additional log types are scheduled when enabled in settings.
	 */
	public function test_schedule_additional_types_when_enabled() {
		global $wpdb;

		// Insert a test log with unregistered type
		$wpdb->insert(
			"{$wpdb->prefix}edd_logs",
			array(
				'object_id'   => 0,
				'object_type' => 'test',
				'type'        => 'custom_enabled_type',
				'title'       => 'Test Log',
				'content'     => 'Test content',
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		// Enable this unregistered type in settings
		edd_update_option( 'edd_log_pruning_settings', array(
			'enabled'   => true,
			'log_types' => array(
				'unregistered_custom_enabled_type' => array(
					'enabled' => true,
					'days'    => 90,
				),
			),
		) );

		// Clear transient cache to ensure fresh results from get_additional_log_types.
		delete_transient( 'edd_additional_log_types' );

		$this->event->schedule();

		// Should be scheduled
		$scheduled = $this->get_next_scheduled( 'edd_prune_logs_unregistered_custom_enabled_type' );
		$this->assertNotFalse( $scheduled, 'Enabled additional log type should be scheduled' );

		// Clean up
		$this->clear_scheduled_hook( 'edd_prune_logs_unregistered_custom_enabled_type' );
		edd_delete_option( 'edd_log_pruning_settings' );
		$wpdb->delete(
			"{$wpdb->prefix}edd_logs",
			array( 'type' => 'custom_enabled_type' ),
			array( '%s' )
		);
	}

	/**
	 * Helper to get next scheduled time for a hook (checks both WP-Cron and Action Scheduler).
	 *
	 * @param string $hook Hook name.
	 * @return int|false Timestamp or false if not scheduled.
	 */
	private function get_next_scheduled( $hook ) {
		// Check Action Scheduler first if available.
		if ( class_exists( 'ActionScheduler' ) && function_exists( 'as_next_scheduled_action' ) ) {
			$next = as_next_scheduled_action( $hook, array(), 'edd' );
			if ( $next ) {
				return $next;
			}
		}

		// Fall back to WP-Cron.
		return wp_next_scheduled( $hook );
	}

	/**
	 * Helper to clear scheduled hook (clears from both WP-Cron and Action Scheduler).
	 *
	 * @param string $hook Hook name.
	 */
	private function clear_scheduled_hook( $hook ) {
		// Clear from Action Scheduler if available.
		if ( class_exists( 'ActionScheduler' ) && function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( $hook, array(), 'edd' );
		}

		// Clear from WP-Cron.
		wp_clear_scheduled_hook( $hook );
	}
}
