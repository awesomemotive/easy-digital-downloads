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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * NextScheduled Trait
 */
trait NextScheduled {
	/**
	 * Get the timestamp of the next scheduled event.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The hook name.
	 * @param array  $args The arguments to pass to the hook.
	 *
	 * @return int|bool The timestamp of the next scheduled event or false if not scheduled.
	 */
	public static function next_scheduled( $hook = '', $args = array() ) {
		return wp_next_scheduled( $hook, $args );
	}
}
