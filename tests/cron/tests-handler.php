<?php
namespace EDD\Tests\Cron;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cron\Schedulers\Handler as HandlerClass;
use EDD\Cron\Schedulers\ActionScheduler;
use EDD\Cron\Schedulers\WPCronScheduler;

/**
 * Handler Tests
 *
 * Tests for the cron scheduler handler/factory.
 *
 * @group edd_cron
 * @group edd_cron_handler
 */
class Handler extends EDD_UnitTestCase {

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		HandlerClass::reset();
		remove_all_filters( 'edd_use_action_scheduler' );
	}

	/**
	 * Test that get_scheduler returns a Scheduler instance.
	 */
	public function test_get_scheduler_returns_scheduler_instance() {
		$scheduler = HandlerClass::get_scheduler();

		$this->assertInstanceOf( 'EDD\Cron\Schedulers\Scheduler', $scheduler );
	}

	/**
	 * Test that get_scheduler returns ActionScheduler when available.
	 */
	public function test_get_scheduler_returns_action_scheduler_when_available() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$scheduler = HandlerClass::get_scheduler();

		$this->assertInstanceOf( 'EDD\Cron\Schedulers\ActionScheduler', $scheduler );
	}

	/**
	 * Test that get_scheduler returns WPCronScheduler when Action Scheduler is disabled via filter.
	 */
	public function test_get_scheduler_returns_wp_cron_when_disabled_via_filter() {
		add_filter( 'edd_use_action_scheduler', '__return_false' );

		HandlerClass::reset();
		$scheduler = HandlerClass::get_scheduler();

		$this->assertInstanceOf( 'EDD\Cron\Schedulers\WPCronScheduler', $scheduler );
	}

	/**
	 * Test that get_scheduler caches the scheduler instance.
	 */
	public function test_get_scheduler_caches_instance() {
		$scheduler1 = HandlerClass::get_scheduler();
		$scheduler2 = HandlerClass::get_scheduler();

		$this->assertSame( $scheduler1, $scheduler2 );
	}

	/**
	 * Test that reset clears the cached scheduler.
	 */
	public function test_reset_clears_cached_scheduler() {
		$scheduler1 = HandlerClass::get_scheduler();
		HandlerClass::reset();
		$scheduler2 = HandlerClass::get_scheduler();

		$this->assertNotSame( $scheduler1, $scheduler2 );
	}

	/**
	 * Test that is_using_action_scheduler returns correct value.
	 */
	public function test_is_using_action_scheduler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$this->assertTrue( HandlerClass::is_using_action_scheduler() );
	}

	/**
	 * Test that get_active_scheduler_name returns correct name.
	 */
	public function test_get_active_scheduler_name_returns_action_scheduler() {
		if ( ! ActionScheduler::is_available() ) {
			$this->markTestSkipped( 'Action Scheduler is not available.' );
		}

		$this->assertEquals( 'action-scheduler', HandlerClass::get_active_scheduler_name() );
	}

	/**
	 * Test that get_active_scheduler_name returns wp-cron when disabled.
	 */
	public function test_get_active_scheduler_name_returns_wp_cron_when_disabled() {
		add_filter( 'edd_use_action_scheduler', '__return_false' );

		HandlerClass::reset();

		$this->assertEquals( 'wp-cron', HandlerClass::get_active_scheduler_name() );
	}
}
