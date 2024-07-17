<?php
/**
 * Handles the Store cron events.
 *
 * These are events that are generally about the store information and overview.
 *
 * @package EDD
 * @subpackage Cron/Components
 * @since 3.3.0
 */

namespace EDD\Cron\Components;


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Store Class for Cron Events.
 *
 * @since 3.3.0
 */
class Store extends Component {

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	protected static $id = 'store';

	/**
	 * Register the event to run.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_weekly_scheduled_events' => 'send',
		);
	}

	/**
	 * Send the data.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function send() {
		EDD()->tracking->send_checkin();
	}
}
