<?php
/**
 * Note Functions
 *
 * @package     EDD
 * @subpackage  Notes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a note.
 *
 * @since 3.0
 *
 * @param array $data
 * @return int
 */
function edd_add_note( $data = array() ) {

	// An object ID and object type must be supplied for every note that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
	$notes = new EDD\Database\Queries\Note();

	return $notes->add_item( $data );
}

/**
 * Delete a note.
 *
 * @since 3.0
 *
 * @param int $note_id Note ID.
 * @return int
 */
function edd_delete_note( $note_id = 0 ) {
	$notes = new EDD\Database\Queries\Note();

	return $notes->delete_item( $note_id );
}

/**
 * Update a note.
 *
 * @since 3.0
 *
 * @param int   $note_id Note ID.
 * @param array $data    Updated note data.
 * @return bool Whether or not the note was updated.
 */
function edd_update_note( $note_id = 0, $data = array() ) {
	$notes = new EDD\Database\Queries\Note();

	return $notes->update_item( $note_id, $data );
}

/**
 * Get a note by ID.
 *
 * @since 3.0
 *
 * @param int $note_id Note ID.
 * @return EDD\Notes\Note
 */
function edd_get_note( $note_id = 0 ) {
	return edd_get_note_by( 'id', $note_id );
}

/**
 * Get a note by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return EDD\Notes\Note
 */
function edd_get_note_by( $field = '', $value = '' ) {
	$notes = new EDD\Database\Queries\Note();

	// Return note
	return $notes->get_item_by( $field, $value );
}

/**
 * Query for notes.
 *
 * @since 3.0
 *
 * @param array $args
 * @return array
 */
function edd_get_notes( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'number' => 30,
		)
	);

	// Instantiate a query object
	$notes = new EDD\Database\Queries\Note();

	// Return notes
	return $notes->query( $r );
}

/**
 * Count notes.
 *
 * @since 3.0
 *
 * @param array $args Arguments.
 * @return int
 */
function edd_count_notes( $args = array() ) {

	// Parse args
	$r = wp_parse_args(
		$args,
		array(
			'count' => true,
		)
	);

	// Query for count(s)
	$notes = new EDD\Database\Queries\Note( $r );

	// Return count(s)
	return absint( $notes->found_items );
}
