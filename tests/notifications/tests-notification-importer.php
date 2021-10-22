<?php
/**
 * Notification Importer Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Notifications;

use EDD\Utils\NotificationImporter;

/**
 * @coversDefaultClass \EDD\Utils\NotificationImporter
 * @group edd_notifications
 */
class NotificationImporterTests extends \EDD_UnitTestCase {

	/**
	 * Truncates the notification table before each test.
	 */
	public function setUp() {
		parent::setUp();

		global $wpdb;
		$tableName = EDD()->notifications->table_name;

		$wpdb->query( "TRUNCATE TABLE {$tableName}" );
	}

	/**
	 * Builds a mock of the NotificationImporter class, overriding the return
	 * value of fetchNotifications().
	 *
	 * @return NotificationImporter
	 */
	protected function getMockImporter( $returnValue = array() ) {
		$mock = $this->getMockBuilder( '\\EDD\\Utils\\NotificationImporter' )
		             ->setMethods( array( 'fetchNotifications' ) )
		             ->getMock();

		// Doing a json_decode / encode here so that we end up with an array of objects.
		$mock->method( 'fetchNotifications' )
		     ->willReturn( json_decode( json_encode( $returnValue ) ) );

		return $mock;
	}

	/**
	 * Returns all notifications in the database.
	 *
	 * @return object[]
	 */
	protected function getNotifications() {
		global $wpdb;
		$tableName = EDD()->notifications->table_name;

		return $wpdb->get_results( "SELECT * FROM {$tableName}" );
	}

	/**
	 * @covers \EDD\Utils\NotificationImporter::insertNewNotification
	 */
	public function test_valid_notification_is_imported() {
		$importer = $this->getMockImporter( array(
			array(
				'title'             => 'Announcing New EDD Feature',
				'content'           => 'This is an exciting new EDD feature.',
				'id'                => 90,
				'start'             => null,
				'notification_type' => 'success',
			)
		) );

		$importer->run();

		$notifications = $this->getNotifications();

		$this->assertSame( 1, count( $notifications ) );

		$this->assertSame( 'Announcing New EDD Feature', $notifications[0]->title );
		$this->assertSame( 'This is an exciting new EDD feature.', $notifications[0]->content );
		$this->assertEquals( 90, $notifications[0]->remote_id );
		$this->assertSame( null, $notifications[0]->start );
		$this->assertSame( 'success', $notifications[0]->type );
	}

	/**
	 * @covers \EDD\Utils\NotificationImporter::validateNotification
	 */
	public function test_notification_with_no_title_not_imported() {
		$importer = $this->getMockImporter( array(
			array(
				// title is missing
				'content'           => 'This is an exciting new EDD feature.',
				'id'                => 90,
				'start'             => null,
				'notification_type' => 'success',
			)
		) );

		$importer->run();

		$notifications = $this->getNotifications();

		$this->assertSame( 0, count( $notifications ) );
	}

	/**
	 * @covers \EDD\Utils\NotificationImporter::updateExistingNotification
	 */
	public function test_existing_notification_updated_with_new_content() {
		$importer = $this->getMockImporter( array(
			array(
				'title'             => 'Announcing New EDD Feature',
				'content'           => 'This is an exciting new EDD feature.',
				'id'                => 90,
				'start'             => null,
				'notification_type' => 'success',
			)
		) );

		$importer->run();

		$notifications = $this->getNotifications();
		$this->assertSame( 'This is an exciting new EDD feature.', $notifications[0]->content );

		$importer = $this->getMockImporter( array(
			array(
				'title'             => 'Announcing New EDD Feature',
				'content'           => 'This is an exciting new EDD feature with updated content.',
				'id'                => 90,
				'start'             => null,
				'notification_type' => 'success',
			)
		) );

		$importer->run();

		$notifications = $this->getNotifications();
		$this->assertSame( 'This is an exciting new EDD feature with updated content.', $notifications[0]->content );
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Notification has expired.
	 * @throws \Exception
	 */
	public function test_ended_notification_doesnt_validate() {
		$importer = new NotificationImporter();

		if ( method_exists( $this, 'setExpectedException' ) ) {
			$this->setExpectedException( 'Exception', 'Notification has expired.' );
		}

		$notification                    = new \stdClass();
		$notification->title             = 'Announcing New EDD Feature';
		$notification->content           = 'This is an exciting new EDD feature.';
		$notification->id                = 90;
		$notification->end               = date( 'Y-m-d H:i:s', strtotime( '-2 days' ) );
		$notification->notification_type = 'success';

		$importer->validateNotification( $notification );
	}

	/**
	 * @expectedException \Exception
	 * @throws \Exception
	 */
	public function test_notification_missing_properties_doesnt_validate() {
		$importer = new NotificationImporter();

		if ( method_exists( $this, 'setExpectedException' ) ) {
			$this->setExpectedException( 'Exception', 'Missing required properties: ["title"]' );
		}

		$notification                    = new \stdClass();
		$notification->content           = 'This is an exciting new EDD feature.';
		$notification->id                = 90;
		$notification->end               = date( 'Y-m-d H:i:s', strtotime( '-2 days' ) );
		$notification->notification_type = 'success';

		$importer->validateNotification( $notification );
	}

}
