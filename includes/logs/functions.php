<?php
/**
 * Log Functions
 *
 * @package     EDD
 * @subpackage  Logs
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

	// An object ID and object type must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
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
 * Get a log by ID.
 *
 * @since 3.0.0
 *
 * @param int $log_id Log ID.
 * @return object
 */
function edd_get_log( $log_id = 0 ) {
	return edd_get_log_by( 'id', $log_id );
}

/**
 * Get a log by a specific field's value.
 *
 * @since 3.0.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_log_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$logs = new EDD_Log_Query();

	// Get an item
	return $logs->get_item_by( $field, $value );
}

/**
 * Query for logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return array
 */
function edd_get_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$logs = new EDD_Log_Query();

	// Return notes
	return $logs->query( $r );
}

/**
 * Count logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return int
 */
function edd_count_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$logs = new EDD_Log_Query( $r );

	// Return count(s)
	return absint( $logs->found_items );
}

/** File Downloads ************************************************************/

/**
 * Add a file download log.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_file_download_log( $data = array() ) {

	// A download ID and a payment ID must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['download_id'] ) || empty( $data['payment_id'] ) ) {
		return false;
	}

	// Instantiate a query object
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
 * Get a file download log by ID.
 *
 * @since 3.0.0
 *
 * @param int $log_id Log ID.
 * @return object
 */
function edd_get_file_download_log( $log_id = 0 ) {
	return edd_get_file_download_log_by( 'id', $log_id );
}

/**
 * Get a file download log by field and value.
 *
 * @since 3.0.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_file_download_log_by( $field = '', $value = '' ) {

	// Instantiate a query object
	$logs = new EDD_Log_File_Download_Query();

	// Return item
	return $logs->get_item_by( $field, $value );
}

/**
 * Query for file download logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return array
 */
function edd_get_file_download_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$logs = new EDD_Log_File_Download_Query();

	// Return logs
	return $logs->query( $r );
}

/**
 * Count file download logs.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return int
 */
function edd_count_file_download_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$logs = new EDD_Log_File_Download_Query( $r );

	// Return count(s)
	return absint( $logs->found_items );
}

/** API Requests **************************************************************/

/**
 * Add an API request log.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_api_request_log( $data = array() ) {

	// A request is required for every API request log that is inserted into
	// the database.
	if ( empty( $data['request'] ) ) {
		return false;
	}

	// Instantiate a query object
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
 * Get an API request log by ID.
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
 * Get an API request log by a specific field's value.
 *
 * @since 3.0.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_api_request_log_by( $field = '', $value = '' ) {
	$logs = new EDD_Log_API_Request_Query();

	// Return note
	return $logs->get_item_by( $field, $value );
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

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$logs = new EDD_Log_API_Request_Query();

	// Return logs
	return $logs->query( $r );
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

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$logs = new EDD_Log_API_Request_Query( $r );

	// Return count(s)
	return absint( $logs->found_items );
}

/** Meta **********************************************************************/

/**
 * Add meta data field to a log.
 *
 * @since 3.0.0
 *
 * @param int     $log_id     Log ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique     Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_log_meta( $log_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_log', $log_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from a log.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.0.0
 *
 * @param int     $log_id     Log ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_log_meta( $log_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_log', $log_id, $meta_key, $meta_value );
}

/**
 * Retrieve log meta field for a log.
 *
 * @since 3.0.0
 *
 * @param int     $log_id  Log ID.
 * @param string  $key     Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool    $single  Optional, default is false. If true, return only the first value of the specified meta_key.
 *                         This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_log_meta( $log_id, $key = '', $single = false ) {
	return get_metadata( 'edd_log', $log_id, $key, $single );
}

/**
 * Update log meta field based on log ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and log ID.
 *
 * If the meta field for the log does not exist, it will be added.
 *
 * @since 3.0.0
 *
 * @param int     $log_id    Note ID.
 * @param string  $meta_key   Meta data key.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_log_meta( $log_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_log', $log_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from log meta matching meta key.
 *
 * @since 3.0.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the log meta key was deleted from the database.
 */
function edd_delete_log_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_log', null, $meta_key, '', true );
}