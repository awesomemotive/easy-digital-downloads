<?php
/**
 * NotificationApiTests.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */
namespace EDD\Tests\Notifications;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\API\v3\Endpoint;
use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\API\v3\Notifications
 */
class NotificationApiTests extends EDD_UnitTestCase {

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

		// Set up the REST API.
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

		// Insert 5 notifications.
		for ( $i = 1; $i <= 5; $i ++ ) {
			self::$notificationIds[] = (int) EDD()->notifications->insert( array(
				'title'     => 'Notification ' . $i,
				'content'   => 'Notification ' . $i,
				'type'      => 'success',
				'dismissed' => 0,
			) );
		}

		/**
		 * Also need to make sure we have the EDD roles so that we pass the
		 * capability check.
		 *
		 * @see \EDD\API\v3\Notifications::canViewNotification
		 */
		$roles = new \EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

		// Set current user as an administrator.
		global $current_user;
		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
		$current_user->add_cap( 'manage_shop_settings' ); // I feel like this shouldn't be necessary to do, but it is.
	}

	/**
	 * Performs a REST API request.
	 *
	 * @param string $endpointUri
	 * @param array  $payload
	 * @param string $method
	 *
	 * @return \WP_REST_Response
	 */
	protected function makeRestRequest( $endpointUri = 'notifications', $payload = array(), $method = \WP_REST_Server::READABLE ) {
		$request = new \WP_REST_Request( $method, sprintf(
			'/%s/%s',
			Endpoint::$namespace,
			$endpointUri
		) );

		$request->set_header( 'content-type', 'application/json' );

		if ( ! empty( $payload ) ) {
			$request->set_body( json_encode( $payload ) );
		}

		return self::$server->dispatch( $request );
	}

	/**
	 * @covers \EDD\API\v3\Notifications::listNotifications
	 * @return void
	 */
	public function test_get_notifications_returns_5_notifications() {
		$response      = $this->makeRestRequest();
		$response_data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 5, $response_data['active'] );
		if ( method_exists( $this, 'assertEqualsCanonicalizing' ) ) {
			$this->assertEqualsCanonicalizing( self::$notificationIds, wp_list_pluck( $response_data['active'], 'id' ) );
		} else {
			$this->assertEquals( self::$notificationIds, wp_list_pluck( $response_data['active'], 'id' ), '', 0, 1, true, true );
		}
	}

	/**
	 * @covers \EDD\API\v3\Notifications::dismissNotification
	 * @return void
	 */
	public function test_dismissing_notification_updates_notification() {
		$notificationId = self::$notificationIds[0];

		$notification = new Notification( EDD()->notifications->get( $notificationId ) );
		$this->assertFalse( $notification->dismissed );

		$response = $this->makeRestRequest(
			'notifications/' . $notificationId,
			null,
			\WP_REST_Server::DELETABLE
		);

		$this->assertEquals( 204, $response->get_status() );

		$notification = new Notification( EDD()->notifications->get( $notificationId ) );
		$this->assertTrue( $notification->dismissed );
	}

	/**
	 * The notification we're attempting to delete doesn't exist.
	 *
	 * @return void
	 */
	public function test_dismissing_notification_with_invalid_id_fails() {
		$notificationId = max( self::$notificationIds ) + 100;

		$response = $this->makeRestRequest(
			'notifications/' . $notificationId,
			null,
			\WP_REST_Server::DELETABLE
		);

		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_subscriber_cannot_list_notifications() {
		global $current_user;
		$current_user = new \WP_User( wp_insert_user( array(
			'user_login' => 'test_subscriber',
			'user_email' => 'test_subscriber@easydigitaldownloads.com',
			'user_pass'  => 'test_subscriber',
		) ) );
		$current_user->set_role( 'subscriber' );

		$response = $this->makeRestRequest();

		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_subscriber_cannot_dismiss_notification() {
		global $current_user;
		$current_user = new \WP_User( wp_insert_user( array(
			'user_login' => 'test_subscriber',
			'user_email' => 'test_subscriber@easydigitaldownloads.com',
			'user_pass'  => 'test_subscriber',
		) ) );
		$current_user->set_role( 'subscriber' );

		$response = $this->makeRestRequest(
			'notifications/' . self::$notificationIds[0],
			null,
			\WP_REST_Server::DELETABLE
		);

		$this->assertEquals( 403, $response->get_status() );
	}

}
