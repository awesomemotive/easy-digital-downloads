<?php
/**
 * Test Integration Registry - Basic Tests
 *
 * @package     EDD\Tests\Integrations
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Tests\Integrations;

use EDD\Integrations\Registry;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Test Integration Registry - Basic Tests
 *
 * @since 3.6.0
 */
class IntegrationRegistry extends TestCase {

	/**
	 * Test that the Registry class exists.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_class_exists() {
		$this->assertTrue( class_exists( 'EDD\Integrations\Registry' ) );
	}

	/**
	 * Test that the Registry implements SubscriberInterface.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_implements_subscriber_interface() {
		$registry = new Registry();
		$this->assertInstanceOf( 'EDD\EventManagement\SubscriberInterface', $registry );
	}

	/**
	 * Test that get_subscribed_events returns the correct events.
	 *
	 * @since 3.6.0
	 */
	public function test_get_subscribed_events() {
		$events = Registry::get_subscribed_events();

		$this->assertIsArray( $events );
		$this->assertArrayHasKey( 'admin_init', $events );
		$this->assertArrayHasKey( 'plugins_loaded', $events );
		$this->assertEquals( 'register_admin_integrations', $events['admin_init'] );
		$this->assertEquals( 'register_integrations', $events['plugins_loaded'] );
	}

	/**
	 * Test that the Registry can be instantiated without errors.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_can_be_instantiated() {
		$registry = new Registry();
		$this->assertInstanceOf( 'EDD\Integrations\Registry', $registry );
	}

	/**
	 * Test that the Registry methods exist and are callable.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_methods_exist_and_are_callable() {
		$registry = new Registry();

		$this->assertTrue( method_exists( $registry, 'register_admin_integrations' ) );
		$this->assertTrue( method_exists( $registry, 'register_integrations' ) );
		$this->assertTrue( is_callable( array( $registry, 'register_admin_integrations' ) ) );
		$this->assertTrue( is_callable( array( $registry, 'register_integrations' ) ) );
	}

	/**
	 * Test that the Registry can be instantiated multiple times.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_can_be_instantiated_multiple_times() {
		$registry1 = new Registry();
		$registry2 = new Registry();

		$this->assertInstanceOf( 'EDD\Integrations\Registry', $registry1 );
		$this->assertInstanceOf( 'EDD\Integrations\Registry', $registry2 );
		$this->assertNotSame( $registry1, $registry2 );
	}

	/**
	 * Test that the Registry handles method calls gracefully.
	 *
	 * @since 3.6.0
	 */
	public function test_registry_handles_method_calls_gracefully() {
		$registry = new Registry();

		// These methods should not throw errors even if they don't do anything
		$registry->register_admin_integrations();
		$registry->register_integrations();

		$this->assertTrue( true ); // If we get here, no error was thrown
	}
}
