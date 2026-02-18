<?php
/**
 * Scheduler Factory
 *
 * Determines and returns the appropriate scheduler implementation.
 *
 * @package EDD\Cron\Schedulers
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since   3.6.5
 */

namespace EDD\Cron\Schedulers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handler Class
 *
 * Factory for creating and managing scheduler instances.
 *
 * @since 3.6.5
 */
class Handler {

	/**
	 * Cached scheduler instance.
	 *
	 * @since 3.6.5
	 * @var Scheduler|null
	 */
	private static $scheduler = null;

	/**
	 * Get the active scheduler instance.
	 *
	 * @since 3.6.5
	 *
	 * @return Scheduler The active scheduler instance.
	 */
	public static function get_scheduler(): Scheduler {
		if ( null === self::$scheduler ) {
			self::$scheduler = self::create_scheduler();
		}

		return self::$scheduler;
	}

	/**
	 * Create the appropriate scheduler instance.
	 *
	 * @since 3.6.5
	 *
	 * @return Scheduler The scheduler instance.
	 */
	private static function create_scheduler(): Scheduler {
		// Check if Action Scheduler is available and preferred.
		if ( ActionScheduler::is_available() && self::should_use_action_scheduler() ) {
			return new ActionScheduler();
		}

		// Fall back to WP-Cron.
		return new WPCronScheduler();
	}

	/**
	 * Determine if Action Scheduler should be used.
	 *
	 * @since 3.6.5
	 *
	 * @return bool True if Action Scheduler should be used, false otherwise.
	 */
	private static function should_use_action_scheduler(): bool {
		/**
		 * Filter whether to use Action Scheduler for cron events.
		 *
		 * @since 3.6.5
		 * @param bool $use_action_scheduler Whether to use Action Scheduler. Default true if available.
		 */
		return apply_filters( 'edd_use_action_scheduler', true );
	}

	/**
	 * Get the name of the active scheduler.
	 *
	 * @since 3.6.5
	 *
	 * @return string 'action-scheduler' or 'wp-cron'.
	 */
	public static function get_active_scheduler_name(): string {
		$scheduler = self::get_scheduler();

		if ( $scheduler instanceof ActionScheduler ) {
			return 'action-scheduler';
		}

		return 'wp-cron';
	}

	/**
	 * Check if Action Scheduler is the active scheduler.
	 *
	 * @since 3.6.5
	 *
	 * @return bool True if Action Scheduler is active, false otherwise.
	 */
	public static function is_using_action_scheduler(): bool {
		return self::get_scheduler() instanceof ActionScheduler;
	}

	/**
	 * Reset the cached scheduler instance.
	 *
	 * Useful for testing or when switching schedulers.
	 *
	 * @since 3.6.5
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$scheduler = null;
	}

	/**
	 * Get the interval in seconds for a named schedule.
	 *
	 * Uses wp_get_schedules() first (core and any added via cron_schedules when using
	 * WP-Cron), then the edd_cron_schedules filter for EDD custom schedules (e.g. when
	 * using Action Scheduler and EDD schedules are not added to cron_schedules).
	 *
	 * @since 3.6.5
	 *
	 * @param string $schedule The schedule name (e.g. 'daily', 'edd_acr_sched_detection').
	 * @return int|false The interval in seconds, or false if not found.
	 */
	public static function get_schedule_interval( string $schedule ) {
		$schedules = wp_get_schedules();

		if ( isset( $schedules[ $schedule ]['interval'] ) ) {
			return absint( $schedules[ $schedule ]['interval'] );
		}

		// EDD custom schedules when not in cron_schedules (e.g. when using Action Scheduler).
		$edd_schedules = apply_filters( 'edd_cron_schedules', array() );
		if ( is_array( $edd_schedules ) ) {
			foreach ( $edd_schedules as $s ) {
				if ( is_object( $s ) && isset( $s->id, $s->interval ) && $schedule === $s->id ) {
					return absint( $s->interval );
				}
			}
		}

		return false;
	}
}
