<?php

namespace EDD\Tests\Notifications;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\Database\NotificationsDB
 * @group edd_notifications
 */
class Notifications_Local extends EDD_UnitTestCase {

	public function test_add_local_notification_included_in_active() {
		EDD()->notifications->maybe_add_local_notification(
			array(
				'title'     => 'Notification',
				'content'   => 'Notification',
				'type'      => 'error',
				'dismissed' => 0,
				'remote_id' => 'local',
			)
		);

		$notifications = EDD()->notifications->getActiveNotifications();

		$this->assertSame( 1, count( $notifications ) );
		$this->assertTrue( $notifications[0] instanceof Notification );
		$this->assertSame( 'Notification', $notifications[0]->title );
	}

	public function test_add_local_notification_numeric_remote_id_returns_false() {
		$notification = EDD()->notifications->maybe_add_local_notification(
			array(
				'title'     => 'Notification',
				'content'   => 'Notification',
				'type'      => 'error',
				'dismissed' => 0,
				'remote_id' => 1,
			)
		);

		$this->assertFalse( $notification );
	}

	public function test_add_local_notification_missing_remote_id_returns_false() {
		$notification = EDD()->notifications->maybe_add_local_notification(
			array(
				'title'   => 'Notification',
				'content' => 'Notification',
				'type'    => 'error',
			)
		);

		$this->assertFalse( $notification );
	}
}
