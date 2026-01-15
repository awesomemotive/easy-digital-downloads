<?php
/**
 * Log Pruning Cron Component
 *
 * Handles automated pruning of old logs on a daily schedule.
 *
 * @package     EDD\Cron\Components
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Tools\Logs\LogStorageCalculator;

/**
 * LogPruning Component Class
 *
 * @since 3.6.4
 */
class LogPruning extends Component {

	/**
	 * The unique identifier for this component.
	 *
	 * @since 3.6.4
	 * @var string
	 */
	protected static $id = 'log_pruning';

	/**
	 * Gets the array of subscribed events.
	 *
	 * Registers an init hook to dynamically add hooks for enabled log types.
	 * This avoids loading translations too early (before text domains are loaded).
	 *
	 * @since 3.6.4
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'init' => 'register_pruning_hooks',
		);
	}

	/**
	 * Register pruning hooks for enabled log types.
	 *
	 * Called on `init` to ensure text domains are loaded and settings are available.
	 * Only registers hooks for log types that have pruning enabled.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function register_pruning_hooks() {
		// Check if pruning is globally enabled.
		if ( ! edd_get_option( 'log_pruning_enabled', false ) ) {
			return;
		}

		$settings = edd_get_option( 'edd_log_pruning_settings', array() );

		$log_types = \EDD\Logs\Registry::get_types();

		// Register hooks for registered log types that are enabled.
		foreach ( $log_types as $type_id => $type_config ) {
			// Skip if not prunable.
			if ( empty( $type_config['prunable'] ) ) {
				continue;
			}

			// Only register if enabled for this specific type.
			if ( ! empty( $settings['log_types'][ $type_id ]['enabled'] ) ) {
				add_action( "edd_prune_logs_{$type_id}", array( $this, 'prune_single_log_type' ) );
			}
		}

		// Also check for unregistered types that might be enabled.
		if ( ! empty( $settings['log_types'] ) ) {
			foreach ( $settings['log_types'] as $type_id => $type_settings ) {
				// Skip registered types (already handled above).
				if ( isset( $log_types[ $type_id ] ) ) {
					continue;
				}

				// Only register if enabled.
				if ( ! empty( $type_settings['enabled'] ) ) {
					add_action( "edd_prune_logs_{$type_id}", array( $this, 'prune_single_log_type' ) );
				}
			}
		}
	}

	/**
	 * Prune logs for a single log type.
	 *
	 * Called by the individual cron event for this log type.
	 * Automatically detects which log type to prune based on the hook name.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function prune_single_log_type() {
		// Get the current hook to determine which log type to prune.
		$current_hook = current_filter();
		$type_id      = str_replace( 'edd_prune_logs_', '', $current_hook );

		// Check if pruning is globally enabled.
		if ( ! edd_get_option( 'log_pruning_enabled', false ) ) {
			return;
		}

		$settings = edd_get_option( 'edd_log_pruning_settings', array() );

		// Check if enabled for this specific type.
		if ( empty( $settings['log_types'][ $type_id ]['enabled'] ) ) {
			return;
		}

		$log_types = \EDD\Logs\Registry::get_types();

		// Check if this is a registered type.
		if ( isset( $log_types[ $type_id ] ) ) {
			$type_config = $log_types[ $type_id ];

			// Double-check it's prunable.
			if ( empty( $type_config['prunable'] ) ) {
				return;
			}
		} else {
			// This is an unregistered type - build config for it.
			$type_config = \EDD\Logs\Registry::get_unregistered_type_config( $type_id );

			if ( empty( $type_config ) ) {
				return;
			}
		}

		$days = isset( $settings['log_types'][ $type_id ]['days'] ) ? absint( $settings['log_types'][ $type_id ]['days'] ) : 90;

		if ( $days <= 0 ) {
			return;
		}

		// Get batch size setting.
		$batch_size = isset( $settings['batch_size'] ) ? absint( $settings['batch_size'] ) : 250;

		// Perform pruning with configured batch size.
		self::prune_log_type( $type_id, $type_config, $days, $batch_size );
	}

	/**
	 * Builds the date_created_query array for pruning queries.
	 *
	 * @since 3.6.4
	 *
	 * @param \DateTime $cutoff_date The cutoff date for pruning.
	 * @return array The date query configuration.
	 */
	private static function build_date_query( \DateTime $cutoff_date ): array {
		return array(
			'column' => 'date_created',
			array(
				'column'    => 'date_created',
				'before'    => $cutoff_date->format( 'Y-m-d H:i:s' ),
				'inclusive' => false,
			),
		);
	}

	/**
	 * Prune logs of a specific type.
	 *
	 * This method is public and static so it can be called from both the cron
	 * and the AJAX handler for manual pruning.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id     Log type ID.
	 * @param array  $type_config Log type configuration.
	 * @param int    $days        Number of days to keep logs.
	 * @param int    $batch_size  Number of logs to delete per batch. Default 250.
	 * @return int Number of logs deleted.
	 */
	public static function prune_log_type( $type_id, $type_config, $days, $batch_size = 250 ): int {
		// Validate inputs.
		if ( empty( $type_id ) || empty( $type_config ) || $days <= 0 ) {
			return 0;
		}

		// Check if this log type is prunable.
		if ( empty( $type_config['prunable'] ) ) {
			return 0;
		}

		// Get query class and normalize namespace separators.
		$query_class = str_replace( '/', '\\', $type_config['query_class'] );

		if ( ! class_exists( $query_class ) ) {
			edd_debug_log( sprintf( 'Log Pruning: Query class %s not found for log type %s', $query_class, $type_id ), true );
			return 0;
		}

		// Calculate cutoff date.
		try {
			$cutoff_date = new \DateTime( "-{$days} days", new \DateTimeZone( 'UTC' ) );
		} catch ( \Exception $e ) {
			edd_debug_log( sprintf( 'Log Pruning: Error calculating cutoff date: %s', $e->getMessage() ), true );
			return 0;
		}

		// Ensure batch_size is within valid range.
		$batch_size = max( 50, min( 1000, absint( $batch_size ) ) );

		// Build query arguments.
		$query_args = array(
			'number'             => $batch_size,  // Process in batches to avoid timeouts.
			'date_created_query' => self::build_date_query( $cutoff_date ),
			'fields'             => 'ids',  // Memory optimization.
		);

		// Add type-specific query arguments (e.g., type => 'gateway_error' for edd_logs).
		if ( ! empty( $type_config['query_args'] ) && is_array( $type_config['query_args'] ) ) {
			$query_args = array_merge( $query_args, $type_config['query_args'] );
		}

		// Get logs to delete.
		$query   = new $query_class();
		$log_ids = $query->query( $query_args );

		if ( empty( $log_ids ) || ! is_array( $log_ids ) ) {
			return 0;
		}

		$deleted_count = 0;

		// Delete logs one by one to allow proper cleanup hooks to fire.
		foreach ( $log_ids as $log_id ) {
			$result = $query->delete_item( $log_id );

			if ( $result ) {
				$deleted_count++;
			}
		}

		// Log the action.
		if ( $deleted_count > 0 ) {
			edd_debug_log(
				sprintf(
					'Log Pruning: Deleted %d %s logs older than %d days.',
					$deleted_count,
					$type_id,
					$days
				),
				true
			);

			// Invalidate the storage cache for this log type.
			LogStorageCalculator::invalidate_cache( $type_id );
		}

		return $deleted_count;
	}

	/**
	 * Get count of logs that would be pruned for a specific type.
	 *
	 * This method is public and static so it can be called from the AJAX handler.
	 *
	 * @since 3.6.4
	 *
	 * @param array $type_config Log type configuration.
	 * @param int   $days        Number of days to keep logs.
	 * @return int Number of logs that would be deleted.
	 */
	public static function get_prune_count( $type_config, $days ): int {
		// Validate inputs.
		if ( empty( $type_config ) || $days <= 0 ) {
			return 0;
		}

		// Check if this log type is prunable.
		if ( empty( $type_config['prunable'] ) ) {
			return 0;
		}

		// Get query class and normalize namespace separators.
		$query_class = str_replace( '/', '\\', $type_config['query_class'] );

		if ( ! class_exists( $query_class ) ) {
			return 0;
		}

		// Calculate cutoff date.
		try {
			$cutoff_date = new \DateTime( "-{$days} days", new \DateTimeZone( 'UTC' ) );
		} catch ( \Exception $e ) {
			return 0;
		}

		// Build query arguments.
		$query_args = array(
			'count'              => true,
			'date_created_query' => self::build_date_query( $cutoff_date ),
		);

		// Add type-specific query arguments (e.g., type => 'gateway_error' for edd_logs).
		if ( ! empty( $type_config['query_args'] ) && is_array( $type_config['query_args'] ) ) {
			$query_args = array_merge( $query_args, $type_config['query_args'] );
		}

		// Get count.
		try {
			$query = new $query_class();
			$query->query( $query_args );

			return absint( $query->found_items );
		} catch ( \Exception $e ) {
			return 0;
		}
	}
}
