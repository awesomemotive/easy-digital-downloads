<?php
/**
 * WP-Cron Scheduler Implementation
 *
 * Implements the SchedulerInterface using WordPress's built-in WP-Cron system.
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
 * WP-Cron Scheduler Class
 *
 * Provides a unified interface for WordPress's native cron system.
 *
 * @since 3.6.5
 */
class WPCronScheduler implements Scheduler {

	/**
	 * Schedule a recurring event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp The first time the event should run.
	 * @param int    $interval  How often the event should recur (in seconds).
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier (unused in WP-Cron).
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_recurring( string $hook, int $timestamp, int $interval, array $args = array(), string $group = '' ): bool {
		// Check if already scheduled to avoid duplicates.
		if ( $this->next_scheduled( $hook, $args, $group ) ) {
			return true;
		}

		// Get or create a schedule name for this interval.
		$schedule_name = $this->get_schedule_for_interval( $interval );

		// Schedule the event.
		$result = wp_schedule_event( $timestamp, $schedule_name, $hook, $args );

		return false !== $result;
	}

	/**
	 * Schedule a single event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp When the event should run.
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier (unused in WP-Cron).
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_single( string $hook, int $timestamp, array $args = array(), string $group = '' ): bool {
		// Check if already scheduled to avoid duplicates.
		if ( $this->next_scheduled( $hook, $args, $group ) ) {
			return true;
		}

		// Schedule the single event.
		$result = wp_schedule_single_event( $timestamp, $hook, $args );

		return false !== $result;
	}

	/**
	 * Get the next scheduled time for an event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to check.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier (unused in WP-Cron).
	 * @return int|false The next scheduled timestamp, or false if not scheduled.
	 */
	public function next_scheduled( string $hook, array $args = array(), string $group = '' ) {
		return wp_next_scheduled( $hook, $args );
	}

	/**
	 * Unschedule a specific event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to unschedule.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier (unused in WP-Cron).
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule( string $hook, array $args = array(), string $group = '' ): bool {
		$timestamp = $this->next_scheduled( $hook, $args, $group );

		if ( false === $timestamp ) {
			return true; // Already unscheduled.
		}

		return wp_unschedule_event( $timestamp, $hook, $args );
	}

	/**
	 * Unschedule all events matching the criteria.
	 *
	 * @since 3.6.5
	 *
	 * @param string|null $hook  Optional hook name to filter by.
	 * @param array|null  $args  Optional arguments to match. Pass null to match any args, or array to match specific args.
	 * @param string      $group Optional group identifier (unused in WP-Cron).
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule_all( ?string $hook = null, $args = null, string $group = '' ): bool {
		if ( null === $hook ) {
			// Cannot clear all cron events without a hook in WP-Cron.
			return false;
		}

		// When args is null, we need to remove ALL occurrences regardless of their arguments.
		// Note: wp_clear_scheduled_hook() only removes events with no arguments, not events with arguments.
		// So we need to get all scheduled events for this hook and remove them individually.
		if ( null === $args ) {
			$crons = _get_cron_array();
			if ( empty( $crons ) ) {
				return true;
			}

			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron[ $hook ] ) ) {
					foreach ( $cron[ $hook ] as $event ) {
						wp_unschedule_event( $timestamp, $hook, $event['args'] );
					}
				}
			}

			return true; // Return true even if nothing was found.
		}

		// For specific args (including empty array), loop through all scheduled instances.
		// This ensures array() only matches actions with empty args, not all args.
		$timestamp = $this->next_scheduled( $hook, $args, $group );
		while ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, $hook, $args );
			$timestamp = $this->next_scheduled( $hook, $args, $group );
		}

		return true;
	}

	/**
	 * Get all scheduled hooks, optionally filtered by prefix.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook_prefix Optional. Hook name prefix to filter by (e.g., 'edd_prune_logs_'). Default empty (all hooks).
	 * @param int    $limit       Optional. Maximum number of hooks to return. Default 1000.
	 * @param string $group       Optional group identifier (unused in WP-Cron).
	 * @return array Array of hook names (strings).
	 */
	public function get_scheduled_hooks( string $hook_prefix = '', int $limit = 1000, string $group = '' ): array {
		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return array();
		}

		$hooks = array();
		$count = 0;

		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $hook => $args ) {
				// Filter by prefix if provided.
				if ( ! empty( $hook_prefix ) && 0 !== strpos( $hook, $hook_prefix ) ) {
					continue;
				}

				$hooks[] = $hook;
				++$count;

				// Respect the limit.
				if ( $count >= $limit ) {
					break 2;
				}
			}
		}

		return array_unique( $hooks );
	}

	/**
	 * Search for scheduled actions.
	 *
	 * Note: WP-Cron has limited search capabilities compared to Action Scheduler.
	 * Only 'hook', 'per_page', and 'status' (pending only) are supported.
	 *
	 * @since 3.6.5
	 *
	 * @param array  $args         Query arguments. Supported args:
	 *        'hook' => '' - Filter by hook name (required for meaningful results)
	 *        'per_page' => 100 - Maximum number of results to return
	 *        Other arguments are ignored as WP-Cron doesn't support advanced querying.
	 * @param string $return_format Return format: 'ids' or 'objects'. Default 'objects'.
	 *                              Note: WP-Cron returns simplified objects with limited data.
	 * @return array Array of action data based on return format.
	 */
	public function search( array $args = array(), string $return_format = 'objects' ): array {
		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return array();
		}

		$hook     = $args['hook'] ?? '';
		$per_page = $args['per_page'] ?? 100;
		$results  = array();
		$count    = 0;

		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $cron_hook => $events ) {
				// Filter by hook if provided.
				if ( ! empty( $hook ) && $hook !== $cron_hook ) {
					continue;
				}

				foreach ( $events as $key => $event ) {
					if ( $count >= $per_page ) {
						break 3;
					}

					if ( 'ids' === $return_format ) {
						// WP-Cron doesn't have IDs, so create a pseudo-ID.
						$results[] = md5( $cron_hook . serialize( $event['args'] ) . $timestamp );
					} else {
						// Return a simplified object with basic info.
						$results[] = (object) array(
							'hook'      => $cron_hook,
							'args'      => $event['args'],
							'timestamp' => $timestamp,
							'schedule'  => $event['schedule'] ?? false,
						);
					}

					++$count;
				}
			}
		}

		return $results;
	}

	/**
	 * Check if WP-Cron is available.
	 *
	 * @since 3.6.5
	 *
	 * @return bool Always returns true as WP-Cron is always available in WordPress.
	 */
	public static function is_available(): bool {
		return true;
	}

	/**
	 * Get or create a schedule name for a given interval.
	 *
	 * @since 3.6.5
	 *
	 * @param int $interval The interval in seconds.
	 * @return string The schedule name.
	 */
	private function get_schedule_for_interval( int $interval ): string {
		$schedules = wp_get_schedules();

		// Check if a matching schedule already exists.
		foreach ( $schedules as $name => $schedule ) {
			if ( isset( $schedule['interval'] ) && (int) $schedule['interval'] === $interval ) {
				return $name;
			}
		}

		// Standard WordPress schedules.
		if ( HOUR_IN_SECONDS === $interval ) {
			return 'hourly';
		}

		if ( 12 * HOUR_IN_SECONDS === $interval ) {
			return 'twicedaily';
		}

		if ( DAY_IN_SECONDS === $interval ) {
			return 'daily';
		}

		if ( WEEK_IN_SECONDS === $interval ) {
			return 'weekly';
		}

		edd_debug_log(
			sprintf(
				'[EDD Cron] No matching schedule for interval %d seconds, defaulting to daily',
				$interval
			)
		);

		// For custom intervals, we'll need to register a schedule
		// This will be handled by the Schedule classes registering custom intervals
		// For now, default to daily if no match.
		return 'daily';
	}
}
