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