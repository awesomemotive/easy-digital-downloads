<?php
/**
 * Log Functions.
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

use EDD\Logs\Log;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a log.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of log data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $object_id     Object ID that the log refers to. This would
 *                                 be an ID that corresponds to the object type
 *                                 specified. E.g. an object ID of 25 with object
 *                                 type of `order` refers to order 25 in the
 *                                 `edd_orders` table. Default empty.
 *     @type string $object_type   Object type that the log refers to.
 *                                 E.g. `discount` or `order`. Default empty.
 *     @type int    $user_id       ID of the current WordPress user logged in.
 *                                 Default 0.
 *     @type string $type          Log type. Default empty.
 *     @type string $title         Log title. Default empty.
 *     @type string $content       Log content. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the log was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_log( $data = array() ) {

	// An object type must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['object_type'] ) ) {
		// Verify that we do have an object_type before checking it further.
		if ( array_key_exists( 'object_type', $data ) && is_null( $data['object_type'] ) ) {
			// If the object_id is not empty but the object_type is null, fail this log attempt.
			if ( ! empty( $data['object_id'] ) ) {
				return false;
			}
			// The object_type is not null, and it is empty. No log will take place.
		} else {
			return false;
		}
	}

	$object_type_for_filter = ! empty( $data['object_type'] ) ? $data['object_type'] : 'generic';
	/**
	 * Allow the ability to disable a generic log entry by object_type.
	 *
	 * Based on the object type, allows developers to disable the insertion of a log.
	 *
	 * Example:
	 * add_filter( 'edd_should_log_gateway_error', '__return_false' )
	 *
	 * You can find a list of the logs object types in the edd_logs table.
	 *
	 * @since 3.1
	 *
	 * @param bool  $should_record_log If this log should be inserted
	 * @param array $data              The data to be logged.
	 */
	$should_record_log = apply_filters( "edd_should_log_{$object_type_for_filter}", true, $data );

	if ( false === $should_record_log ) {
		return false;
	}

	// Instantiate a query object.
	$logs = new EDD\Database\Queries\Log();

	return $logs->add_item( $data );
}

/**
 * Delete a log.
 *
 * @since 3.0
 *
 * @param int $log_id Log ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
 */
function edd_delete_log( $log_id = 0 ) {
	$logs = new EDD\Database\Queries\Log();

	return $logs->delete_item( $log_id );
}

/**
 * Update a log.
 *
 * @since 3.0
 *
 * @param int   $log_id Log ID.
 * @param array $data {
 *     Array of log data. Default empty.
 *
 *     @type int    $object_id     Object ID that the log refers to. This would
 *                                 be an ID that corresponds to the object type
 *                                 specified. E.g. an object ID of 25 with object
 *                                 type of `order` refers to order 25 in the
 *                                 `edd_orders` table. Default empty.
 *     @type string $object_type   Object type that the log refers to.
 *                                 E.g. `discount` or `order`. Default empty.
 *     @type int    $user_id       ID of the current WordPress user logged in.
 *                                 Default 0.
 *     @type string $type          Log type. Default empty.
 *     @type string $title         Log title. Default empty.
 *     @type string $content       Log content. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the log was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_log( $log_id = 0, $data = array() ) {
	$logs = new EDD\Database\Queries\Log();

	return $logs->update_item( $log_id, $data );
}

/**
 * Get a log by ID.
 *
 * @since 3.0
 *
 * @param int $log_id Log ID.
 * @return Log|false Log object if successful, false otherwise.
 */
function edd_get_log( $log_id = 0 ) {
	$logs = new EDD\Database\Queries\Log();

	// Return log
	return $logs->get_item( $log_id );
}

/**
 * Get a log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return Log|false Log object if successful, false otherwise.
 */
function edd_get_log_by( $field = '', $value = '' ) {
	$logs = new EDD\Database\Queries\Log();

	// Return log
	return $logs->get_item_by( $field, $value );
}

/**
 * Query for logs.
 *
 * @see \EDD\Database\Queries\Log::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log` for
 *                    accepted arguments.
 * @return Log[] Array of `Log` objects.
 */
function edd_get_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$logs = new EDD\Database\Queries\Log();

	// Return logs
	return $logs->query( $r );
}

/**
 * Count logs.
 *
 * @see \EDD\Database\Queries\Log::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log` for
 *                    accepted arguments.
 * @return int Number of logs returned based on query arguments passed.
 */
function edd_count_logs( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s).
	$logs = new EDD\Database\Queries\Log( $r );

	// Return the number of logs found in the query.
	return absint( $logs->found_items );
}
