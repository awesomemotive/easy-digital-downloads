<?php
namespace EDD\Tests\REST;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Routes\Cart as REST_Cart_Routes;

/**
 * REST Cart Routes Tests
 *
 * @group edd_rest
 * @group edd_rest_cart
 * @group edd_rest_cart_routes
 */
class CartRoutes extends EDD_UnitTestCase {

	/**
	 * Routes instance.
	 *
	 * @var Routes
	 */
	protected $routes;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->routes = new REST_Cart_Routes();
	}

	/**
	 * Test that Routes extends Route base class.
	 *
	 * @covers \EDD\REST\Routes\Cart
	 */
	public function test_routes_extends_route_class() {
		$this->assertInstanceOf( 'EDD\REST\Routes\Route', $this->routes );
	}

	/**
	 * Test that BASE constant is defined.
	 *
	 * @covers \EDD\REST\Routes\Cart
	 */
	public function test_base_constant_defined() {
		$this->assertEquals( 'cart', REST_Cart_Routes::BASE );
	}

	/**
	 * Test that namespace constant is inherited.
	 *
	 * @covers \EDD\REST\Routes\Cart
	 */
	public function test_namespace_constant_inherited() {
		$this->assertEquals( 'edd', REST_Cart_Routes::NAMESPACE );
	}

	/**
	 * Test that version is defined.
	 *
	 * @covers \EDD\REST\Routes\Cart
	 */
	public function test_version_defined() {
		$this->assertEquals( 'v3', REST_Cart_Routes::$version );
	}

	/**
	 * Test register method exists and is callable.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_register_method_exists() {
		$this->assertTrue( method_exists( $this->routes, 'register' ) );
		$this->assertTrue( is_callable( array( $this->routes, 'register' ) ) );
	}

	/**
	 * Test that register actually registers routes.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_register_creates_routes() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();

		// Verify all cart routes are registered
		$expected_routes = array(
			'/edd/v3/cart/add',
			'/edd/v3/cart/remove',
			'/edd/v3/cart/update-quantity',
			'/edd/v3/cart/contents',
			'/edd/v3/cart/token',
		);

		foreach ( $expected_routes as $expected_route ) {
			$this->assertArrayHasKey( $expected_route, $routes, "Route {$expected_route} should be registered" );
		}
	}

	/**
	 * Test add route configuration.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_add_route_configuration() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();
		$add_route = $routes['/edd/v3/cart/add'][0];

		// Test method
		$this->assertArrayHasKey( \WP_REST_Server::CREATABLE, $add_route['methods'] );

		// Test callback exists
		$this->assertArrayHasKey( 'callback', $add_route );
		$this->assertIsCallable( $add_route['callback'] );

		// Test permission callback exists
		$this->assertArrayHasKey( 'permission_callback', $add_route );
		$this->assertIsCallable( $add_route['permission_callback'] );

		// Test arguments
		$this->assertArrayHasKey( 'args', $add_route );
		$args = $add_route['args'];

		// download_id argument
		$this->assertArrayHasKey( 'download_id', $args );
		$this->assertTrue( $args['download_id']['required'] );
		$this->assertEquals( 'integer', $args['download_id']['type'] );
		$this->assertIsCallable( $args['download_id']['sanitize_callback'] );
		$this->assertIsCallable( $args['download_id']['validate_callback'] );

		// price_id argument
		$this->assertArrayHasKey( 'price_id', $args );
		$this->assertFalse( $args['price_id']['required'] );
		$this->assertEquals( 'integer', $args['price_id']['type'] );

		// quantity argument
		$this->assertArrayHasKey( 'quantity', $args );
		$this->assertFalse( $args['quantity']['required'] );
		$this->assertEquals( 'integer', $args['quantity']['type'] );
		$this->assertEquals( 1, $args['quantity']['default'] );

		// options argument
		$this->assertArrayHasKey( 'options', $args );
		$this->assertFalse( $args['options']['required'] );
		$this->assertEquals( 'object', $args['options']['type'] );
		$this->assertIsArray( $args['options']['default'] );
		$this->assertEmpty( $args['options']['default'] );
		$this->assertIsCallable( $args['options']['sanitize_callback'] );
	}

	/**
	 * Test remove route configuration.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_remove_route_configuration() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();
		$remove_route = $routes['/edd/v3/cart/remove'][0];

		// Test method
		$this->assertArrayHasKey( \WP_REST_Server::CREATABLE, $remove_route['methods'] );

		// Test callback
		$this->assertArrayHasKey( 'callback', $remove_route );
		$this->assertIsCallable( $remove_route['callback'] );

		// Test permission callback
		$this->assertArrayHasKey( 'permission_callback', $remove_route );
		$this->assertIsCallable( $remove_route['permission_callback'] );

		// Test arguments
		$this->assertArrayHasKey( 'args', $remove_route );
		$args = $remove_route['args'];

		$this->assertArrayHasKey( 'cart_key', $args );
		$this->assertTrue( $args['cart_key']['required'] );
		$this->assertEquals( 'integer', $args['cart_key']['type'] );
	}

	/**
	 * Test update-quantity route configuration.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_update_quantity_route_configuration() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();
		$update_route = $routes['/edd/v3/cart/update-quantity'][0];

		// Test method
		$this->assertArrayHasKey( \WP_REST_Server::CREATABLE, $update_route['methods'] );

		// Test callback
		$this->assertArrayHasKey( 'callback', $update_route );
		$this->assertIsCallable( $update_route['callback'] );

		// Test permission callback
		$this->assertArrayHasKey( 'permission_callback', $update_route );
		$this->assertIsCallable( $update_route['permission_callback'] );

		// Test arguments
		$this->assertArrayHasKey( 'args', $update_route );
		$args = $update_route['args'];

		$this->assertArrayHasKey( 'cart_key', $args );
		$this->assertTrue( $args['cart_key']['required'] );
		$this->assertEquals( 'integer', $args['cart_key']['type'] );

		$this->assertArrayHasKey( 'quantity', $args );
		$this->assertTrue( $args['quantity']['required'] );
		$this->assertEquals( 'integer', $args['quantity']['type'] );
		$this->assertIsCallable( $args['quantity']['validate_callback'] );
	}

	/**
	 * Test contents route configuration.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_contents_route_configuration() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();
		$contents_route = $routes['/edd/v3/cart/contents'][0];

		// Test method
		$this->assertArrayHasKey( \WP_REST_Server::READABLE, $contents_route['methods'] );

		// Test callback
		$this->assertArrayHasKey( 'callback', $contents_route );
		$this->assertIsCallable( $contents_route['callback'] );

		// Test permission callback
		$this->assertArrayHasKey( 'permission_callback', $contents_route );
		$this->assertIsCallable( $contents_route['permission_callback'] );
	}

	/**
	 * Test token route configuration.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_token_route_configuration() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();
		$token_route = $routes['/edd/v3/cart/token'][0];

		// Test method
		$this->assertArrayHasKey( \WP_REST_Server::READABLE, $token_route['methods'] );

		// Test callback
		$this->assertArrayHasKey( 'callback', $token_route );
		$this->assertIsCallable( $token_route['callback'] );

		// Test permission callback (should be public)
		$this->assertArrayHasKey( 'permission_callback', $token_route );
		$this->assertEquals( '__return_true', $token_route['permission_callback'] );
	}

	/**
	 * Test that routes use correct namespace and version.
	 *
	 * @covers \EDD\REST\Routes\Cart::register
	 */
	public function test_routes_use_correct_namespace_and_version() {
		global $wp_rest_server;

		if ( empty( $wp_rest_server ) ) {
			$wp_rest_server = new \WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		// Expect incorrect usage since we're registering routes outside the action for testing
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		$this->routes->register();

		$routes = $wp_rest_server->get_routes();

		// All routes should start with /edd/v3/cart
		$expected_prefix = '/edd/v3/cart/';

		foreach ( $routes as $route => $config ) {
			if ( strpos( $route, '/edd/v3/cart' ) === 0 ) {
				$this->assertStringStartsWith( $expected_prefix, $route );
			}
		}
	}
}
