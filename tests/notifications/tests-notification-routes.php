<?php
/**
 * Notification REST Routes Tests
 *
 * Tests the REST route registration, argument validation, and permission
 * callbacks for the Notifications endpoint introduced in Issue #2242.
 *
 * @package   EDD\Tests\Notifications
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.6
 */
namespace EDD\Tests\Notifications;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Routes\Notifications;
use EDD\REST\Routes\Route;

/**
 * @coversDefaultClass \EDD\REST\Routes\Notifications
 * @group edd_notifications
 * @group edd_rest
 */
class NotificationRoutesTests extends EDD_UnitTestCase {

	/**
	 * @var \WP_REST_Server
	 */
	protected static $server;

	/**
	 * Runs once before any tests run.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		global $wp_rest_server;
		self::$server = $wp_rest_server = new \WP_REST_Server();

		do_action( 'rest_api_init' );
	}

	/**
	 * Runs once after all tests have run.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tearDownAfterClass();
	}

	/**
	 * Tests that the Notifications route extends the base Route class.
	 *
	 * @covers \EDD\REST\Routes\Notifications
	 * @return void
	 */
	public function test_notifications_route_extends_route() {
		$route = new Notifications();
		$this->assertInstanceOf( Route::class, $route );
	}

	/**
	 * Tests that the list notifications endpoint is registered.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_notifications_route_is_registered() {
		global $wp_rest_server;
		$routes = $wp_rest_server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/notifications', $routes );
	}

	/**
	 * Tests that the dismiss notification endpoint is registered.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_notifications_route_is_registered() {
		global $wp_rest_server;
		$routes = $wp_rest_server->get_routes();

		$this->assertArrayHasKey( '/edd/v3/notifications/(?P<id>\\d+)', $routes );
	}

	/**
	 * Tests that the list route accepts the 'dismissed' parameter.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_route_has_dismissed_arg() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'args', $list_route );
		$this->assertArrayHasKey( 'dismissed', $list_route['args'] );
		$this->assertFalse( $list_route['args']['dismissed']['required'] );
		$this->assertEquals( 0, $list_route['args']['dismissed']['default'] );
		$this->assertEquals( 'integer', $list_route['args']['dismissed']['type'] );
	}

	/**
	 * Tests that the list route accepts the 'source' parameter.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_route_has_source_arg() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'source', $list_route['args'] );
		$this->assertFalse( $list_route['args']['source']['required'] );
		$this->assertEquals( 'string', $list_route['args']['source']['type'] );
	}

	/**
	 * Tests that the list route accepts the 'type' parameter.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_route_has_type_arg() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'type', $list_route['args'] );
		$this->assertFalse( $list_route['args']['type']['required'] );
		$this->assertEquals( 'string', $list_route['args']['type']['type'] );
	}

	/**
	 * Tests that the dismiss route requires an 'id' argument.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_route_has_required_id_arg() {
		global $wp_rest_server;
		$routes        = $wp_rest_server->get_routes();
		$dismiss_route = $routes['/edd/v3/notifications/(?P<id>\\d+)'][0];

		$this->assertArrayHasKey( 'args', $dismiss_route );
		$this->assertArrayHasKey( 'id', $dismiss_route['args'] );
		$this->assertTrue( $dismiss_route['args']['id']['required'] );
		$this->assertEquals( 'integer', $dismiss_route['args']['id']['type'] );
	}

	/**
	 * Tests that check_permission returns true for an admin user.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_check_permission_returns_true_for_admin() {
		global $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );

		$route   = new Notifications();
		$request = new \WP_REST_Request( \WP_REST_Server::READABLE, '/edd/v3/notifications' );

		$this->assertTrue( $route->check_permission( $request ) );
	}

	/**
	 * Tests that check_permission returns WP_Error for a subscriber.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_check_permission_returns_error_for_subscriber() {
		global $current_user;
		$user_id      = wp_insert_user( array(
			'user_login' => 'test_sub_routes_perm',
			'user_email' => 'test_sub_routes_perm@easydigitaldownloads.com',
			'user_pass'  => 'test_sub_routes_perm',
		) );
		$current_user = new \WP_User( $user_id );
		$current_user->set_role( 'subscriber' );

		$route   = new Notifications();
		$request = new \WP_REST_Request( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$result  = $route->check_permission( $request );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'rest_forbidden', $result->get_error_code() );
	}

	/**
	 * Tests that check_permission returns WP_Error for a logged-out user.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_check_permission_returns_error_for_logged_out_user() {
		wp_set_current_user( 0 );

		$route   = new Notifications();
		$request = new \WP_REST_Request( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$result  = $route->check_permission( $request );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'rest_forbidden', $result->get_error_code() );
	}

	/**
	 * Tests that the WP_Error includes a 403 status code in its data.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_check_permission_error_has_403_status() {
		wp_set_current_user( 0 );

		$route      = new Notifications();
		$request    = new \WP_REST_Request( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$result     = $route->check_permission( $request );
		$error_data = $result->get_error_data( 'rest_forbidden' );

		$this->assertArrayHasKey( 'status', $error_data );
		$this->assertEquals( 403, $error_data['status'] );
	}

	/**
	 * Tests that the BASE constant is set correctly.
	 *
	 * @covers \EDD\REST\Routes\Notifications
	 * @return void
	 */
	public function test_base_constant_is_notifications() {
		$this->assertEquals( 'notifications', Notifications::BASE );
	}

	/**
	 * Tests that the list endpoint uses GET method.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_endpoint_uses_get_method() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( \WP_REST_Server::READABLE, $list_route['methods'] );
	}

	/**
	 * Tests that the dismiss endpoint uses DELETE method.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_endpoint_uses_delete_method() {
		global $wp_rest_server;
		$routes        = $wp_rest_server->get_routes();
		$dismiss_route = $routes['/edd/v3/notifications/(?P<id>\\d+)'][0];

		$this->assertArrayHasKey( \WP_REST_Server::DELETABLE, $dismiss_route['methods'] );
	}

	/**
	 * Tests that both routes have sanitize callbacks for their parameters.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_list_route_params_have_sanitize_callbacks() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'sanitize_callback', $list_route['args']['dismissed'] );
		$this->assertArrayHasKey( 'sanitize_callback', $list_route['args']['source'] );
		$this->assertArrayHasKey( 'sanitize_callback', $list_route['args']['type'] );
	}

	/**
	 * Tests that the dismissed parameter has a validate callback.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismissed_param_has_validate_callback() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'validate_callback', $list_route['args']['dismissed'] );
		$this->assertIsCallable( $list_route['args']['dismissed']['validate_callback'] );
	}

	/**
	 * Tests the dismissed validate callback accepts 0.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismissed_validate_callback_accepts_zero() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['dismissed']['validate_callback'];

		$this->assertTrue( $validate_callback( 0 ) );
	}

	/**
	 * Tests the dismissed validate callback accepts 1.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismissed_validate_callback_accepts_one() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['dismissed']['validate_callback'];

		$this->assertTrue( $validate_callback( 1 ) );
	}

	/**
	 * Tests the dismissed validate callback rejects invalid values.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismissed_validate_callback_rejects_invalid_value() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['dismissed']['validate_callback'];

		$result = $validate_callback( 5 );
		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'rest_invalid_param', $result->get_error_code() );
	}

	/**
	 * Tests the dismiss route ID validate_callback rejects non-existent notification.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_route_id_validate_rejects_nonexistent() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$dismiss_route     = $routes['/edd/v3/notifications/(?P<id>\\d+)'][0];
		$validate_callback = $dismiss_route['args']['id']['validate_callback'];

		// Use a very high ID that doesn't exist.
		$this->assertFalse( $validate_callback( 999999 ) );
	}

	/**
	 * Tests the dismiss route ID validate_callback accepts existing notification.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_route_id_validate_accepts_existing() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$dismiss_route     = $routes['/edd/v3/notifications/(?P<id>\\d+)'][0];
		$validate_callback = $dismiss_route['args']['id']['validate_callback'];

		$id = EDD()->notifications->insert( array(
			'title'     => 'Validate Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$this->assertTrue( (bool) $validate_callback( $id ) );
	}

	/**
	 * Tests the dismiss route ID sanitize callback converts to integer.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_dismiss_route_id_sanitize_converts_to_int() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$dismiss_route     = $routes['/edd/v3/notifications/(?P<id>\\d+)'][0];
		$sanitize_callback = $dismiss_route['args']['id']['sanitize_callback'];

		$this->assertSame( 42, $sanitize_callback( '42' ) );
		$this->assertSame( 0, $sanitize_callback( 'abc' ) );
	}

	/**
	 * Tests that the source parameter has an enum constraint.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_source_param_has_enum() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'enum', $list_route['args']['source'] );
		$this->assertContains( 'api', $list_route['args']['source']['enum'] );
		$this->assertContains( 'local', $list_route['args']['source']['enum'] );
	}

	/**
	 * Tests that the type parameter has an enum constraint.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_type_param_has_enum() {
		global $wp_rest_server;
		$routes     = $wp_rest_server->get_routes();
		$list_route = $routes['/edd/v3/notifications'][0];

		$this->assertArrayHasKey( 'enum', $list_route['args']['type'] );
		$this->assertContains( 'success', $list_route['args']['type']['enum'] );
		$this->assertContains( 'warning', $list_route['args']['type']['enum'] );
		$this->assertContains( 'error', $list_route['args']['type']['enum'] );
		$this->assertContains( 'info', $list_route['args']['type']['enum'] );
	}

	/**
	 * Tests that the source validate callback accepts 'api'.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_source_validate_callback_accepts_api() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['source']['validate_callback'];

		$this->assertTrue( $validate_callback( 'api' ) );
	}

	/**
	 * Tests that the source validate callback accepts 'local'.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_source_validate_callback_accepts_local() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['source']['validate_callback'];

		$this->assertTrue( $validate_callback( 'local' ) );
	}

	/**
	 * Tests that the source validate callback rejects invalid values.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_source_validate_callback_rejects_invalid() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['source']['validate_callback'];

		$result = $validate_callback( 'nonexistent' );
		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'rest_invalid_param', $result->get_error_code() );
	}

	/**
	 * Tests that the type validate callback accepts 'success'.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_type_validate_callback_accepts_success() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['type']['validate_callback'];

		$this->assertTrue( $validate_callback( 'success' ) );
	}

	/**
	 * Tests that the type validate callback rejects invalid values.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_type_validate_callback_rejects_invalid() {
		global $wp_rest_server;
		$routes            = $wp_rest_server->get_routes();
		$validate_callback = $routes['/edd/v3/notifications'][0]['args']['type']['validate_callback'];

		$result = $validate_callback( 'nonexistent' );
		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'rest_invalid_param', $result->get_error_code() );
	}

	/**
	 * Tests that the ALLOWED_SOURCES constant is defined.
	 *
	 * @covers \EDD\REST\Routes\Notifications
	 * @return void
	 */
	public function test_allowed_sources_constant_exists() {
		$this->assertIsArray( Notifications::ALLOWED_SOURCES );
		$this->assertNotEmpty( Notifications::ALLOWED_SOURCES );
	}

	/**
	 * Tests that the ALLOWED_TYPES constant is defined.
	 *
	 * @covers \EDD\REST\Routes\Notifications
	 * @return void
	 */
	public function test_allowed_types_constant_exists() {
		$this->assertIsArray( Notifications::ALLOWED_TYPES );
		$this->assertNotEmpty( Notifications::ALLOWED_TYPES );
	}
}
