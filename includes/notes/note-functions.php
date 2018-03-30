<?php
/**
 * Note Functions
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
 * Add a note.
 *
 * @since 3.0.0
 *
 * @param array $data
 * @return int
 */
function edd_add_note( $data = array() ) {
	$notes = new EDD_Note_Query();

	return $notes->add_item( $data );
}

/**
 * Delete a note.
 *
 * @since 3.0.0
 *
 * @param int $note_id Note ID.
 * @return int
 */
function edd_delete_note( $note_id = 0 ) {
	$notes = new EDD_Note_Query();

	return $notes->delete_item( $note_id );
}

/**
 * Update a note.
 *
 * @since 3.0.0
 *
 * @param int   $note_id Note ID.
 * @param array $data    Updated note data.
 * @return bool Whether or not the note was updated.
 */
function edd_update_note( $note_id = 0, $data = array() ) {
	$notes = new EDD_Note_Query();

	return $notes->update_item( $note_id, $data );
}

/**
 * Query for a note.
 *
 * @since 3.0.0
 *
 * @param int $note_id Note ID.
 * @return object
 */
function edd_get_note( $note_id = 0 ) {
	return edd_get_note_by( 'id', $note_id );
}

/**
 * Query for notes.
 *
 * @since 3.0.0
 *
 * @param array $args
 * @return array
 */
function edd_get_notes( $args = array() ) {
	// Query for notes
	$notes = new EDD_Note_Query( $args );

	// Return notes
	return $notes->items;
}

/**
 * Query for notes.
 *
 * @since 3.0.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return object
 */
function edd_get_note_by( $field = '', $value = '' ) {
	// Query for note
	$notes = new EDD_Note_Query( array(
		'number' => 1,
		$field   => $value
	) );

	// Return note
	return reset( $notes->items );
}

/**
 * Get total number of notes.
 *
 * @since 3.0.0
 *
 * @return int
 */
function edd_get_note_count() {
	// Query for count
	$notes = new EDD_Note_Query( array(
		'number' => 0,
		'count'  => true,

		'update_cache'      => false,
		'update_meta_cache' => false
	) );

	// Return count
	return absint( $notes->found_items );
}