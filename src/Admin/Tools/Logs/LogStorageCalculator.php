<?php
/**
 * Log Storage Calculator.
 *
 * Calculates and caches database storage usage for log types.
 *
 * @package     EDD\Admin\Tools\Logs
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Admin\Tools\Logs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Log Storage Calculator class.
 *
 * @since 3.6.4
 */
class LogStorageCalculator {

	/**
	 * Cache TTL in seconds.
	 *
	 * @since 3.6.4
	 */
	private const CACHE_TTL = HOUR_IN_SECONDS;

	/**
	 * Cache key prefix.
	 *
	 * @since 3.6.4
	 */
	private const CACHE_PREFIX = 'edd_log_storage_';

	/**
	 * Get the storage size in bytes for a log type.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id     The log type identifier.
	 * @param array  $type_config The log type configuration.
	 * @return int Storage size in bytes.
	 */
	public static function get_storage( string $type_id, array $type_config ): int {
		$cache_key = self::CACHE_PREFIX . $type_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return absint( $cached );
		}

		$storage = self::calculate_storage( $type_config );

		set_transient( $cache_key, $storage, self::CACHE_TTL );

		return $storage;
	}

	/**
	 * Get the formatted storage size for display.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id     The log type identifier.
	 * @param array  $type_config The log type configuration.
	 * @return string Human-readable storage size (e.g., "1.5 MB").
	 */
	public static function get_formatted_storage( string $type_id, array $type_config ): string {
		$bytes = self::get_storage( $type_id, $type_config );

		return size_format( $bytes );
	}

	/**
	 * Invalidate the cache for a specific log type or all log types.
	 *
	 * @since 3.6.4
	 *
	 * @param string $type_id Optional. The log type identifier. If empty, clears all caches.
	 */
	public static function invalidate_cache( string $type_id = '' ): void {
		if ( ! empty( $type_id ) ) {
			delete_transient( self::CACHE_PREFIX . $type_id );
			return;
		}

		global $wpdb;

		// Clear all log storage transients.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%',
				'_transient_timeout_' . self::CACHE_PREFIX . '%'
			)
		);
	}

	/**
	 * Calculate the storage size for a log type.
	 *
	 * Uses row-based estimation for maximum compatibility across all hosting environments.
	 * Includes both main table and meta table storage if configured.
	 *
	 * @since 3.6.4
	 *
	 * @param array $type_config The log type configuration.
	 * @return int Estimated storage size in bytes.
	 */
	private static function calculate_storage( array $type_config ): int {
		if ( empty( $type_config['table'] ) ) {
			return 0;
		}

		global $wpdb;

		$table      = $type_config['table'];
		$table_name = $wpdb->prefix . $table;
		$columns    = self::get_table_columns( $table );

		if ( empty( $columns ) ) {
			return 0;
		}

		// Build the SUM expression for all columns.
		$length_expressions = array_map(
			function ( $column ) {
				return "COALESCE(LENGTH({$column}), 0)";
			},
			$columns
		);

		// Add 8 bytes overhead for the bigint ID.
		$sum_expression = '8 + ' . implode( ' + ', $length_expressions );

		// For the shared edd_logs table, filter by type.
		if ( 'edd_logs' === $table && ! empty( $type_config['query_args']['type'] ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM({$sum_expression}) FROM {$table_name} WHERE type = %s",
					$type_config['query_args']['type']
				)
			);
		} else {
			// For dedicated tables, calculate the entire table.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->get_var( "SELECT SUM({$sum_expression}) FROM {$table_name}" );
		}

		$main_storage = absint( $result );
		$meta_storage = self::calculate_meta_storage( $type_config );

		return $main_storage + $meta_storage;
	}

	/**
	 * Calculate the meta table storage size for a log type.
	 *
	 * @since 3.6.4
	 *
	 * @param array $type_config The log type configuration.
	 * @return int Estimated meta storage size in bytes.
	 */
	private static function calculate_meta_storage( array $type_config ): int {
		// Return 0 if meta table is not configured.
		if ( empty( $type_config['meta_table'] ) || empty( $type_config['meta_foreign_key'] ) ) {
			return 0;
		}

		global $wpdb;

		$main_table  = $wpdb->prefix . $type_config['table'];
		$meta_table  = $wpdb->prefix . $type_config['meta_table'];
		$foreign_key = $type_config['meta_foreign_key'];

		// Verify the meta table exists before querying.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $meta_table ) );
		if ( ! $table_exists ) {
			return 0;
		}

		// Meta columns: meta_id (8 bytes) + foreign_key (8 bytes) + meta_key + meta_value.
		$sum_expression = '16 + COALESCE(LENGTH(meta_key), 0) + COALESCE(LENGTH(meta_value), 0)';

		// For the shared edd_logs table, join to filter by type.
		if ( 'edd_logs' === $type_config['table'] && ! empty( $type_config['query_args']['type'] ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM({$sum_expression})
					FROM {$meta_table} m
					INNER JOIN {$main_table} l ON m.{$foreign_key} = l.id
					WHERE l.type = %s",
					$type_config['query_args']['type']
				)
			);
		} else {
			// For dedicated tables, calculate the entire meta table.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->get_var( "SELECT SUM({$sum_expression}) FROM {$meta_table}" );
		}

		// Handle null or error results gracefully.
		if ( null === $result || false === $result ) {
			return 0;
		}

		return absint( $result );
	}

	/**
	 * Get the columns for a specific log table.
	 *
	 * @since 3.6.4
	 *
	 * @param string $table The table name without prefix.
	 * @return array Array of column names.
	 */
	private static function get_table_columns( string $table ): array {
		$columns = array(
			'edd_logs'                => array(
				'object_id',
				'object_type',
				'user_id',
				'type',
				'title',
				'content',
				'date_created',
				'date_modified',
				'uuid',
			),
			'edd_logs_file_downloads' => array(
				'product_id',
				'file_id',
				'order_id',
				'price_id',
				'customer_id',
				'ip',
				'user_agent',
				'date_created',
				'date_modified',
				'uuid',
			),
			'edd_logs_api_requests'   => array(
				'user_id',
				'api_key',
				'token',
				'version',
				'request',
				'error',
				'ip',
				'time',
				'date_created',
				'date_modified',
				'uuid',
			),
			'edd_logs_emails'         => array(
				'object_id',
				'object_type',
				'email',
				'email_id',
				'subject',
				'date_created',
				'date_modified',
				'uuid',
			),
		);

		return $columns[ $table ] ?? array();
	}
}
