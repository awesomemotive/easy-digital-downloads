<?php
/**
 * Trait to look for next scheduled event.
 *
 * @since 3.3.0
 *
 * @package EDD
 * @subpackage Cron/Traits
 */

namespace EDD\Cron\Traits;

use EDD\Cron\Schedulers\Handler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * NextScheduled Trait
 */
trait NextScheduled {
	/**
	 * Get the timestamp of the next scheduled event.
	 *
	 * Uses the active scheduler.
	 *
	 * @since 3.3.0
	 * @since 3.6.5 Uses the appropriate scheduler (Action Scheduler or WP-Cron) to check
	 * if an event is already scheduled.
	 *
	 * @param string $hook The hook name.
	 * @param array  $args The arguments to pass to the hook.
	 *
	 * @return int|bool The timestamp of the next scheduled event or false if not scheduled.
	 */
	public static function next_scheduled( $hook = '', $args = array() ) {
		$scheduler = Handler::get_scheduler();
		return $scheduler->next_scheduled( $hook, $args );
	}
}
