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
 *     @type string $date_created  The date & time the adjustment was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified The date & time the adjustment was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_log( $data = array() ) {

	// An object ID and object type must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
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
 *     @type string $date_created  The date & time the adjustment was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified The date & time the adjustment was last modified.
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
 * @return EDD\Logs\Log|false Log object if successful, false otherwise.
 */
function edd_get_log( $log_id = 0 ) {
	return edd_get_log_by( 'id', $log_id );
}

/**
 * Get a log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\Logs\Log|false Log object if successful, false otherwise.
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
 * @return \EDD\Logs\Log[] Array of `Log` objects.
 */
function edd_get_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$logs = new EDD\Database\Queries\Log();

	// Return notes
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

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$logs = new EDD\Database\Queries\Log( $r );

	// Return count(s)
	return absint( $logs->found_items );
}

/** File Downloads ************************************************************/

/**
 * Add a file download log.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of file download log data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int $product_id
 *     @type int $file_id          File ID corresponding to the URL used
 *                                 to download the file. Default 0.
 *     @type int $order_id         Order ID corresponding to the URL used
 *                                 to download the file. Default 0.
 *     @type int $price_id         Price ID of the download for which the file
 *                                 is being downloaded. Default 0.
 *     @type int $customer_id      ID of the customer downloading the file.
 *                                 Default 0.
 *     @type string $ip            IP address of the client downloading the file.
 *                                 Default empty.
 *     @type string $user_agent    User agent of the client downloading the file.
 *                                 Default empty.
 *     @type string $date_created  The date & time the file download log was inserted.
 *     @type string $date_modified The date & time the file download log was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created log, false on error.
 */
function edd_add_file_download_log( $data = array() ) {

	// A product ID and an order ID must be supplied for every log that is
	// inserted into the database.
	if ( empty( $data['product_id'] ) || empty( $data['order_id'] ) ) {
		return false;
	}

	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	return $file_download_logs->add_item( $data );
}

/**
 * Delete a file download log.
 *
 * @since 3.0
 *
 * @param int $file_download_log_id File download log ID.
 * @return int|false `1` if the adjustment was deleted successfully, false on error.
 */
function edd_delete_file_download_log( $file_download_log_id = 0 ) {
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	return $file_download_logs->delete_item( $file_download_log_id );
}

/**
 * Update a file download log.
 *
 * @since 3.0
 *
 * @param int   $file_download_log_id File download log ID.
 * @param array $data {
 *     Array of file download log data. Default empty.
 *
 *     @type int $product_id
 *     @type int $file_id          File ID corresponding to the URL used
 *                                 to download the file. Default 0.
 *     @type int $order_id         Order ID corresponding to the URL used
 *                                 to download the file. Default 0.
 *     @type int $price_id         Price ID of the download for which the file
 *                                 is being downloaded. Default 0.
 *     @type int $customer_id      ID of the customer downloading the file.
 *                                 Default 0.
 *     @type string $ip            IP address of the client downloading the file.
 *                                 Default empty.
 *     @type string $user_agent    User agent of the client downloading the file.
 *                                 Default empty.
 *     @type string $date_created  The date & time the file download log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified The date & time the file download log was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return int|false Number of rows updated if successful, false otherwise.
 */
function edd_update_file_download_log( $file_download_log_id = 0, $data = array() ) {
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	return $file_download_logs->update_item( $file_download_log_id, $data );
}

/**
 * Get a file download log by ID.
 *
 * @since 3.0
 *
 * @param int $file_download_log_id Log ID.
 * @return \EDD\Logs\Log|false Log object if successful, false otherwise.
 */
function edd_get_file_download_log( $file_download_log_id = 0 ) {
	return edd_get_file_download_log_by( 'id', $file_download_log_id );
}

/**
 * Get a file download log by field and value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Logs\Log|false Log object if successful, false otherwise.
 */
function edd_get_file_download_log_by( $field = '', $value = '' ) {
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	// Return file download log
	return $file_download_logs->get_item_by( $field, $value );
}

/**
 * Query for file download logs.
 *
 * @see \EDD\Database\Queries\Log_File_Download::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_File_Download`
 *                    for accepted arguments.
 * @return \EDD\Logs\File_Download_Log[] Array of `File_Download_Log` objects.
 */
function edd_get_file_download_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	// Return file download logs
	return $file_download_logs->query( $r );
}

/**
 * Count file download logs.
 *
 * @see \EDD\Database\Queries\Log_File_Download::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_File_Download`
 *                    for accepted arguments.
 * @return int Number of file download logs returned based on query arguments passed.
 */
function edd_count_file_download_logs( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$file_download_logs = new EDD\Database\Queries\Log_File_Download( $r );

	// Return count(s)
	return absint( $file_download_logs->found_items );
}

/** API Requests **************************************************************/

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
 *     @type string $date_created  The date & time the API request log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified The date & time the API request log was last
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

	// Instantiate a query object
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
 *     @type string $date_created  The date & time the API request log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified The date & time the API request log was last
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
 * @return \EDD\Logs\Api_Request_Log|false Api_Request_Log object if successful,
 *                                         false otherwise.
 */
function edd_get_api_request_log( $api_request_log_id = 0 ) {
	return edd_get_api_request_log_by( 'id', $api_request_log_id );
}

/**
 * Get an API request log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \EDD\Logs\Api_Request_Log|false Api_Request_Log object if successful,
 *                                         false otherwise.
 */
function edd_get_api_request_log_by( $field = '', $value = '' ) {
	$api_request_logs = new EDD\Database\Queries\Log_Api_Request();

	// Return note
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
 * @return \EDD\Logs\Api_Request_Log[] Array of `Api_Request_Log` objects.
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