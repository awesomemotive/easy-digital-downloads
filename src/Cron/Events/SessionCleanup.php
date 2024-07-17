<?php
/**
 * Session Cleanup Event
 *
 * @since 3.3.0
 *
 * @package EDD\Cron\Events
 */

namespace EDD\Cron\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Session Cleanup Event
 *
 * @since 3.3.0
 */
class SessionCleanup extends Event {
	/**
	 * Hook name.
	 *
	 * The hook that will fire when the Cron event is run.
	 *
	 * @var string
	 */
	protected $hook = 'edd_cleanup_sessions';

	/**
	 * First Run Time.
	 *
	 * The UTC timestamp to run the event for the first time.
	 *
	 * @var int
	 */
	protected $first_run = 0;

	/**
	 * Schedule.
	 *
	 * The registered WP Cron schedule to use.
	 *
	 * @var string
	 */
	protected $schedule = 'edd_cleanup_sessions';
}
