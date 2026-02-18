<?php
/**
 * Action Scheduler Integration
 *
 * Provides a clean interface to Action Scheduler for EDD's cron system.
 *
 * @package EDD\Cron\Schedulers
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Cron\Schedulers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * ActionScheduler Class
 *
 * Implements the Scheduler interface using Action Scheduler.
 *
 * @since 3.6.5
 */
class ActionScheduler implements Scheduler {

	/**
	 * The group identifier for EDD actions.
	 *
	 * @var string
	 */
	const GROUP = 'edd';

	/**
	 * Check if Action Scheduler is available and initialized.
	 *
	 * Action Scheduler is loaded early in the main plugin file and initializes
	 * on the plugins_loaded hook, so it should be available by the time this is called.
	 *
	 * @since 3.6.5
	 *
	 * @return bool
	 */
	public static function is_available(): bool {
		return class_exists( 'ActionScheduler' ) && function_exists( 'as_schedule_recurring_action' );
	}

	/**
	 * Schedule a recurring event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp The first time the event should run.
	 * @param int    $interval  How often the event should recur (in seconds).
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier. Default 'edd'.
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_recurring( string $hook, int $timestamp, int $interval, array $args = array(), string $group = '' ): bool {
		if ( ! self::is_available() ) {
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		// Use has_scheduled (as_has_scheduled_action) per Action Scheduler API recommendation.
		if ( ! $this->has_scheduled( $hook, $args, $group ) ) {
			$action_id = as_schedule_recurring_action(
				$timestamp,
				$interval,
				$hook,
				$args,
				$group
			);

			return 0 !== $action_id;
		}

		// At least one pending; check for duplicates (e.g. from concurrent requests or branch switching).
		$pending       = as_get_scheduled_actions(
			array(
				'hook'     => $hook,
				'args'     => $args,
				'group'    => $group,
				'status'   => \ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 2,
			),
			'ids'
		);
		$pending_count = is_array( $pending ) ? count( $pending ) : 0;

		if ( $pending_count >= 2 ) {
			// Duplicates found: remove all and schedule a single recurring action.
			as_unschedule_all_actions( $hook, $args, $group );
		} else {
			// Exactly one pending; do not reschedule (avoids shifting run times).
			return true;
		}

		$action_id = as_schedule_recurring_action(
			$timestamp,
			$interval,
			$hook,
			$args,
			$group
		);

		return 0 !== $action_id;
	}

	/**
	 * Schedule a single event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook      The hook name to execute.
	 * @param int    $timestamp When the event should run.
	 * @param array  $args      Optional arguments to pass to the hook.
	 * @param string $group     Optional group identifier. Default 'edd'.
	 * @return bool True if successfully scheduled, false otherwise.
	 */
	public function schedule_single( string $hook, int $timestamp, array $args = array(), string $group = '' ): bool {
		if ( ! self::is_available() ) {
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		// Pre-check if already scheduled to provide idempotent behavior.
		//
		// When unique=true and an action already exists, as_schedule_single_action() returns 0
		// (which would make us return false). However, returning false would incorrectly signal
		// a failure when the action is actually successfully scheduled.
		//
		// By returning true when the action already exists, we provide idempotent "ensure scheduled"
		// semantics: calling this method multiple times with identical parameters will always return
		// true and result in exactly one scheduled action. This matches the behavior of schedule_recurring()
		// and prevents callers from incorrectly treating an already-scheduled action as an error.
		if ( $this->has_scheduled( $hook, $args, $group ) ) {
			return true;
		}

		$action_id = as_schedule_single_action(
			$timestamp,
			$hook,
			$args,
			$group
		);

		return 0 !== $action_id;
	}

	/**
	 * Get the next scheduled time for an event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to check.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier. Default 'edd'.
	 * @return int|false The next scheduled timestamp, or false if not scheduled.
	 */
	public function next_scheduled( string $hook, array $args = array(), string $group = '' ) {
		if ( ! self::is_available() ) {
			return false;
		}

		if ( ! \ActionScheduler::is_initialized( null ) ) {
			edd_debug_log( 'Action Scheduler was called too early by: ' . print_r( debug_backtrace(), true ) );
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		if ( ! as_has_scheduled_action( $hook, $args, $group ) ) {
			return false;
		}

		$next_timestamp = as_next_scheduled_action( $hook, $args, $group );
		if ( ! is_numeric( $next_timestamp ) ) {
			return false;
		}

		return (int) $next_timestamp;
	}

	/**
	 * Unschedule a specific event.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook name to unschedule.
	 * @param array  $args  Optional arguments to match.
	 * @param string $group Optional group identifier. Default 'edd'.
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule( string $hook, array $args = array(), string $group = '' ): bool {
		if ( ! self::is_available() ) {
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		$action_id = as_unschedule_action( $hook, $args, $group );

		return null !== $action_id;
	}

	/**
	 * Unschedule all events matching the criteria.
	 *
	 * @since 3.6.5
	 *
	 * @param string|null $hook  Optional hook name to filter by.
	 * @param array|null  $args  Optional arguments to match. Pass null to match any args, or array to match specific args.
	 * @param string      $group Optional group identifier. Default 'edd'.
	 * @return bool True if successfully unscheduled, false otherwise.
	 */
	public function unschedule_all( ?string $hook = null, $args = null, string $group = '' ): bool {
		if ( ! self::is_available() ) {
			return false;
		}

		if ( null === $hook ) {
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		// When $args is null, we want to match any args. Action Scheduler needs
		// null (not empty array) to match actions regardless of their args.
		// When $args is an array (even empty), it matches only actions with those exact args.
		as_unschedule_all_actions( $hook, $args, $group );

		return true;
	}

	/**
	 * Check if an action is scheduled (more efficient than next_scheduled).
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook  The hook of the action.
	 * @param array  $args  Optional. Args that have been passed to the action. Default empty array.
	 * @param string $group Optional. The group the action is assigned to. Default 'edd'.
	 *
	 * @return bool True if a matching action is pending or in-progress, false otherwise.
	 */
	public function has_scheduled( string $hook, array $args = array(), string $group = '' ): bool {
		if ( ! self::is_available() ) {
			return false;
		}

		$group = ! empty( $group ) ? $group : self::GROUP;

		return as_has_scheduled_action( $hook, $args, $group );
	}

	/**
	 * Get all scheduled hooks, optionally filtered by prefix.
	 *
	 * @since 3.6.5
	 *
	 * @param string $hook_prefix Optional. Hook name prefix to filter by (e.g., 'edd_prune_logs_'). Default empty (all hooks).
	 * @param int    $limit       Optional. Maximum number of hooks to return. Default 1000.
	 * @param string $group       Optional group identifier. Default 'edd'.
	 * @return array Array of hook names (strings).
	 */
	public function get_scheduled_hooks( string $hook_prefix = '', int $limit = 1000, string $group = '' ): array {
		if ( ! self::is_available() ) {
			return array();
		}

		$group   = ! empty( $group ) ? $group : self::GROUP;
		$actions = self::get_actions( $group, $limit );
		$hooks   = array();

		foreach ( $actions as $action ) {
			if ( ! is_object( $action ) || ! method_exists( $action, 'get_hook' ) ) {
				continue;
			}

			$hook = $action->get_hook();

			// Filter by prefix if provided.
			if ( ! empty( $hook_prefix ) && 0 !== strpos( $hook, $hook_prefix ) ) {
				continue;
			}

			$hooks[] = $hook;
		}

		return array_unique( $hooks );
	}

	/**
	 * Get all scheduled actions for a group.
	 *
	 * @since 3.6.5
	 *
	 * @param string $group   Optional. The group to get actions for. Default 'edd'.
	 * @param int    $per_page Optional. Number of results to return. Default 100.
	 *
	 * @return array Array of action objects.
	 */
	public static function get_actions( string $group = self::GROUP, int $per_page = 100 ): array {
		if ( ! self::is_available() ) {
			return array();
		}

		return as_get_scheduled_actions(
			array(
				'group'    => $group,
				'status'   => \ActionScheduler_Store::STATUS_PENDING,
				'per_page' => $per_page,
			),
			OBJECT
		);
	}

	/**
	 * Search for scheduled actions.
	 *
	 * @since 3.6.5
	 *
	 * @param array  $args         Possible arguments, with their default values:
	 *        'hook' => '' - the name of the action that will be triggered
	 *        'args' => null - the args array that will be passed with the action
	 *        'date' => null - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'date_compare' => '<=' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='
	 *        'modified' => null - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'modified_compare' => '<=' - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='
	 *        'group' => '' - the group the action belongs to
	 *        'status' => '' - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
	 *        'claimed' => null - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID
	 *        'per_page' => 5 - Number of results to return
	 *        'offset' => 0
	 *        'orderby' => 'date' - accepted values are 'hook', 'group', 'modified', or 'date'
	 *        'order' => 'ASC'.
	 * @param string $return_format Return format: 'ids' for IDs only, 'objects' or OBJECT for full objects. Default 'objects'.
	 * @return array Array of action IDs or objects based on return format.
	 */
	public function search( array $args = array(), string $return_format = 'objects' ): array {
		if ( ! self::is_available() ) {
			return array();
		}

		// Normalize return format to match as_get_scheduled_actions expectations.
		$format = ( 'ids' === $return_format ) ? 'ids' : OBJECT;

		// Default to the EDD group if not specified.
		$args['group'] = ! empty( $args['group'] ) ? $args['group'] : self::GROUP;

		return as_get_scheduled_actions( $args, $format );
	}
}
