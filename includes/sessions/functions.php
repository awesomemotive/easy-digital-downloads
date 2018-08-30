<?php
/**
 * Session Functions
 *
 * @package     EDD
 * @subpackage  Sessions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a session.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int
 */
function edd_add_session( $data = array() ) {

	// A hash and content must be supplied for every session that is
	// inserted into the database.
	if ( empty( $data['hash'] ) ) {
		return false;
	}

	// Instantiate a query object
	$sessions = new EDD\Database\Queries\Session();

	return $sessions->add_item( $data );
}

/**
 * Delete a session.
 *
 * @since 3.0
 *
 * @param int $session_id Session ID.
 * @return int
 */
function edd_delete_session( $session_id = 0 ) {
	$sessions = new EDD\Database\Queries\Session();

	return $sessions->delete_item( $session_id );
}

/**
 * Update a session.
 *
 * @since 3.0
 *
 * @param int   $session_id Session ID.
 * @param array $data    Updated session data.
 * @return bool Whether or not the session was updated.
 */
function edd_update_session( $session_id = 0, $data = array() ) {
	$sessions = new EDD\Database\Queries\Session();

	return $sessions->update_item( $session_id, $data );
}

/**
 * Get a session by ID.
 *
 * @since 3.0
 *
 * @param int $session_id Session ID.
 * @return EDD\Sessions\Session
 */
function edd_get_session( $session_id = 0 ) {
	return edd_get_session_by( 'id', $session_id );
}

/**
 * Get a session by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return EDD\Sessions\Session
 */
function edd_get_session_by( $field = '', $value = '' ) {
	$sessions = new EDD\Database\Queries\Session();

	// Return session
	return $sessions->get_item_by( $field, $value );
}

/**
 * Query for sessions.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_sessions( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$sessions = new EDD\Database\Queries\Session();

	// Return sessions
	return $sessions->query( $r );
}

/**
 * Count sessions.
 *
 * @since 3.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_count_sessions( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$sessions = new EDD\Database\Queries\Session( $r );

	// Return count(s)
	return absint( $sessions->found_items );
}
