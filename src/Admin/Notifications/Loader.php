<?php
/**
 * Loader for Notifications.
 *
 * @since 3.3.0
 * @package EDD
 */

namespace EDD\Admin\Notifications;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\EventManagement\EventManager;

/**
 * Class Loader
 *
 * This class implements the SubscriberInterface and is responsible for loading the notifications in the admin area.
 */
class Loader implements SubscriberInterface {

	/**
	 * Returns the list of events to which this class subscribes.
	 *
	 * @since 3.3.0
	 * @return array The list of subscribed events.
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_footer' => 'add_events',
		);
	}

	/**
	 * Adds events for the Notifications Loader.
	 *
	 * @return void
	 */
	public function add_events() {
		$notification_classes = $this->get_notifications();
		foreach ( $notification_classes as $notification_class ) {
			if ( ! is_subclass_of( $notification_class, Notification::class ) ) {
				continue;
			}

			$notification_class::add();
		}
	}

	/**
	 * Retrieves the notifications.
	 *
	 * This method is responsible for retrieving the notifications.
	 *
	 * @since 3.3.0
	 * @return array The notifications.
	 */
	private function get_notifications() {
		return array(
			GBLegacy::class,
		);
	}
}
