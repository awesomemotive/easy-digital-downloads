<?php
/**
 * Notification Database Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Notifications;

use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\Database\NotificationsDB
 * @group edd_notifications
 */
class NotificationDBTests extends \EDD_UnitTestCase {

	/**
	 * @covers \EDD\Database\NotificationsDB::getActiveNotifications
	 */
	public function test_notification_included_in_active() {
		EDD()->notifications->insert( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'type'      => 'success',
			'dismissed' => 0,
		), 'notification' );

		$notifications = EDD()->notifications->getActiveNotifications();

		$this->assertSame( 1, count( $notifications ) );
		$this->assertTrue( $notifications[0] instanceof Notification );
		$this->assertSame( 'Notification', $notifications[0]->title );
	}

	/**
	 * @covers \EDD\Database\NotificationsDB::getActiveNotifications
	 */
	public function test_dismissed_notification_not_included_in_active() {
		EDD()->notifications->insert( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'type'      => 'success',
			'dismissed' => 1,
		), 'notification' );

		$this->assertEmpty( EDD()->notifications->getActiveNotifications() );
	}

	/**
	 * @covers \EDD\Database\NotificationsDB::getActiveNotifications
	 */
	public function test_start_date_in_future_not_included_in_active() {
		EDD()->notifications->insert( array(
			'title'   => 'Notification',
			'content' => 'Notification',
			'type'    => 'success',
			'start'   => date( 'Y-m-d H:i:s', strtotime( '+1 week' ) ),
		), 'notification' );

		$this->assertEmpty( EDD()->notifications->getActiveNotifications() );
	}

	/**
	 * @covers \EDD\Database\NotificationsDB::getActiveNotifications
	 */
	public function test_end_date_in_past_not_included_in_active() {
		EDD()->notifications->insert( array(
			'title'   => 'Notification',
			'content' => 'Notification',
			'type'    => 'success',
			'end'     => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
		), 'notification' );

		$this->assertEmpty( EDD()->notifications->getActiveNotifications() );
	}

	/**
	 * @covers \EDD\Database\NotificationsDB::getActiveNotifications
	 */
	public function test_end_date_in_future_included_in_active() {
		// Ends in 1 week - still valid.
		EDD()->notifications->insert( array(
			'title'   => 'Notification',
			'content' => 'Notification',
			'type'    => 'success',
			'end'     => date( 'Y-m-d H:i:s', strtotime( '+1 week' ) ),
		), 'notification' );

		// Ended 1 week ago - should not be in results.
		EDD()->notifications->insert( array(
			'title'   => 'Notification',
			'content' => 'Notification',
			'type'    => 'success',
			'end'     => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
		), 'notification' );

		$notifications = EDD()->notifications->getActiveNotifications();

		$this->assertSame( 1, count( $notifications ) );
	}

}
