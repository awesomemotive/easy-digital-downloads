<?php
/**
 * Extended Notification API Tests
 *
 * Tests additional edge cases and combined filter scenarios for the
 * Notifications REST API endpoints introduced in the notification system
 * usability improvements (Issue #2242).
 *
 * @package   EDD\Tests\Notifications
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.6
 */
namespace EDD\Tests\Notifications;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Routes\Route;
use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\REST\Controllers\Notifications
 * @group edd_notifications
 * @group edd_rest
 */
class NotificationApiExtendedTests extends EDD_UnitTestCase {

	/**
	 * @var int[] IDs of notifications we've created.
	 */
	protected static $notificationIds;

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

		$component = EDD()->components['notification'];
		$thing     = $component->get_interface( 'table' );
		if ( $thing instanceof \EDD\Database\Table ) {
			$thing->truncate();
		}

		self::$notificationIds = array();

		global $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
	}

	/**
	 * Helper to make REST requests.
	 *
	 * @param string $endpointUri
	 * @param array  $params Query parameters.
	 * @param string $method
	 *
	 * @return \WP_REST_Response
	 */
	protected function makeRestRequest( $endpointUri = 'notifications', $params = array(), $method = \WP_REST_Server::READABLE ) {
		$request = new \WP_REST_Request( $method, sprintf(
			'/%s/%s/%s',
			Route::NAMESPACE,
			Route::$version,
			$endpointUri
		) );

		$request->set_header( 'content-type', 'application/json' );

		if ( ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$request->set_param( $key, $value );
			}
		}

		return self::$server->dispatch( $request );
	}

	/**
	 * Helper to insert a notification and track its ID.
	 *
	 * @param array $data Notification data.
	 * @return int The notification ID.
	 */
	protected function insertNotification( $data ) {
		$id                      = (int) EDD()->notifications->insert( $data );
		self::$notificationIds[] = $id;

		return $id;
	}

	/**
	 * Verifies the response has the correct top-level structure.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_response_structure_has_notifications_and_total_keys() {
		$this->insertNotification( array(
			'title'     => 'Test Notification',
			'content'   => 'Test content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'notifications', $response_data );
		$this->assertArrayHasKey( 'total', $response_data );
		$this->assertIsArray( $response_data['notifications'] );
		$this->assertIsInt( $response_data['total'] );
	}

	/**
	 * Verifies each notification in the response contains all expected fields.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_response_contains_all_expected_fields() {
		$this->insertNotification( array(
			'title'     => 'Field Test',
			'content'   => 'Field test content',
			'type'      => 'info',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		$expected_fields = array(
			'id',
			'title',
			'content',
			'type',
			'source',
			'dismissed',
			'date_created',
			'date_updated',
			'icon_name',
			'relative_date',
		);

		foreach ( $expected_fields as $field ) {
			$this->assertArrayHasKey( $field, $notification, "Missing expected field: {$field}" );
		}
	}

	/**
	 * Verifies the icon_name mapping is correct in the response for each type.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_response_has_correct_icon_for_type() {
		$type_icon_map = array(
			'success' => 'yes-alt',
			'warning' => 'warning',
			'error'   => 'dismiss',
			'info'    => 'admin-generic',
		);

		foreach ( $type_icon_map as $type => $expected_icon ) {
			$this->insertNotification( array(
				'title'     => "Type {$type}",
				'content'   => "Content for {$type}",
				'type'      => $type,
				'source'    => 'api',
				'dismissed' => 0,
			) );
		}

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		foreach ( $response_data['notifications'] as $notification ) {
			$expected_icon = $type_icon_map[ $notification['type'] ];
			$this->assertEquals( $expected_icon, $notification['icon_name'], "Icon mismatch for type: {$notification['type']}" );
		}
	}

	/**
	 * Verifies that requesting notifications returns an empty array when none exist.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_empty_notification_list_returns_empty_array() {
		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 0, $response_data['notifications'] );
		$this->assertEquals( 0, $response_data['total'] );
	}

	/**
	 * Tests combined source and type filters.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_combined_source_and_type_filter() {
		// API + success.
		$this->insertNotification( array(
			'title'     => 'API Success',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// API + warning.
		$this->insertNotification( array(
			'title'     => 'API Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Local + success.
		$this->insertNotification( array(
			'title'     => 'Local Success',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'local',
			'dismissed' => 0,
		) );

		// Local + warning.
		$this->insertNotification( array(
			'title'     => 'Local Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'local',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest( 'notifications', array(
			'source' => 'local',
			'type'   => 'warning',
		) );
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Local Warning', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that combined source, type, and dismissed filters work together.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_combined_dismissed_source_and_type_filter() {
		// Dismissed API warning.
		$this->insertNotification( array(
			'title'     => 'Dismissed API Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		// Active API warning.
		$this->insertNotification( array(
			'title'     => 'Active API Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Dismissed local warning.
		$this->insertNotification( array(
			'title'     => 'Dismissed Local Warning',
			'content'   => 'Content',
			'type'      => 'warning',
			'source'    => 'local',
			'dismissed' => 1,
		) );

		$response      = $this->makeRestRequest( 'notifications', array(
			'dismissed' => 1,
			'source'    => 'api',
			'type'      => 'warning',
		) );
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Dismissed API Warning', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that dismissed=0 (default) excludes dismissed notifications.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_default_dismissed_param_excludes_dismissed() {
		$this->insertNotification( array(
			'title'     => 'Active Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$this->insertNotification( array(
			'title'     => 'Dismissed Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		// No dismissed param means default=0.
		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Active Notification', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that the total count matches the number of notifications in response.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_total_matches_notification_count() {
		for ( $i = 1; $i <= 8; $i++ ) {
			$this->insertNotification( array(
				'title'     => "Notification {$i}",
				'content'   => "Content {$i}",
				'type'      => 'success',
				'source'    => 'api',
				'dismissed' => 0,
			) );
		}

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( $response_data['total'], $response_data['notifications'] );
		$this->assertEquals( 8, $response_data['total'] );
	}

	/**
	 * Tests that notifications with future start dates are excluded from active results.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_future_start_date_excluded_from_active_results() {
		// Active now (no start date).
		$this->insertNotification( array(
			'title'     => 'Current Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Starts in the future.
		$this->insertNotification( array(
			'title'     => 'Future Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
			'start'     => gmdate( 'Y-m-d H:i:s', strtotime( '+1 week' ) ),
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Current Notification', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that notifications with past end dates are excluded from active results.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_past_end_date_excluded_from_active_results() {
		// Active (no end date).
		$this->insertNotification( array(
			'title'     => 'Current Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Expired.
		$this->insertNotification( array(
			'title'     => 'Expired Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
			'end'       => gmdate( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Current Notification', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that dismissed notifications skip date range filtering.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_dismissed_notifications_skip_date_filtering() {
		// Insert a notification with an expired end date, then dismiss it.
		$id = $this->insertNotification( array(
			'title'     => 'Dismissed Expired',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
			'end'       => gmdate( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
		) );

		EDD()->notifications->update( $id, array( 'dismissed' => 1 ) );

		// Requesting dismissed notifications should include it regardless of end date.
		$response      = $this->makeRestRequest( 'notifications', array( 'dismissed' => 1 ) );
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Dismissed Expired', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that dismissing clears the active notification count cache.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_clears_active_notification_cache() {
		$id = $this->insertNotification( array(
			'title'     => 'Cache Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Warm the cache.
		$count = EDD()->notifications->countActiveNotifications();
		$this->assertEquals( 1, $count );

		// Dismiss via REST.
		$this->makeRestRequest(
			'notifications/' . $id,
			array(),
			\WP_REST_Server::DELETABLE
		);

		// Cache should be cleared; new count should be 0.
		$new_count = EDD()->notifications->countActiveNotifications();
		$this->assertEquals( 0, $new_count );
	}

	/**
	 * Tests that the notification is actually marked as dismissed in the database after REST dismiss.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_notification_persists_in_database() {
		$id = $this->insertNotification( array(
			'title'     => 'Persist Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$notification = new Notification( EDD()->notifications->get( $id ) );
		$this->assertFalse( $notification->dismissed );

		$response = $this->makeRestRequest(
			'notifications/' . $id,
			array(),
			\WP_REST_Server::DELETABLE
		);

		$this->assertEquals( 204, $response->get_status() );

		$notification = new Notification( EDD()->notifications->get( $id ) );
		$this->assertTrue( $notification->dismissed );
	}

	/**
	 * Tests that a filter returning no results returns correct empty structure.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_filter_with_no_matching_results() {
		$this->insertNotification( array(
			'title'     => 'API Success',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		// Filter by a valid source that doesn't match any notifications.
		$response      = $this->makeRestRequest( 'notifications', array( 'source' => 'local' ) );
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 0, $response_data['notifications'] );
		$this->assertEquals( 0, $response_data['total'] );
	}

	/**
	 * Tests that an invalid source parameter is rejected by validation.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_invalid_source_param_returns_error() {
		$response = $this->makeRestRequest( 'notifications', array( 'source' => 'nonexistent' ) );

		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Tests that an invalid type parameter is rejected by validation.
	 *
	 * @covers \EDD\REST\Routes\Notifications::register
	 * @return void
	 */
	public function test_invalid_type_param_returns_error() {
		$response = $this->makeRestRequest( 'notifications', array( 'type' => 'nonexistent' ) );

		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Tests that dismissing an already-dismissed notification returns 400.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::dismiss_notification
	 * @return void
	 */
	public function test_dismiss_already_dismissed_returns_400() {
		$id = $this->insertNotification( array(
			'title'     => 'Already Dismissed',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		$response = $this->makeRestRequest(
			'notifications/' . $id,
			array(),
			\WP_REST_Server::DELETABLE
		);

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'rest_notification_already_dismissed', $response->get_data()['code'] );
	}

	/**
	 * Tests that notification content is sanitized via wp_kses_post in the response.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_content_is_sanitized() {
		$this->insertNotification( array(
			'title'     => 'Sanitize Test',
			'content'   => '<p>Safe content</p><script>alert("xss")</script>',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		// Script tags should be stripped by wp_kses_post.
		$this->assertStringNotContainsString( '<script>', $notification['content'] );
		$this->assertStringContainsString( '<p>Safe content</p>', $notification['content'] );
	}

	/**
	 * Tests that notification title is sanitized via wp_kses_post in the response.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_title_is_sanitized() {
		$this->insertNotification( array(
			'title'     => 'Title <script>alert("xss")</script>',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		// Script tags should be stripped by wp_kses_post.
		$this->assertStringNotContainsString( '<script>', $notification['title'] );
		$this->assertStringContainsString( 'Title', $notification['title'] );
	}

	/**
	 * Tests filtering dismissed notifications by source.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_filter_dismissed_by_source() {
		$this->insertNotification( array(
			'title'     => 'Dismissed API',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 1,
		) );

		$this->insertNotification( array(
			'title'     => 'Dismissed Local',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'local',
			'dismissed' => 1,
		) );

		$response      = $this->makeRestRequest( 'notifications', array(
			'dismissed' => 1,
			'source'    => 'local',
		) );
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 1, $response_data['notifications'] );
		$this->assertEquals( 'Dismissed Local', $response_data['notifications'][0]['title'] );
	}

	/**
	 * Tests that a shop_worker role with manage_shop_settings can access notifications.
	 *
	 * @covers \EDD\REST\Routes\Notifications::check_permission
	 * @return void
	 */
	public function test_shop_manager_can_list_notifications() {
		$this->insertNotification( array(
			'title'     => 'Test Notification',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		global $current_user;
		$user_id      = wp_insert_user( array(
			'user_login' => 'test_shop_manager_notif',
			'user_email' => 'test_shop_manager_notif@easydigitaldownloads.com',
			'user_pass'  => 'test_shop_manager_notif',
		) );
		$current_user = new \WP_User( $user_id );
		$current_user->set_role( 'shop_manager' );

		$response = $this->makeRestRequest();

		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Tests that the notification 'id' field in the response is an integer.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_id_is_integer_in_response() {
		$this->insertNotification( array(
			'title'     => 'ID Type Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		$this->assertIsInt( $notification['id'] );
	}

	/**
	 * Tests that the dismissed field in the response is a boolean.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_dismissed_is_boolean_in_response() {
		$this->insertNotification( array(
			'title'     => 'Bool Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		$this->assertIsBool( $notification['dismissed'] );
		$this->assertFalse( $notification['dismissed'] );
	}

	/**
	 * Tests that the notification response contains relative_date as a string.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_has_relative_date_string() {
		$this->insertNotification( array(
			'title'     => 'Date Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		$this->assertArrayHasKey( 'relative_date', $notification );
		$this->assertIsString( $notification['relative_date'] );
		$this->assertStringContainsString( 'ago', $notification['relative_date'] );
	}

	/**
	 * Tests that buttons are included in the response when set.
	 *
	 * @covers \EDD\REST\Controllers\Notifications::list_notifications
	 * @return void
	 */
	public function test_notification_with_buttons_in_response() {
		$buttons = array(
			array(
				'type' => 'primary',
				'url'  => 'https://example.com',
				'text' => 'Learn More',
			),
		);

		$this->insertNotification( array(
			'title'     => 'Button Test',
			'content'   => 'Content',
			'type'      => 'success',
			'source'    => 'api',
			'dismissed' => 0,
			'buttons'   => $buttons,
		) );

		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();
		$notification  = $response_data['notifications'][0];

		$this->assertIsArray( $notification['buttons'] );
		$this->assertCount( 1, $notification['buttons'] );
		$this->assertEquals( 'Learn More', $notification['buttons'][0]['text'] );
	}
}
