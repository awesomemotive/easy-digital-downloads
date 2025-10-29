<?php
/**
 * Test Integration Interface
 *
 * @package     EDD\Tests\Integrations
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Tests\Integrations;

use EDD\Integrations\Integration;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Test Integration Interface
 *
 * @since 3.6.0
 */
class IntegrationInterface extends TestCase {

	/**
	 * Test that the Integration interface exists and can be implemented.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_interface_exists() {
		$this->assertTrue( interface_exists( 'EDD\Integrations\Integration' ) );
	}

	/**
	 * Test that the Integration interface has the required methods.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_interface_has_required_methods() {
		$reflection = new \ReflectionClass( 'EDD\Integrations\Integration' );
		$methods    = $reflection->getMethods();

		$method_names = array_map( function( $method ) {
			return $method->getName();
		}, $methods );

		$this->assertContains( 'can_load', $method_names );
		$this->assertContains( 'subscribe', $method_names );
	}

	/**
	 * Test that the Integration interface methods have correct signatures.
	 *
	 * @since 3.6.0
	 */
	public function test_integration_interface_method_signatures() {
		$reflection = new \ReflectionClass( 'EDD\Integrations\Integration' );

		// Test can_load method signature
		$can_load_method = $reflection->getMethod( 'can_load' );
		$this->assertEquals( 'bool', $can_load_method->getReturnType()->getName() );
		$this->assertEquals( 0, $can_load_method->getNumberOfParameters() );

		// Test subscribe method signature
		$subscribe_method = $reflection->getMethod( 'subscribe' );
		// The subscribe method doesn't have a return type annotation in the interface
		$this->assertNull( $subscribe_method->getReturnType() );
		$this->assertEquals( 0, $subscribe_method->getNumberOfParameters() );
	}

	/**
	 * Test that a class can implement the Integration interface.
	 *
	 * @since 3.6.0
	 */
	public function test_can_implement_integration_interface() {
		$mock_integration = new class implements Integration {
			public function can_load(): bool {
				return true;
			}

			public function subscribe() {
				// Mock implementation
			}
		};

		$this->assertInstanceOf( 'EDD\Integrations\Integration', $mock_integration );
		$this->assertTrue( $mock_integration->can_load() );
	}
}
