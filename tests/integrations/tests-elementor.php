<?php
/**
 * Test Elementor Integration - Basic Tests
 *
 * @package     EDD\Tests\Integrations
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Tests\Integrations;

use EDD\Integrations\Elementor;
use EDD\Integrations\Integration;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Test Elementor Integration - Basic Tests
 *
 * @since 3.6.0
 */
class ElementorIntegration extends TestCase {

	/**
	 * Test that the Elementor integration class exists.
	 *
	 * @since 3.6.0
	 */
	public function test_elementor_integration_class_exists() {
		$this->assertTrue( class_exists( 'EDD\Integrations\Elementor' ) );
	}

	/**
	 * Test that the Elementor integration implements the Integration interface.
	 *
	 * @since 3.6.0
	 */
	public function test_elementor_integration_implements_interface() {
		$integration = new Elementor();
		$this->assertInstanceOf( 'EDD\Integrations\Integration', $integration );
	}

	/**
	 * Test can_load returns false when Elementor is not available.
	 *
	 * @since 3.6.0
	 */
	public function test_can_load_returns_false_when_elementor_not_available() {
		$integration = new Elementor();

		// In the test environment, Elementor should not be available
		$this->assertFalse( $integration->can_load() );
	}

	/**
	 * Test that the subscribe method can be called without errors.
	 *
	 * @since 3.6.0
	 */
	public function test_subscribe_method_can_be_called() {
		$integration = new Elementor();

		// Remove any existing actions to start clean
		remove_all_actions( 'plugins_loaded' );

		// This should not throw an error
		$integration->subscribe();

		// Verify that the action was added
		$this->assertNotFalse( has_action( 'plugins_loaded', array( $integration, 'load_elementor' ) ) );
	}

	/**
	 * Test that the load_elementor method exists and is callable.
	 *
	 * @since 3.6.0
	 */
	public function test_load_elementor_method_exists_and_is_callable() {
		$integration = new Elementor();

		$this->assertTrue( method_exists( $integration, 'load_elementor' ) );
		$this->assertTrue( is_callable( array( $integration, 'load_elementor' ) ) );
	}

	/**
	 * Test that the integration can be instantiated multiple times.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_can_be_instantiated_multiple_times() {
		$integration1 = new Elementor();
		$integration2 = new Elementor();

		$this->assertInstanceOf( 'EDD\Integrations\Elementor', $integration1 );
		$this->assertInstanceOf( 'EDD\Integrations\Elementor', $integration2 );
		$this->assertNotSame( $integration1, $integration2 );
	}

	/**
	 * Test that the integration methods return expected types.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_methods_return_expected_types() {
		$integration = new Elementor();

		// Test can_load returns boolean
		$result = $integration->can_load();
		$this->assertIsBool( $result );

		// Test subscribe returns void (no return value)
		$result = $integration->subscribe();
		$this->assertNull( $result );
	}

	/**
	 * Test that the integration handles missing dependencies gracefully.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_handles_missing_dependencies_gracefully() {
		$integration = new Elementor();

		// Test that can_load doesn't throw errors when constants/classes are missing
		$result = $integration->can_load();
		$this->assertIsBool( $result );

		// Test that subscribe doesn't throw errors
		$integration->subscribe();
		$this->assertTrue( true ); // If we get here, no error was thrown
	}
}
