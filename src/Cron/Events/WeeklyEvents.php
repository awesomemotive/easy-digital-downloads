<?php
/**
 * Weekly Events
 *
 * @since 1.6 Originally in EDD_Cron
 * @since 3.3.0 Moved to EDD\Cron\Events
 *
 * @package EDD
 * @subpackage Cron\Events
 */

namespace EDD\Cron\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Weekly Events
 *
 * @since 1.6 Originally in EDD_Cron
 * @since 3.3.0
 */
class WeeklyEvents extends Event {
	/**
	 * Hook name.
	 *
	 * The hook that will fire when the Cron event is run.
	 *
	 * @var string
	 */
	protected $hook = 'edd_weekly_scheduled_events';

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
	protected $schedule = 'weekly';
}
