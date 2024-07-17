<?php
/**
 * Represents a notification.
 *
 * This class is an abstract base class for all notifications in the application.
 * It provides common functionality and properties that are shared among different types of notifications.
 *
 * @package EDD
 * @subpackage Admin\Notifications
 * @since 3.3.0
 */

namespace EDD\Admin\Notifications;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents a notification.
 *
 * @since 3.3.0
 */
abstract class Notification {

	/**
	 * The ID of the notification.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	protected static $id;

	/**
	 * The type of notification.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected static $type = 'info';

	/**
	 * Adds a new notification.
	 *
	 * @return void
	 */
	public static function add() {
		if ( static::notification_exists() ) {
			return;
		}

		if ( ! static::can_register() ) {
			return;
		}

		$args = self::get_notification_args();
		if ( empty( $args ) ) {
			return;
		}

		EDD()->notifications->maybe_add_local_notification( $args );
	}

	/**
	 * Determines whether the notification exists.
	 *
	 * @since 3.3.0
	 * @return bool True if the notification exists, false otherwise.
	 */
	protected static function notification_exists() {
		$query = new \EDD\Database\Queries\Notification();

		return (bool) $query->get_item_by( 'remote_id', static::$id );
	}

	/**
	 * Registers the notification.
	 *
	 * @since 3.3.0
	 * @return array The registered notification.
	 */
	abstract protected static function register(): array;

	/**
	 * Determines whether the notification can be registered.
	 *
	 * @since 3.3.0
	 * @return bool True if the notification can be registered, false otherwise.
	 */
	abstract public static function can_register(): bool;

	/**
	 * Retrieves the notification arguments.
	 *
	 * @since 3.3.0
	 * @return array The notification arguments.
	 */
	private static function get_notification_args(): array {
		$args = static::register();
		if ( empty( $args ) ) {
			return array();
		}

		return wp_parse_args(
			$args,
			array(
				'type'      => static::$type,
				'remote_id' => static::$id,
			)
		);
	}
}
