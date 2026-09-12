<?php
/**
 * Lite REST Manager Tests
 *
 * Ensures the core v3 REST Manager is wired up through the replaceable-provider
 * mechanism when Pro is not active, and that the Lite checkout-templates route
 * set is registered.
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Manager as CoreManager;

/**
 * Lite REST Manager tests.
 *
 * @group rest-api
 */
class LiteRestManagerTest extends EDD_UnitTestCase {

	/**
	 * Tear down after each test.
	 *
	 * Restores global state so the forced-Lite filter and the REST server do not
	 * leak into later test files. This runs even when a test assertion fails.
	 */
	public function tearDown(): void {
		global $wp_rest_server;

		remove_filter( 'edd_is_pro', '__return_false' );
		$wp_rest_server = null;

		parent::tearDown();
	}

	/**
	 * Test that Lite\Core registers the core REST Manager as the replaceable provider.
	 *
	 * This guards the boot wiring in Lite\Core::get_replaceable_providers(); without
	 * it, the whole v3 REST Manager is dropped on Lite and none of its routes are
	 * ever registered.
	 */
	public function test_lite_core_replaces_rest_manager() {
		$core   = ( new \ReflectionClass( \EDD\Lite\Core::class ) )->newInstanceWithoutConstructor();
		$method = new \ReflectionMethod( \EDD\Lite\Core::class, 'get_replaceable_providers' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$providers = $method->invoke( $core );

		$this->assertArrayHasKey( 'REST\Manager', $providers );
		$this->assertInstanceOf( \EDD\REST\Manager::class, $providers['REST\Manager'] );
		$this->assertNotInstanceOf( \EDD\Pro\REST\Manager::class, $providers['REST\Manager'] );
	}

	/**
	 * Test that the Lite Manager registers the browse and stub routes.
	 */
	public function test_lite_manager_registers_browse_and_stub_routes() {
		// This test verifies the Lite route-SET contract and bypasses Core wiring by
		// design; test 1 is the wiring detector.
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();

		add_filter( 'edd_is_pro', '__return_false' );

		( new CoreManager() )->register_rest_routes();

		$routes = $wp_rest_server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/checkout-templates', $routes );
		$this->assertArrayHasKey( '/edd/v3/checkout-templates/(?P<id>[\w-]+)/import', $routes );
		$this->assertArrayHasKey( '/edd/v3/checkout-templates/restore', $routes, 'Lite restore stub route missing' );
	}

	/**
	 * Test that both Lite stub routes gate Pro-only operations with a 403 pro_required.
	 *
	 * Covers POST .../import and POST .../restore in one method so the downstream
	 * per-class test count stays determinate. Both routes require the shop-settings
	 * capability and a valid nonce to reach their callbacks, so a capable user is
	 * set up first; the callbacks then return the pro_required upgrade error.
	 */
	public function test_lite_stub_routes_require_pro() {
		// This test verifies the Lite route-SET contract and bypasses Core wiring by
		// design; test 1 is the wiring detector.
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();

		add_filter( 'edd_is_pro', '__return_false' );

		( new CoreManager() )->register_rest_routes();

		// A user who passes the shared shop-settings permission callback, so the
		// request reaches the Pro-required callback rather than stopping at the gate.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$user    = new \WP_User( $user_id );
		$user->add_cap( 'manage_shop_settings' );
		wp_set_current_user( $user_id );

		$import_request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/test-template/import' );
		$import_request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$import_request->set_param( 'id', 'test-template' );
		$import_request->set_param( 'editor', 'elementor' );
		$import_response = $wp_rest_server->dispatch( $import_request );

		$this->assertSame( 403, $import_response->get_status() );
		$this->assertSame( 'pro_required', $import_response->get_data()['code'] );
		$this->assertSame(
			edd_link_helper(
				\EDD\Checkout\Templates\Config\Constants::UPGRADE_URL,
				array(
					'utm_medium'  => 'checkout-templates',
					'utm_content' => 'import-requires-pro',
				)
			),
			$import_response->get_data()['data']['upgrade_url']
		);

		$restore_request = new \WP_REST_Request( 'POST', '/edd/v3/checkout-templates/restore' );
		$restore_request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$restore_request->set_param( 'revision_id', 999 );
		$restore_response = $wp_rest_server->dispatch( $restore_request );

		$this->assertSame( 403, $restore_response->get_status() );
		$this->assertSame( 'pro_required', $restore_response->get_data()['code'] );
		$this->assertSame(
			edd_link_helper(
				\EDD\Checkout\Templates\Config\Constants::UPGRADE_URL,
				array(
					'utm_medium'  => 'checkout-templates',
					'utm_content' => 'restore-requires-pro',
				)
			),
			$restore_response->get_data()['data']['upgrade_url']
		);
	}
}
