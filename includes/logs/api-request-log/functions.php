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

use EDD\Logs\Api_Request_Log;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Add an API request log.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of API request log data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int $user_id          WordPress user ID of the authenticated user
 *                                 making the API request. Default 0.
 *     @type string $api_key       User's API key. Default empty.
 *     @type string $token         User's API token. Default empty.
 *     @type string $version       API version used. Default empty.
 *     @type string $request       API request query. Default empty.
 *     @type string $error         Error(s), if any when making the API request.
 *                                 Default empty.
 *     @type string $ip            IP address of the client making the API
 *                                 request. Default empty.
 *     @type string $time          Time it took for API request to complete. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the API request log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the API request log was last
 *                                 modified. Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of inserted API request log, false on error.
 */
function edd_add_api_request_log( $data = array() ) {

	// A request is required for every API request log that is inserted into
	// the database.
	if ( empty( $data['request'] ) ) {
		return false;
	}

	/**
	 * Allow the ability to disable an API Request Log from being inserted.
	 *
	 * @since 3.1
	 *
	 * @param bool  $should_record_log If this API reqeust should be logged.
	 * @param array $data              The data to be logged.
	 */
	$should_record_log = apply_filters( 'edd_should_log_api_request', true, $data );

	if ( false === $should_record_log ) {
		return false;
	}

	// Instantiate a query object.
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->add_item( $data );
}

/**
 * Delete an API request log.
 *
 * @since 3.0
 *
 * @param int $api_request_log_id API request log ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
 */
function edd_delete_api_request_log( $api_request_log_id = 0 ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->delete_item( $api_request_log_id );
}

/**
 * Update an API request log.
 *
 * @since 3.0
 *
 * @param int   $api_request_log_id API request log ID.
 * @param array $data {
 *     Array of API request log data. Default empty.
 *
 *     @type int $user_id          WordPress user ID of the authenticated user
 *                                 making the API request. Default 0.
 *     @type string $api_key       User's API key. Default empty.
 *     @type string $token         User's API token. Default empty.
 *     @type string $version       API version used. Default empty.
 *     @type string $request       API request query. Default empty.
 *     @type string $error         Error(s), if any when making the API request.
 *                                 Default empty.
 *     @type string $ip            IP address of the client making the API
 *                                 request. Default empty.
 *     @type string $time          Time it took for API request to complete. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the API request log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the API request log was last
 *                                 modified. Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_api_request_log( $api_request_log_id = 0, $data = array() ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	return $api_request_logs->update_item( $api_request_log_id, $data );
}

/**
 * Get an API request log by ID.
 *
 * @since 3.0
 *
 * @param int $api_request_log_id API request log ID.
 * @return Api_Request_Log|false Api_Request_Log object if successful, false otherwise.
 */
function edd_get_api_request_log( $api_request_log_id = 0 ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return API Request Log.
	return $api_request_logs->get_item( $api_request_log_id );
}

/**
 * Get an API request log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return Api_Request_Log|false Api_Request_Log object if successful, false otherwise.
 */
function edd_get_api_request_log_by( $field = '', $value = '' ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return API Request Log.
	return $api_request_logs->get_item_by( $field, $value );
}

/**
 * Query for API request logs.
 *
 * @see \EDD\Database\Queries\Log_Api_Request::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_Api_Request` for
 *                    accepted arguments.
 * @return Api_Request_Log[] Array of `Api_Request_Log` objects.
 */
function edd_get_api_request_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return logs
	return $api_request_logs->query( $r );
}

/**
 * Count API request logs.
 *
 * @see \EDD\Database\Queries\Log_Api_Request::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_Api_Request` for
 *                    accepted arguments.
 * @return int Number of API request logs returned based on query arguments passed.
 */
function edd_count_api_request_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request( $r );

	// Return count(s)
	return absint( $api_request_logs->found_items );
}
