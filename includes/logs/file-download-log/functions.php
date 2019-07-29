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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add a file download log.
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
function edd_add_file_download_log( $data = array() ) {

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
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	return $file_download_logs->add_item( $data );
}

/**
 * Delete a file download log.
 *
 * @since 3.0
 *
 * @param int $file_download_log_id Log ID.
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
 * @param int   $file_download_log_id Log ID.
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
 * @return EDD\File_Download_Logs\File_Download_Log|false Log object if successful, false otherwise.
 */
function edd_get_file_download_log( $file_download_log_id = 0 ) {
	return edd_get_file_download_log_by( 'id', $file_download_log_id );
}

/**
 * Get a file download log by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return EDD\File_Download_Logs\File_Download_Log|false Log object if successful, false otherwise.
 */
function edd_get_file_download_log_by( $field = '', $value = '' ) {
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	// Return log.
	return $file_download_logs->get_item_by( $field, $value );
}

/**
 * Query for file download logs.
 *
 * @see \EDD\Database\Queries\Log_File_Download::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_File_Download` for
 *                    accepted arguments.
 * @return \EDD\File_Download_Logs\File_Download_Log[] Array of `Log` objects.
 */
function edd_get_file_download_logs( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object.
	$file_download_logs = new EDD\Database\Queries\Log_File_Download();

	// Return file download logs.
	return $file_download_logs->query( $r );
}

/**
 * Count logs.
 *
 * @see \EDD\Database\Queries\Log_File_Download::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `EDD\Database\Queries\Log_File_Download` for
 *                    accepted arguments.
 * @return int Number of logs returned based on query arguments passed.
 */
function edd_count_file_download_logs( $args = array() ) {

	// Parse args.
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s).
	$file_download_logs = new EDD\Database\Queries\Log_File_Download( $r );

	// Return the number of file download logs that were found.
	return absint( $file_download_logs->found_items );
}
