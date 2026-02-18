<?php
namespace EDD\Tests\REST;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Manager as REST_Manager;

/**
 * REST Manager Tests
 *
 * @group edd_rest
 * @group edd_rest_manager
 */
class Manager extends EDD_UnitTestCase {

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->manager = new REST_Manager();

		// Ensure REST server is initialized for each test.
		global $wp_rest_server;
		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
		}
		// Always call rest_api_init to ensure routes are registered.
		do_action( 'rest_api_init', $wp_rest_server );
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Reset REST server to prevent state leakage.
		global $wp_rest_server;
		$wp_rest_server = null;
	}

	/**
	 * Test that Manager implements SubscriberInterface.
	 *
	 * @covers \EDD\REST\Manager
	 */
	public function test_manager_implements_subscriber_interface() {
		$this->assertInstanceOf( 'EDD\EventManagement\SubscriberInterface', $this->manager );
	}

	/**
	 * Test that get_subscribed_events returns array.
	 *
	 * @covers \EDD\REST\Manager::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_array() {
		$events = REST_Manager::get_subscribed_events();
		$this->assertIsArray( $events );
	}

	/**
	 * Test that rest_api_init event is subscribed.
	 *
	 * @covers \EDD\REST\Manager::get_subscribed_events
	 */
	public function test_subscribes_to_rest_api_init() {
		$events = REST_Manager::get_subscribed_events();
		$this->assertArrayHasKey( 'rest_api_init', $events );
		$this->assertEquals( 'register_rest_routes', $events['rest_api_init'] );
	}

	/**
	 * Test that register_rest_routes registers routes.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_register_rest_routes() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();

		// Verify cart routes are registered
		$this->assertArrayHasKey( '/edd/v3/cart/add', $routes );
		$this->assertArrayHasKey( '/edd/v3/cart/remove', $routes );
		$this->assertArrayHasKey( '/edd/v3/cart/update-quantity', $routes );
		$this->assertArrayHasKey( '/edd/v3/cart/contents', $routes );
		$this->assertArrayHasKey( '/edd/v3/cart/token', $routes );
	}

	/**
	 * Test that cart routes have correct HTTP methods.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_cart_routes_have_correct_methods() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();

		// Test add route uses POST
		$this->assertArrayHasKey( '/edd/v3/cart/add', $routes );
		$add_route = $routes['/edd/v3/cart/add'][0];
		$this->assertArrayHasKey( \WP_REST_Server::CREATABLE, $add_route['methods'] );

		// Test remove route uses POST
		$this->assertArrayHasKey( '/edd/v3/cart/remove', $routes );
		$remove_route = $routes['/edd/v3/cart/remove'][0];
		$this->assertArrayHasKey( \WP_REST_Server::CREATABLE, $remove_route['methods'] );

		// Test contents route uses GET
		$this->assertArrayHasKey( '/edd/v3/cart/contents', $routes );
		$contents_route = $routes['/edd/v3/cart/contents'][0];
		$this->assertArrayHasKey( \WP_REST_Server::READABLE, $contents_route['methods'] );

		// Test token route uses GET
		$this->assertArrayHasKey( '/edd/v3/cart/token', $routes );
		$token_route = $routes['/edd/v3/cart/token'][0];
		$this->assertArrayHasKey( \WP_REST_Server::READABLE, $token_route['methods'] );
	}

	/**
	 * Test that routes have permission callbacks.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_routes_have_permission_callbacks() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();

		// Test add route has permission callback
		$add_route = $routes['/edd/v3/cart/add'][0];
		$this->assertArrayHasKey( 'permission_callback', $add_route );
		$this->assertIsCallable( $add_route['permission_callback'] );

		// Test token route has public permission callback
		$token_route = $routes['/edd/v3/cart/token'][0];
		$this->assertArrayHasKey( 'permission_callback', $token_route );
		$this->assertEquals( '__return_true', $token_route['permission_callback'] );
	}

	/**
	 * Test that add route has required arguments.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_add_route_has_required_arguments() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();
		$add_route = $routes['/edd/v3/cart/add'][0];

		$this->assertArrayHasKey( 'args', $add_route );
		$this->assertArrayHasKey( 'download_id', $add_route['args'] );
		$this->assertTrue( $add_route['args']['download_id']['required'] );

		$this->assertArrayHasKey( 'price_id', $add_route['args'] );
		$this->assertFalse( $add_route['args']['price_id']['required'] );

		$this->assertArrayHasKey( 'quantity', $add_route['args'] );
		$this->assertFalse( $add_route['args']['quantity']['required'] );
		$this->assertEquals( 1, $add_route['args']['quantity']['default'] );
	}

	/**
	 * Test that remove route has required cart_key argument.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_remove_route_has_cart_key_argument() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();
		$remove_route = $routes['/edd/v3/cart/remove'][0];

		$this->assertArrayHasKey( 'args', $remove_route );
		$this->assertArrayHasKey( 'cart_key', $remove_route['args'] );
		$this->assertTrue( $remove_route['args']['cart_key']['required'] );
	}

	/**
	 * Test that update-quantity route has required arguments.
	 *
	 * @covers \EDD\REST\Manager::register_rest_routes
	 */
	public function test_update_quantity_route_has_required_arguments() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();
		$update_route = $routes['/edd/v3/cart/update-quantity'][0];

		$this->assertArrayHasKey( 'args', $update_route );
		$this->assertArrayHasKey( 'cart_key', $update_route['args'] );
		$this->assertTrue( $update_route['args']['cart_key']['required'] );

		$this->assertArrayHasKey( 'quantity', $update_route['args'] );
		$this->assertTrue( $update_route['args']['quantity']['required'] );
	}
}
