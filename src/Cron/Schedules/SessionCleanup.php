<?php
/**
 * Session Cleanup Schedule
 *
 * @package EDD\Cron\Schedules
 * @since 3.3.0
 */

namespace EDD\Cron\Schedules;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Session Cleanup Schedule
 */
class SessionCleanup extends Schedule {
	/**
	 * The schedule ID.
	 *
	 * @var string
	 */
	public $id = 'edd_cleanup_sessions';

	/**
	 * The interval in seconds.
	 *
	 * @var int
	 */
	public $interval = 6 * HOUR_IN_SECONDS;

	/**
	 * Get the display name for the schedule.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	protected function get_display_name(): string {
		return __( 'Easy Digital Downloads Session Cleanup', 'easy-digital-downloads' );
	}
}
