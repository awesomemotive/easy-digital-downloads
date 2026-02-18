<?php
/**
 * Log Pruning Daily Event
 *
 * @package     EDD\Cron\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Cron\Events;

use EDD\Cron\Traits\Clear;
use EDD\Cron\Schedulers\Handler;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * LogPruning Event Class
 *
 * Schedules separate daily cron events for each log type at randomized times
 * throughout the day to avoid database strain from simultaneous operations.
 *
 * @since 3.6.4
 */
class LogPruning extends Event {
	use Clear;

	/**
	 * Hook name.
	 *
	 * This is a base hook - individual log types will have their own hooks.
	 *
	 * @since 3.6.4
	 * @var string
	 */
	protected $hook = 'edd_prune_logs';

	/**
	 * First Run Time.
	 *
	 * Not used directly - calculated per log type.
	 *
	 * @since 3.6.4
	 * @var int
	 */
	protected $first_run = 0;

	/**
	 * Schedule.
	 *
	 * The registered WP Cron schedule to use.
	 *
	 * @since 3.6.4
	 * @var string
	 */
	protected $schedule = 'daily';

	/**
	 * Schedule events for all enabled log types.
	 *
	 * Overrides the parent schedule() method to create separate events
	 * for each log type at staggered times.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function schedule() {
		if ( ! $this->valid ) {
			return;
		}

		$log_types = \EDD\Logs\Registry::get_types();
		$settings  = edd_get_option( 'edd_log_pruning_settings', array() );

		// Track which type IDs should have scheduled events.
		$scheduled_type_ids = array();

		// Calculate base time (tomorrow at a random time between 1-6 AM UTC).
		$base_time = $this->calculate_base_time();

		// Stagger each log type by 15-45 minutes from the previous one.
		$time_offset = 0;

		// Schedule registered log types that are enabled.
		foreach ( $log_types as $type_id => $type_config ) {
			// Skip if not prunable.
			if ( empty( $type_config['prunable'] ) ) {
				continue;
			}

			// Only schedule if enabled for this specific type.
			if ( empty( $settings['log_types'][ $type_id ]['enabled'] ) ) {
				continue;
			}

			$this->schedule_single_type( $type_id, $base_time, $time_offset );
			$scheduled_type_ids[] = $type_id;

			// Add random stagger between 15-45 minutes for next log type.
			$time_offset += $this->get_random_offset();
		}

		// Schedule unregistered log types that are enabled.
		$additional_types = \EDD\Logs\Registry::get_additional_log_types( false, true );
		foreach ( $additional_types as $type_id => $type_config ) {
			// Only schedule if enabled.
			if ( ! empty( $settings['log_types'][ $type_id ]['enabled'] ) ) {
				$this->schedule_single_type( $type_id, $base_time, $time_offset );
				$scheduled_type_ids[] = $type_id;

				// Add random stagger between 15-45 minutes for next log type.
				$time_offset += $this->get_random_offset();
			}
		}

		// Clean up orphaned cron events for types that are no longer enabled.
		$this->cleanup_orphaned_events( $scheduled_type_ids );
	}

	/**
	 * Clean up orphaned cron events.
	 *
	 * Removes cron events for log types that are no longer enabled or registered.
	 *
	 * @since 3.6.4
	 *
	 * @param array $valid_type_ids Array of type IDs that should have cron events.
	 * @return void
	 */
	private function cleanup_orphaned_events( array $valid_type_ids ) {
		$scheduler = Handler::get_scheduler();

		// Get all scheduled hooks with our log pruning prefix.
		$hooks = $scheduler->get_scheduled_hooks( 'edd_prune_logs_', 1000 );

		foreach ( $hooks as $hook ) {
			// Extract the type ID from the hook name.
			$type_id = str_replace( 'edd_prune_logs_', '', $hook );

			// If this type isn't in our valid list, clear it.
			if ( ! in_array( $type_id, $valid_type_ids, true ) ) {
				self::clear( $hook );
			}
		}
	}

	/**
	 * Schedule a single log type's cron event.
	 *
	 * Uses the active scheduler (Action Scheduler or WP-Cron).
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id     Log type ID.
	 * @param int    $base_time   Base timestamp for scheduling.
	 * @param int    $time_offset Offset in seconds from base time.
	 * @return void
	 */
	private function schedule_single_type( $type_id, $base_time, $time_offset ) {
		// Create hook name for this log type.
		$hook = "edd_prune_logs_{$type_id}";

		// Check if already scheduled.
		if ( $this->next_scheduled( $hook, array() ) ) {
			return;
		}

		// Calculate this log type's run time with staggered offset.
		$first_run = $base_time + $time_offset;

		// Get the scheduler and convert schedule to interval.
		$scheduler = Handler::get_scheduler();
		$interval  = Handler::get_schedule_interval( $this->schedule );

		if ( false === $interval ) {
			edd_debug_log(
				sprintf(
					'[EDD Cron] Could not get interval for schedule "%s" when scheduling log pruning for type "%s"',
					$this->schedule,
					$type_id
				)
			);
			return;
		}

		$scheduler->schedule_recurring( $hook, $first_run, $interval );
	}

	/**
	 * Calculate the base time for scheduling.
	 *
	 * Returns a random time between 1-6 AM UTC tomorrow. The base time is cached
	 * in a transient to prevent schedule drift when events are rescheduled.
	 *
	 * @since 3.6.4
	 * @return int Unix timestamp.
	 */
	private function calculate_base_time(): int {
		// Use a date-based transient key to ensure consistency within a scheduling cycle.
		$transient_key = 'edd_log_prune_base_time_' . gmdate( 'Y-m-d' );
		$cached_time   = get_transient( $transient_key );

		if ( false !== $cached_time ) {
			return (int) $cached_time;
		}

		// Get tomorrow at midnight UTC.
		$tomorrow = strtotime( 'tomorrow midnight UTC' );

		// Add random offset between 1-6 hours (3600-21600 seconds).
		$random_offset = $this->get_random_offset( 3600, 21600 );
		$base_time     = $tomorrow + $random_offset;

		// Cache for 24 hours to maintain consistency if events are rescheduled.
		set_transient( $transient_key, $base_time, DAY_IN_SECONDS );

		return $base_time;
	}

	/**
	 * Get a random time offset in seconds.
	 *
	 * @since 3.6.4
	 *
	 * @param int $min Minimum offset in seconds.
	 * @param int $max Maximum offset in seconds.
	 * @return int Random offset in seconds.
	 */
	private function get_random_offset( $min = 900, $max = 2700 ): int {
		return random_int( $min, $max );
	}
}
