<?php
/**
 * Handles the notifications request via cron.
 *
 * @package EDD
 * @subpackage Cron/Components
 * @since 3.3.0
 */

namespace EDD\Cron\Components;

use EDD\Utils\NotificationImporter;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notifications Class
 *
 * @since 3.3.0
 */
class Notifications extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @var string
	 */
	protected static $id = 'notifications';

	/**
	 * Register the event to run.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_daily_scheduled_events' => 'get_notifications',
		);
	}

	/**
	 * Get the notifications and send them.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function get_notifications() {
		$importer = new NotificationImporter();
		$importer->run();
	}
}
