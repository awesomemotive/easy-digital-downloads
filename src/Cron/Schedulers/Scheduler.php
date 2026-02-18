<?php
/**
 * Scheduler Interface
 *
 * Defines the contract for cron scheduler implementations.
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
 * Scheduler Interface
 *
 * Provides a unified interface for different cron scheduling systems.
 *
 * @since 3.6.5
 */
interface Scheduler {

	/**
	 * Schedule a recurring event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp The first time the event should run.
	 * @param int    $interval  How often the event should recur (in seconds).
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier.
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_recurring( string $hook, int $timestamp, int $interval, array $args = array(), string $group = '' ): bool;

	/**
	 * Schedule a single event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp When the event should run.
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier.
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_single( string $hook, int $timestamp, array $args = array(), string $group = '' ): bool;

	/**
	 * Get the next scheduled time for an event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to check.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier.
	 * @return int|false The next scheduled timestamp, or false if not scheduled.
	 */
	public function next_scheduled( string $hook, array $args = array(), string $group = '' );

	/**
	 * Unschedule a specific event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to unschedule.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier.
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule( string $hook, array $args = array(), string $group = '' ): bool;

	/**
	 * Unschedule all events matching the criteria.
	 *
	 * @since 3.6.5
	 *
	 * @param string|null $hook  Optional hook name to filter by.
	 * @param array|null  $args  Optional arguments to match. Pass null to match any args, or array to match specific args.
	 * @param string      $group Optional group identifier.
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule_all( ?string $hook = null, $args = null, string $group = '' ): bool;

	/**
	 * Get all scheduled hooks, optionally filtered by prefix.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook_prefix Optional. Hook name prefix to filter by (e.g., 'edd_prune_logs_'). Default empty (all hooks).
	 * @param int    $limit       Optional. Maximum number of hooks to return. Default 1000.
	 * @param string $group       Optional group identifier.
	 * @return array Array of hook names (strings).
	 */
	public function get_scheduled_hooks( string $hook_prefix = '', int $limit = 1000, string $group = '' ): array;

	/**
	 * Search for scheduled actions.
	 *
	 * @since 3.6.5
	 *
	 * @param array  $args         Query arguments. See implementation for supported args.
	 * @param string $return_format Return format: 'ids', 'objects', or 'OBJECT'. Default 'objects'.
	 * @return array Array of action IDs or objects.
	 */
	public function search( array $args = array(), string $return_format = 'objects' ): array;

	/**
	 * Check if this scheduler is available.
	 *
	 * @since 3.6.5
	 *
	 * @return bool True if the scheduler is available, false otherwise.
	 */
	public static function is_available(): bool;
}
