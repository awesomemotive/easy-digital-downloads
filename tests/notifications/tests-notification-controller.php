<?php
/**
 * Notification Controller Tests
 *
 * Tests the Notifications REST Controller directly, including edge cases
 * and error paths for the notification system usability improvements
 * (Issue #2242).
 *
 * @package   EDD\Tests\Notifications
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.6
 */
namespace EDD\Tests\Notifications;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Controllers\Notifications as Controller;
use EDD\REST\Routes\Route;
use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\REST\Controllers\Notifications
 * @group edd_notifications
 * @group edd_rest
 */
class NotificationControllerTests extends EDD_UnitTestCase {

	/**
	 * @var Controller
	 */
	protected $controller;

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
	 * Runs before each test.
	 */
	public function setup(): void {
		parent::setUp();

		$this->controller = new Controller();

		$component = EDD()->components['notification'];
		$thing     = $component->get_interface( 'table' );
		if ( $thing instanceof \EDD\Database\Table ) {
			$thing->truncate();
		}

		wp_cache_delete( 'edd_active_notification_count', 'edd_notifications' );

		global $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
	}

	/**
	 * Helper to make a WP_REST_Request.
	 *
	 * @param string $method
	 * @param string $route
	 * @param array  $params
	 *
	 * @return \WP_REST_Request
	 */
	protected function makeRequest( $method, $route, $params = array() ) {
		$request = new \WP_REST_Request( $method, $route );

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		return $request;
	}

	/**
	 * Tests that list_notifications returns a WP_REST_Response.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_returns_rest_response() {
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
	}

	/**
	 * Tests that list_notifications returns expected structure.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_returns_correct_keys() {
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'notifications', $data );
		$this->assertArrayHasKey( 'total', $data );
	}

	/**
	 * Tests that list_notifications total equals notification array count.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_total_equals_count() {
		for ( $i = 1; $i <= 3; $i++ ) {
			EDD()->notifications->insert( array(
				'title'     => "Notification {$i}",
				'content'   => "Content {$i}",
				'type'      => 'success',
				'source'    => 'api',
				'dismissed' => 0,
			) );
		}

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( count( $data['notifications'] ), $data['total'] );
		$this->assertEquals( 3, $data['total'] );
	}

	/**
	 * Tests that list_notifications passes the dismissed parameter to the DB.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_passes_dismissed_param() {
		EDD()->notifications->insert( array(
			'title'     => 'Active',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		EDD()->notifications->insert( array(
			'title'     => 'Dismissed',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		// Default (dismissed=0) should return only active.
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications', array( 'dismissed' => 0 ) );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 1, $data['total'] );
		$this->assertEquals( 'Active', $data['notifications'][0]['title'] );

		// Dismissed=1 should return only dismissed.
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications', array( 'dismissed' => 1 ) );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 1, $data['total'] );
		$this->assertEquals( 'Dismissed', $data['notifications'][0]['title'] );
	}

	/**
	 * Tests that list_notifications passes source parameter to the DB.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_passes_source_param() {
		EDD()->notifications->insert( array(
			'title'     => 'API Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		EDD()->notifications->insert( array(
			'title'     => 'Local Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'local',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications', array( 'source' => 'local' ) );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 1, $data['total'] );
		$this->assertEquals( 'Local Notification', $data['notifications'][0]['title'] );
	}

	/**
	 * Tests that list_notifications passes type parameter to the DB.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_passes_type_param() {
		EDD()->notifications->insert( array(
			'title'     => 'Success',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		EDD()->notifications->insert( array(
			'title'     => 'Error',
			'content'   => 'Content',
			'type'      => 'error',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications', array( 'type' => 'error' ) );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 1, $data['total'] );
		$this->assertEquals( 'Error', $data['notifications'][0]['title'] );
	}

	/**
	 * Tests that notification data is properly converted via toArray().
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_maps_to_array() {
		EDD()->notifications->insert( array(
			'title'     => 'Array Map Test',
			'content'   => 'Testing toArray mapping',
			'type'      => 'warning',
			'source'    => 'local',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$notification = $data['notifications'][0];

		// These are added by toArray().
		$this->assertArrayHasKey( 'icon_name', $notification );
		$this->assertArrayHasKey( 'relative_date', $notification );
		$this->assertEquals( 'warning', $notification['icon_name'] );
	}

	/**
	 * Tests that notifications array values are re-indexed (sequential keys).
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_returns_sequential_keys() {
		for ( $i = 0; $i < 3; $i++ ) {
			EDD()->notifications->insert( array(
				'title'     => "Notification {$i}",
				'content'   => 'Content',
				'type'      => 'success',
				'source'    => 'api',
				'dismissed' => 0,
			) );
		}

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$keys = array_keys( $data['notifications'] );
		$this->assertEquals( array( 0, 1, 2 ), $keys );
	}

	/**
	 * Tests that dismiss_notification returns 204 on success.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_notification_returns_204() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Dismiss Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/' . $id, array( 'id' => $id ) );
		$response = $this->controller->dismiss_notification( $request );

		$this->assertEquals( 204, $response->get_status() );
	}

	/**
	 * Tests that dismiss_notification sets dismissed to 1 in DB.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_notification_updates_database() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Dismiss DB Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$notification = new Notification( EDD()->notifications->get( $id ) );
		$this->assertFalse( $notification->dismissed );

		$request = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/' . $id, array( 'id' => $id ) );
		$this->controller->dismiss_notification( $request );

		$notification = new Notification( EDD()->notifications->get( $id ) );
		$this->assertTrue( $notification->dismissed );
	}

	/**
	 * Tests that dismiss_notification clears the active count cache.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_notification_clears_count_cache() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Cache Clear Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Warm the cache.
		$count = EDD()->notifications->countActiveNotifications();
		$this->assertEquals( 1, $count );

		$request = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/' . $id, array( 'id' => $id ) );
		$this->controller->dismiss_notification( $request );

		// After dismissing, the cache should be cleared and count should be 0.
		$new_count = EDD()->notifications->countActiveNotifications();
		$this->assertEquals( 0, $new_count );
	}

	/**
	 * Tests the full REST dispatch for listing notifications via the server.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_full_dispatch_list_returns_200() {
		EDD()->notifications->insert( array(
			'title'     => 'Dispatch Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request = new \WP_REST_Request(
			\WP_REST_Server::READABLE,
			sprintf( '/%s/%s/notifications', Route::NAMESPACE, Route::$version )
		);

		$response = self::$server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response->get_data()['notifications'] );
	}

	/**
	 * Tests the full REST dispatch for dismissing a notification via the server.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_full_dispatch_dismiss_returns_204() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Dispatch Dismiss Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request = new \WP_REST_Request(
			\WP_REST_Server::DELETABLE,
			sprintf( '/%s/%s/notifications/%d', Route::NAMESPACE, Route::$version, $id )
		);

		$response = self::$server->dispatch( $request );

		$this->assertEquals( 204, $response->get_status() );
	}

	/**
	 * Tests that empty source parameter is ignored in filtering.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_empty_source_param_returns_all_notifications() {
		EDD()->notifications->insert( array(
			'title'     => 'API Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		EDD()->notifications->insert( array(
			'title'     => 'Local Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'local',
			'dismissed' => 0,
		) );

		// source is not set (empty) - should return all.
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 2, $data['total'] );
	}

	/**
	 * Tests that empty type parameter is ignored in filtering.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_empty_type_param_returns_all_notifications() {
		EDD()->notifications->insert( array(
			'title'     => 'Success',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		EDD()->notifications->insert( array(
			'title'     => 'Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// type is not set (empty) - should return all.
		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertEquals( 2, $data['total'] );
	}

	/**
	 * Tests that the dismiss endpoint via full dispatch returns 403 for subscriber.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_full_dispatch_dismiss_returns_403_for_subscriber() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Permission Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		global $current_user;
		$user_id      = wp_insert_user( array(
			'user_login' => 'test_sub_ctrl_dismiss',
			'user_email' => 'test_sub_ctrl_dismiss@easydigitaldownloads.com',
			'user_pass'  => 'test_sub_ctrl_dismiss',
		) );
		$current_user = new \WP_User( $user_id );
		$current_user->set_role( 'subscriber' );

		$request = new \WP_REST_Request(
			\WP_REST_Server::DELETABLE,
			sprintf( '/%s/%s/notifications/%d', Route::NAMESPACE, Route::$version, $id )
		);

		$response = self::$server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Tests that the list endpoint via full dispatch returns 403 for logged-out user.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_full_dispatch_list_returns_403_for_logged_out() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request(
			\WP_REST_Server::READABLE,
			sprintf( '/%s/%s/notifications', Route::NAMESPACE, Route::$version )
		);

		$response = self::$server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Tests that dismiss_notification response body is null on success.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_notification_response_body_is_null() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Null Body Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/' . $id, array( 'id' => $id ) );
		$response = $this->controller->dismiss_notification( $request );

		$this->assertNull( $response->get_data() );
	}

	/**
	 * Tests that dismiss_notification returns 400 for already-dismissed notification.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_already_dismissed_returns_400() {
		$id = EDD()->notifications->insert( array(
			'title'     => 'Already Dismissed',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/' . $id, array( 'id' => $id ) );
		$response = $this->controller->dismiss_notification( $request );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'rest_notification_already_dismissed', $response->get_data()['code'] );
	}

	/**
	 * Tests that dismiss_notification returns 404 for nonexistent notification.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_nonexistent_returns_404() {
		$request  = $this->makeRequest( \WP_REST_Server::DELETABLE, '/edd/v3/notifications/999999', array( 'id' => 999999 ) );
		$response = $this->controller->dismiss_notification( $request );

		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( 'rest_notification_not_found', $response->get_data()['code'] );
	}

	/**
	 * Tests that notification content containing script tags is sanitized.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_sanitizes_content() {
		EDD()->notifications->insert( array(
			'title'     => 'XSS Test',
			'content'   => '<p>Safe</p><script>alert("xss")</script>',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertStringNotContainsString( '<script>', $data['notifications'][0]['content'] );
		$this->assertStringContainsString( '<p>Safe</p>', $data['notifications'][0]['content'] );
	}

	/**
	 * Tests that notification title containing script tags is sanitized.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_list_notifications_sanitizes_title() {
		EDD()->notifications->insert( array(
			'title'     => 'Title <script>alert("xss")</script>',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$request  = $this->makeRequest( \WP_REST_Server::READABLE, '/edd/v3/notifications' );
		$response = $this->controller->list_notifications( $request );
		$data     = $response->get_data();

		$this->assertStringNotContainsString( '<script>', $data['notifications'][0]['title'] );
		$this->assertStringContainsString( 'Title', $data['notifications'][0]['title'] );
	}
}
