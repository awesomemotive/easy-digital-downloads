<?php
/**
 * Notification Model Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Notifications;

use EDD\Models\Notification;

/**
 * @coversDefaultClass \EDD\Models\Notification
 * @group edd_notifications
 */
class NotificationModelTests extends \EDD_UnitTestCase {

	private function insertAndGetNotification( $data ) {
		$notificationId = EDD()->notifications->insert( $data, 'notification' );

		return new Notification( EDD()->notifications->get( $notificationId ) );
	}

	/**
	 * The `dismissed` property should be cast to boolean.
	 *
	 * @covers \EDD\Models\Notification::castAttribute
	 */
	public function test_dismissed_is_boolean() {
		$notification = $this->insertAndGetNotification( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'type'      => 'success',
			'dismissed' => 0,
		) );

		$this->assertFalse( $notification->dismissed );
	}

	/**
	 * The `id` property should be cast to an integer.
	 *
	 * @covers \EDD\Models\Notification::castAttribute
	 */
	public function test_id_is_integer() {
		$notification = $this->insertAndGetNotification( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'type'      => 'success',
			'dismissed' => 0,
		) );

		$this->assertTrue( is_int( $notification->id ) );
	}

	/**
	 * The `buttons` property should be cast to an array if not empty.
	 *
	 * @covers \EDD\Models\Notification::castAttribute
	 */
	public function test_non_empty_buttons_is_array() {
		$notification = $this->insertAndGetNotification( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'buttons'   => array(
				array(
					'type' => 'primary',
					'url'  => 'https://easydigitaldownloads.com',
					'text' => 'Learn More',
				)
			),
			'type'      => 'success',
			'dismissed' => 0,
		) );

		$this->assertTrue( is_array( $notification->buttons ) );
		$this->assertSame( 'Learn More', $notification->buttons[0]['text'] );
	}

	/**
	 * The `buttons` property should be null if no data.
	 *
	 * @covers \EDD\Models\Notification::castAttribute
	 */
	public function test_empty_buttons_is_null() {
		$notification = $this->insertAndGetNotification( array(
			'title'     => 'Notification',
			'content'   => 'Notification',
			'buttons'   => null,
			'type'      => 'success',
			'dismissed' => 0,
		) );

		$this->assertTrue( is_null( $notification->buttons ) );
	}

}
