<?php
/**
 * API Request Log Functions.
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add an api request log.
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
function edd_add_api_request_log( $data = array() ) {

	// An object type must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['object_type'] ) ) {
		// If the object_type is set to null, check the value of the object_id.
		if ( is_null( $data['object_type'] ) ) {
			// If the object_id is not empty but the object_type is, fail this log attempt.
			if ( ! empty( $data['object_id'] ) ) {
				return false;
			}
			// The object_type is not null, and it is empty. No log will take place.
		} else {
			return false;
		}
	}

	// Instantiate a query object.
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->add_item( $data );
}

/**
 * Delete an api request log.
 *
 * @since 3.0
 *
 * @param int $api_request_log_id Log ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
 */
function edd_delete_api_request_log( $api_request_log_id = 0 ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->delete_item( $api_request_log_id );
}

/**
 * Update am api request log.
 *
 * @since 3.0
 *
 * @param int   $api_request_log_id Log ID.
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
function edd_update_api_request_log( $api_request_log_id = 0, $data = array() ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->update_item( $api_request_log_id, $data );
}

/**
 * Get an api request log by ID.
 *
 * @since 3.0
 *
 * @param int $api_request_log_id Log ID.
 * @return EDD\Api_Request_Logs\Api_Request_Log|false Log object if successful, false otherwise.
 */
function edd_get_api_request_log( $api_request_log_id = 0 ) {
	return edd_get_api_request_log_by( 'id', $api_request_log_id );
}

/**
 * Get a api request log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\Api_Request_Logs\Api_Request_Log|false Log object if successful, false otherwise.
 */
function edd_get_api_request_log_by( $field = '', $value = '' ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return log.
	return $api_request_logs->get_item_by( $field, $value );
}

/**
 * Query for api request logs.
 *
 * @see \EDD\Database\Queries\Log_Api_Request::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_Api_Request` for
 *                    accepted arguments.
 * @return \EDD\Api_Request_Logs\Api_Request_Log[] Array of `Log` objects.
 */
function edd_get_api_request_logs( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object.
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return api request logs.
	return $api_request_logs->query( $r );
}

/**
 * Count api request logs.
 *
 * @see \EDD\Database\Queries\Log_Api_Request::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_Api_Request` for
 *                    accepted arguments.
 * @return int Number of logs returned based on query arguments passed.
 */
function edd_count_api_request_logs( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s).
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request( $r );

	// Return the number of api request logs that were found.
	return absint( $api_request_logs->found_items );
}
