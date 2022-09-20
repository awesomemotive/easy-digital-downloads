<?php
/**
 * File Download Log Functions.
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

use EDD\Logs\File_Download_Log;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the file download log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the file download log was last modified.
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

	/**
	 * Allow the ability to disable a File Download Log being inserted.
	 *
	 * @since 3.1
	 *
	 * @param bool  $should_record_log If this file download should be logged.
	 * @param array $data              The data to be logged.
	 */
	$should_record_log = apply_filters( 'edd_should_log_file_download', true, $data );

	if ( false === $should_record_log ) {
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
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the file download log was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the file download log was last modified.
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
 * @return File_Download_Log|false Log object if successful, false otherwise.
 */
function edd_get_file_download_log( $file_download_log_id = 0 ) {
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	// Return file download log
	return $file_download_logs->get_item( $file_download_log_id );
}

/**
 * Get a file download log by field and value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return File_Download_Log|false Log object if successful, false otherwise.
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
 * @return File_Download_Log[] Array of `File_Download_Log` objects.
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
