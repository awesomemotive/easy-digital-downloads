<?php
/**
 * Trait to clear items hooked into a scheduled event.
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
 * Clear Trait
 */
trait Clear {
	/**
	 * Clear the scheduled event.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The hook name.
	 * @param array  $args The arguments to pass to the hook.
	 */
	public static function clear( $hook = '', $args = array() ) {
		$timestamp = wp_next_scheduled( $hook, $args );

		if ( $timestamp ) {
			wp_clear_scheduled_hook( $hook, $args );
		}
	}
}
