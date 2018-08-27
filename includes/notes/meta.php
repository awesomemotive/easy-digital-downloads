<?php
/**
 * Note Meta Functions
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
 * Add meta data field to a note.
 *
 * @since 3.0
 *
 * @param int     $note_id    Note ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param bool    $unique     Optional. Whether the same key should not be added. Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 */
function edd_add_note_meta( $note_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'edd_note', $note_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove meta data matching criteria from a note.
 *
 * You can match based on the key, or key and value. Removing based on key and value, will keep from removing duplicate
 * meta data with the same key. It also allows removing all meta data matching key, if needed.
 *
 * @since 3.0
 *
 * @param int     $note_id    Note ID.
 * @param string  $meta_key   Meta data name.
 * @param mixed   $meta_value Optional. Meta data value. Must be serializable if non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function edd_delete_note_meta( $note_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'edd_note', $note_id, $meta_key, $meta_value );
}

/**
 * Retrieve note meta field for a note.
 *
 * @since 3.0
 *
 * @param int     $note_id  Note ID.
 * @param string  $key      Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
 * @param bool    $single   Optional, default is false. If true, return only the first value of the specified meta_key.
 *                          This parameter has no effect if meta_key is not specified.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function edd_get_note_meta( $note_id, $key = '', $single = false ) {
	return get_metadata( 'edd_note', $note_id, $key, $single );
}

/**
 * Update note meta field based on note ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and note ID.
 *
 * If the meta field for the note does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int     $note_id    Note ID.
 * @param string  $meta_key   Meta data key.
 * @param mixed   $meta_value Meta data value. Must be serializable if non-scalar.
 * @param mixed   $prev_value Optional. Previous value to check before removing. Default empty.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function edd_update_note_meta( $note_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'edd_note', $note_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete everything from note meta matching meta key.
 *
 * @since 3.0
 *
 * @param string $meta_key Key to search for when deleting.
 *
 * @return bool Whether the note meta key was deleted from the database.
 */
function edd_delete_note_meta_by_key( $meta_key ) {
	return delete_metadata( 'edd_note', null, $meta_key, '', true );
}