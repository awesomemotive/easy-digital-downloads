<?php
/**
 * Trait to clear items hooked into a scheduled event.
 *
 * @package   EDD\Cron\Traits
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.0
 */

namespace EDD\Cron\Traits;

use EDD\Cron\Schedulers\Handler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Clear Trait
 */
trait Clear {
	/**
	 * Clear the scheduled event.
	 *
	 * Clears from the active scheduler.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The hook name.
	 * @param array  $args The arguments to pass to the hook.
	 */
	public static function clear( $hook = '', $args = array() ) {
		$scheduler = Handler::get_scheduler();
		$scheduler->unschedule_all( $hook, $args );
	}
}
