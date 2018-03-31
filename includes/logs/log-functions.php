<?php
/**
 * Log Functions
 *
 * @package     EDD
 * @subpackage  Notes
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a log.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_log( $data = array() ) {
	// An object ID and object type must be supplied for every log that is inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	$logs = new EDD_Log_Query();

	return $logs->add_item( $data );
}

/**
 * Delete a log.
 *
 * @since 3.0.0
 *
 * @param int $log_id Log ID.
 * @return int
 */
function edd_delete_log( $log_id = 0 ) {
	$logs = new EDD_Log_Query();

	return $logs->delete_item( $log_id );
}

/**
 * Update a log.
 *
 * @since 3.0.0
 *
 * @param int   $log_id Log ID.
 * @param array $data   Updated log data.
 * @return bool Whether or not the log was updated.
 */
function edd_update_log( $log_id = 0, $data = array() ) {
	$logs = new EDD_Log_Query();

	return $logs->update_item( $log_id, $data );
}

/**
 * Add an API request log.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_api_request_log( $data = array() ) {
	// A request is required for every API request log that is inserted into the database.
	if ( empty( $data['request'] ) ) {
		return false;
	}

	$logs = new EDD_Log_API_Request_Query();

	return $logs->add_item( $data );
}

/**
 * Delete an API request log.
 *
 * @since 3.0.0
 *
 * @param int $log_id API request log ID.
 * @return int
 */
function edd_delete_api_request_log( $log_id = 0 ) {
	$logs = new EDD_Log_API_Request_Query();

	return $logs->delete_item( $log_id );
}

/**
 * Update an API request log.
 *
 * @since 3.0.0
 *
 * @param int   $log_id API request log ID.
 * @param array $data   Updated API request log data.
 * @return bool Whether or not the API request log was updated.
 */
function edd_update_api_request_log( $log_id = 0, $data = array() ) {
	$logs = new EDD_Log_API_Request_Query();

	return $logs->update_item( $log_id, $data );
}

/**
 * Add a file download log.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_file_download_log( $data = array() ) {
	// A download ID and a payment ID must be supplied for every log that is inserted into the database.
	if ( empty( $data['download_id'] ) || empty( $data['payment_id'] ) ) {
		return false;
	}

	$logs = new EDD_Log_File_Download_Query();

	return $logs->add_item( $data );
}

/**
 * Delete a file download log.
 *
 * @since 3.0.0
 *
 * @param int $log_id Log ID.
 * @return int
 */
function edd_delete_file_download_log( $log_id = 0 ) {
	$logs = new EDD_Log_File_Download_Query();

	return $logs->delete_item( $log_id );
}

/**
 * Update a file download log.
 *
 * @since 3.0.0
 *
 * @param int   $log_id API request log ID.
 * @param array $data   Updated file download log data.
 * @return bool Whether or not the file download log was updated.
 */
function edd_update_file_download_log( $log_id = 0, $data = array() ) {
	$logs = new EDD_Log_File_Download_Query();

	return $logs->update_item( $log_id, $data );
}

/**
 * Query for an API request log.
 *
 * @since 3.0.0
 *
 * @param int $log_id API request log ID.
 * @return object
 */
function edd_get_api_request_log( $log_id = 0 ) {
	return edd_get_api_request_log_by( 'id', $log_id );
}

/**
 * Count API request logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return int
 */
function edd_count_api_request_logs( $args = array() ) {
	$count_args = array(
		'number' => 0,
		'count'  => true,

		'update_cache'      => false,
		'update_meta_cache' => false
	);

	$args = array_merge( $args, $count_args );

	$logs = new EDD_Log_API_Request_Query( $args );
	return absint( $logs->found_items );
}

/**
 * Query for API request logs.
 *
 * @since 3.0.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_api_request_log_by( $field = '', $value = '' ) {
	// Query for API request log
	$logs = new EDD_Log_API_Request_Query( array(
		'number' => 1,
		$field   => $value
	) );

	// Return note
	return reset( $logs->items );
}

/**
 * Query for API request logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return array
 */
function edd_get_api_request_logs( $args = array() ) {
	// Query for API request logs
	$logs = new EDD_Log_API_Request_Query( $args );

	// Return notes
	return $logs->items;
}