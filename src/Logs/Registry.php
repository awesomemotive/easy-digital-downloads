<?php
/**
 * Log Type Registry
 *
 * Centralized registry for all log types that can be pruned.
 *
 * @package     EDD\Logs
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Logs;

/**
 * Log Type Registry class.
 *
 * @since 3.6.4
 */
final class Registry {

	/**
	 * Cached log types array.
	 *
	 * @since 3.6.4
	 * @var array|null
	 */
	private static $cached_types = null;

	/**
	 * Reset the cached log types.
	 *
	 * This is primarily useful for testing, when filters need to be
	 * applied after the cache has already been populated.
	 *
	 * @since 3.6.4
	 *
	 * @return void
	 */
	public static function reset_cache() {
		self::$cached_types = null;
	}

	/**
	 * Get all registered log types with their metadata.
	 *
	 * @since 3.6.4
	 *
	 * @return array Array of log types with configuration.
	 */
	public static function get_types(): array {
		// Return cached types if available.
		if ( null !== self::$cached_types ) {
			return self::$cached_types;
		}

		// Determine if file downloads should show a warning based on active extensions.
		$file_downloads_has_warning = self::should_warn_file_downloads();

		// Build file downloads description based on active extensions.
		$file_downloads_description = __( 'Logs of file downloads by customers.', 'easy-digital-downloads' );
		if ( class_exists( 'EDD_Software_Licensing' ) ) {
			$file_downloads_description = __( 'Logs of file downloads by customers and package downloads by customer sites.', 'easy-digital-downloads' );
		}

		$log_types = array(
			'file_downloads' => array(
				'label'            => __( 'File Downloads', 'easy-digital-downloads' ),
				'table'            => 'edd_logs_file_downloads',
				'meta_table'       => 'edd_logs_file_downloadmeta',
				'meta_foreign_key' => 'edd_logs_file_download_id',
				'query_class'      => 'EDD\\Database\\Queries\\Log_File_Download',
				'prunable'         => true,
				'has_warning'      => $file_downloads_has_warning,
				'default_days'     => 90,
				'description'      => $file_downloads_description,
			),
			'gateway_errors' => array(
				'label'            => __( 'Payment Errors', 'easy-digital-downloads' ),
				'table'            => 'edd_logs',
				'meta_table'       => 'edd_logmeta',
				'meta_foreign_key' => 'edd_log_id',
				'query_class'      => 'EDD\\Database\\Queries\\Log',
				'query_args'       => array( 'type' => 'gateway_error' ),
				'prunable'         => true,
				'default_days'     => 30,
				'description'      => __( 'Gateway and payment error logs.', 'easy-digital-downloads' ),
			),
			'api_requests' => array(
				'label'            => __( 'API Requests', 'easy-digital-downloads' ),
				'table'            => 'edd_logs_api_requests',
				'meta_table'       => 'edd_logs_api_requestmeta',
				'meta_foreign_key' => 'edd_logs_api_request_id',
				'query_class'      => 'EDD\\Database\\Queries\\Log_Api_Request',
				'prunable'         => true,
				'default_days'     => 60,
				'description'      => __( 'REST API request logs.', 'easy-digital-downloads' ),
			),
			'emails' => array(
				'label'            => __( 'Email Logs', 'easy-digital-downloads' ),
				'table'            => 'edd_logs_emails',
				'meta_table'       => 'edd_logs_emailmeta',
				'meta_foreign_key' => 'edd_logs_email_id',
				'query_class'      => 'EDD\\Database\\Queries\\LogEmail',
				'prunable'         => true,
				'default_days'     => 30,
				'description'      => __( 'Email sending logs.', 'easy-digital-downloads' ),
			),
		);

		// Backwards compatibility: incorporate legacy log types from edd_log_views filter.
		$log_types = self::merge_legacy_log_views( $log_types );

		// Apply filters to each log type's prunable status.
		foreach ( $log_types as $type_id => $type_config ) {
			/**
			 * Filter whether a specific log type can be pruned.
			 *
			 * Allows extensions to conditionally prevent pruning based on their settings.
			 *
			 * @since 3.6.4
			 *
			 * @param bool   $prunable    Whether the log type can be pruned.
			 * @param string $type_id     The log type ID.
			 * @param array  $type_config The log type configuration.
			 */
			$log_types[ $type_id ]['prunable'] = apply_filters(
				'edd_log_type_prunable',
				$type_config['prunable'],
				$type_id,
				$type_config
			);
		}

		/**
		 * Filter the registered log types.
		 *
		 * Allows extensions to add, remove, or modify log types and their pruning settings.
		 *
		 * @since 3.6.4
		 *
		 * @param array $log_types Array of log type configurations.
		 */
		self::$cached_types = apply_filters( 'edd_registered_log_types', $log_types );

		return self::$cached_types;
	}

	/**
	 * Check if the file download limit setting is enabled.
	 *
	 * @since 3.6.4
	 *
	 * @return bool True if file download limit is enabled, false otherwise.
	 */
	private static function is_file_download_limit_enabled(): bool {
		$file_download_limit = edd_get_option( 'file_download_limit', 0 );

		return ! empty( $file_download_limit ) && $file_download_limit > 0;
	}

	/**
	 * Get active extensions that may depend on file download logs.
	 *
	 * Returns untranslated extension identifiers to avoid loading
	 * text domains too early. Use get_extension_labels() to get
	 * translated names for display purposes.
	 *
	 * @since 3.6.4
	 *
	 * @return array Array of active extension identifiers.
	 */
	private static function get_active_file_download_extensions(): array {
		$extensions = array();

		if ( function_exists( 'edd_recurring' ) ) {
			$extensions[] = 'recurring';
		}

		if ( class_exists( 'EDD_All_Access' ) ) {
			$extensions[] = 'all_access';
		}

		if ( class_exists( 'EDD_Commission' ) ) {
			$extensions[] = 'commissions';
		}

		return $extensions;
	}

	/**
	 * Get translated labels for extension identifiers.
	 *
	 * @since 3.6.4
	 *
	 * @return array Mapping of extension identifiers to translated names.
	 */
	private static function get_extension_labels(): array {
		return array(
			'recurring'   => __( 'Recurring Payments', 'easy-digital-downloads' ),
			'all_access'  => __( 'All Access', 'easy-digital-downloads' ),
			'commissions' => __( 'Commissions', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Check if file download logs should show a pruning warning.
	 *
	 * Automatically detects if certain extensions are active that may require
	 * file download logs depending on their settings.
	 *
	 * @since 3.6.4
	 *
	 * @return bool True if a warning should be shown, false otherwise.
	 */
	public static function should_warn_file_downloads(): bool {
		$show_warning = self::is_file_download_limit_enabled() || ! empty( self::get_active_file_download_extensions() );

		/**
		 * Filter whether file download logs should show a pruning warning.
		 *
		 * @since 3.6.4
		 *
		 * @param bool $show_warning Whether a warning should be shown.
		 */
		return apply_filters( 'edd_should_warn_file_downloads', $show_warning );
	}

	/**
	 * Get the pruning warning message for a log type.
	 *
	 * @since 3.6.4
	 *
	 * @param string $log_type_id Log type ID.
	 * @return string Warning message or empty string if no warning.
	 */
	public static function get_pruning_warning( $log_type_id ): string {
		$warning = '';

		if ( 'file_downloads' === $log_type_id && self::should_warn_file_downloads() ) {
			$warnings = array();

			// Check if File Download Limit is set.
			if ( self::is_file_download_limit_enabled() ) {
				$warnings[] = __( 'File Download Limit is enabled - pruning logs may reset customer download counts.', 'easy-digital-downloads' );
			}

			// Check for extensions.
			$extension_ids = self::get_active_file_download_extensions();

			if ( ! empty( $extension_ids ) ) {
				// Translate extension identifiers to display names.
				$labels          = self::get_extension_labels();
				$extension_names = array_map(
					function ( $id ) use ( $labels ) {
						return isset( $labels[ $id ] ) ? $labels[ $id ] : $id;
					},
					$extension_ids
				);

				// Format the list with proper grammar (Item1, Item2, or Item3).
				$formatted_list = self::format_list_with_or( $extension_names );

				$warnings[] = sprintf(
					/* translators: %s: list of extension names (e.g., "Recurring Payments or All Access") */
					__( 'Pruning may impact how %s tracks or limits downloads.', 'easy-digital-downloads' ),
					$formatted_list
				);
			}

			if ( ! empty( $warnings ) ) {
				$warning = implode( ' ', $warnings );
			}
		}

		/**
		 * Filter the pruning warning message for a log type.
		 *
		 * @since 3.6.4
		 *
		 * @param string $warning      The warning message.
		 * @param string $log_type_id  The log type ID.
		 */
		return (string) apply_filters( 'edd_get_log_type_pruning_warning', $warning, $log_type_id );
	}

	/**
	 * Builds configuration for an unregistered log type.
	 *
	 * Unregistered log types are those found in the database but not formally
	 * registered via the edd_registered_log_types filter.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id The type ID (with or without 'unregistered_' prefix).
	 * @param array  $extra   Optional extra fields to merge (e.g., 'description', 'count').
	 * @return array|null Config array or null if empty type value.
	 */
	public static function get_unregistered_type_config( string $type_id, array $extra = array() ): ?array {
		// Handle both prefixed and unprefixed type IDs.
		if ( strpos( $type_id, 'unregistered_' ) === 0 ) {
			$type_value = sanitize_key( str_replace( 'unregistered_', '', $type_id ) );
		} else {
			$type_value = sanitize_key( $type_id );
		}

		if ( empty( $type_value ) ) {
			return null;
		}

		$config = array(
			'label'            => ucwords( str_replace( array( '_', '-' ), ' ', $type_value ) ),
			'table'            => 'edd_logs',
			'meta_table'       => 'edd_logmeta',
			'meta_foreign_key' => 'edd_log_id',
			'query_class'      => 'EDD\\Database\\Queries\\Log',
			'query_args'       => array( 'type' => $type_value ),
			'prunable'         => true,
			'default_days'     => 90,
		);

		return array_merge( $config, $extra );
	}

	/**
	 * Gets additional (unregistered) log types from the database.
	 *
	 * Queries the edd_logs table for distinct type values that are not
	 * part of the registered log types.
	 *
	 * @since 3.6.4
	 *
	 * @param bool $include_counts Whether to include record counts (slower query).
	 * @param bool $use_cache      Whether to use transient caching.
	 * @return array Array of unregistered log type configurations.
	 */
	public static function get_additional_log_types( bool $include_counts = false, bool $use_cache = true ): array {
		$cache_key = 'edd_additional_log_types' . ( $include_counts ? '_counts' : '' );

		if ( $use_cache ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;

		$registered_types = self::get_types();

		// Build list of registered type values to exclude.
		$exclude_types = array();
		foreach ( $registered_types as $type_config ) {
			if ( isset( $type_config['table'] ) && 'edd_logs' === $type_config['table'] && isset( $type_config['query_args']['type'] ) ) {
				$exclude_types[] = $type_config['query_args']['type'];
			}
		}

		// Build query.
		if ( $include_counts ) {
			$select = "SELECT type, COUNT(*) as count FROM {$wpdb->prefix}edd_logs";
			$suffix = 'GROUP BY type ORDER BY count DESC LIMIT 100';
		} else {
			$select = "SELECT DISTINCT type FROM {$wpdb->prefix}edd_logs";
			$suffix = 'LIMIT 100';
		}

		if ( empty( $exclude_types ) ) {
			$query = "{$select} {$suffix}";
		} else {
			$placeholders = implode( ', ', array_fill( 0, count( $exclude_types ), '%s' ) );
			$query        = $wpdb->prepare(
				"{$select} WHERE type NOT IN ({$placeholders}) {$suffix}",
				$exclude_types
			);
		}

		$results = $include_counts ? $wpdb->get_results( $query, ARRAY_A ) : $wpdb->get_col( $query );

		if ( empty( $results ) ) {
			if ( $use_cache ) {
				set_transient( $cache_key, array(), HOUR_IN_SECONDS );
			}
			return array();
		}

		// Build config array for each additional type.
		$additional_types = array();
		foreach ( $results as $row ) {
			$type_value = $include_counts ? $row['type'] : $row;
			$count      = $include_counts ? absint( $row['count'] ) : 0;

			if ( empty( $type_value ) ) {
				continue;
			}

			$type_id = 'unregistered_' . sanitize_key( $type_value );
			$extra   = array();

			if ( $include_counts ) {
				$extra['description'] = sprintf(
					/* translators: %s: log type value */
					__( 'Unregistered log type: %s', 'easy-digital-downloads' ),
					$type_value
				);
				$extra['count'] = $count;
			}

			$additional_types[ $type_id ] = self::get_unregistered_type_config( $type_value, $extra );
		}

		if ( $use_cache ) {
			set_transient( $cache_key, $additional_types, HOUR_IN_SECONDS );
		}

		return $additional_types;
	}

	/**
	 * Merge legacy log types from the edd_log_views filter.
	 *
	 * Provides backwards compatibility for extensions that register log types
	 * via the edd_log_views filter before updating to use edd_registered_log_types.
	 *
	 * @since 3.6.4
	 *
	 * @param array $log_types Current registered log types.
	 * @return array Merged log types array.
	 */
	private static function merge_legacy_log_views( array $log_types ): array {
		// Get legacy log views. We apply the filter to EDD's base views to get extension additions.
		$base_views = array(
			'file_downloads' => __( 'File Downloads', 'easy-digital-downloads' ),
			'gateway_errors' => __( 'Payment Errors', 'easy-digital-downloads' ),
			'api_requests'   => __( 'API Requests', 'easy-digital-downloads' ),
		);

		/**
		 * Filter the default logs views.
		 *
		 * @since 1.4
		 * @since 3.0 Removed sales log.
		 *
		 * @param array $views Logs views.
		 */
		$legacy_views = apply_filters( 'edd_log_views', $base_views );

		// Find any legacy types that aren't already registered.
		foreach ( $legacy_views as $type_id => $label ) {
			// Skip if already registered.
			if ( isset( $log_types[ $type_id ] ) ) {
				continue;
			}

			// Skip legacy types with custom view actions - they use custom storage
			// and can't be queried/pruned via our standard system.
			if ( has_action( "edd_logs_view_{$type_id}" ) ) {
				continue;
			}

			// Create a basic registry entry for this legacy type.
			// Legacy types use the edd_logs table with a type column filter.
			$log_types[ $type_id ] = array(
				'label'            => $label,
				'table'            => 'edd_logs',
				'meta_table'       => 'edd_logmeta',
				'meta_foreign_key' => 'edd_log_id',
				'query_class'      => 'EDD\\Database\\Queries\\Log',
				'query_args'       => array( 'type' => $type_id ),
				'prunable'         => true,
				'has_warning'      => false,
				'default_days'     => 90,
				'description'      => sprintf(
					/* translators: %s: log type label */
					__( '%s logs (registered via legacy filter).', 'easy-digital-downloads' ),
					$label
				),
				'legacy'           => true,
			);
		}

		return $log_types;
	}

	/**
	 * Format a list of items with proper grammar using "or".
	 *
	 * - 1 item: "Item1"
	 * - 2 items: "Item1 or Item2"
	 * - 3+ items: "Item1, Item2, or Item3"
	 *
	 * @since 3.6.4
	 *
	 * @param array $items Array of items to format.
	 * @return string Formatted list string.
	 */
	private static function format_list_with_or( array $items ): string {
		$count = count( $items );

		if ( 0 === $count ) {
			return '';
		}

		if ( 1 === $count ) {
			return $items[0];
		}

		if ( 2 === $count ) {
			return sprintf(
				/* translators: 1: first item, 2: second item */
				__( '%1$s or %2$s', 'easy-digital-downloads' ),
				$items[0],
				$items[1]
			);
		}

		// 3 or more items: "Item1, Item2, or Item3".
		$last_item = array_pop( $items );

		return sprintf(
			/* translators: 1: comma-separated list of items, 2: last item */
			__( '%1$s, or %2$s', 'easy-digital-downloads' ),
			implode( ', ', $items ),
			$last_item
		);
	}
}
