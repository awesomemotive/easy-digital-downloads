<?php
/**
 * Pro REST Manager Tests
 *
 * Ensures the Pro checkout-templates REST route is wired up through the
 * replaceable-provider mechanism when Pro is active.
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\REST\Manager as ProManager;

/**
 * Pro REST Manager tests.
 *
 * @group rest-api
 */
class ProRestManagerTest extends EDD_UnitTestCase {

	/**
	 * Test that Pro\Core registers the Pro REST Manager as the replaceable provider.
	 *
	 * This guards the load-bearing re-key in Pro\Core::get_replaceable_providers();
	 * without it, Pro would register the Lite Manager and the Pro checkout-templates
	 * route would never be registered.
	 */
	public function test_pro_core_replaces_rest_manager() {
		$core   = ( new \ReflectionClass( \EDD\Pro\Core::class ) )->newInstanceWithoutConstructor();
		$method = new \ReflectionMethod( \EDD\Pro\Core::class, 'get_replaceable_providers' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$providers = $method->invoke( $core );

		$this->assertArrayHasKey( 'REST\Manager', $providers );
		$this->assertInstanceOf( ProManager::class, $providers['REST\Manager'] );
	}

	/**
	 * Test that the Pro Manager registers the Pro checkout-templates route.
	 */
	public function test_pro_manager_registers_checkout_templates_route() {
		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();

		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$manager = new ProManager();
		$manager->register_rest_routes();

		$routes = $wp_rest_server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/checkout-templates/(?P<id>[\w-]+)/import', $routes );
		$this->assertArrayHasKey( '/edd/v3/checkout-templates/restore', $routes );

		$wp_rest_server = null;
	}
}
