<?php
/**
 * Notes Actions
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a note via AJAX.
 *
 * @since 3.0
 */
function edd_admin_ajax_add_note() {

	// Check AJAX referrer
	check_ajax_referer( 'edd_note', 'nonce' );

	// Bail if user cannot delete notes
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		wp_die( -1 );
	}

	// Get object ID
	$object_id = ! empty( $_POST['object_id'] )
		? absint( $_POST['object_id'] )
		: 0;

	// Get object type
	$object_type = ! empty( $_POST['object_type'] )
		? sanitize_key( $_POST['object_type'] )
		: '';

	// Bail if no object
	if ( empty( $object_id ) || empty( $object_type ) ) {
		wp_die( -1 );
	}

	// Get note contents (maybe sanitize)
	$note = ! empty( $_POST['note'] )
		? trim( wp_kses( stripslashes_deep( $_POST['note'] ), edd_get_allowed_tags() ) )
		: '';

	// Bail if no note
	if ( empty( $note ) ) {
		wp_die( -1 );
	}

	// Add the note
	$note_id = edd_add_note( array(
		'object_id'   => $object_id,
		'object_type' => $object_type,
		'content'     => $note,
		'user_id'     => get_current_user_id()
	) );

	$x = new WP_Ajax_Response();
	$x->add(
		array(
			'what' => 'edd_note_html',
			'data' => edd_admin_get_note_html( $note_id, $object_id ),
		)
	);
	$x->send();
}
add_action( 'wp_ajax_edd_add_note', 'edd_admin_ajax_add_note' );

/**
 * Delete a note.
 *
 * @since 3.0
 *
 * @param array $data Data from $_GET.
 */
function edd_admin_delete_note( $data = array() ) {

	// Bail if missing any data
	if ( empty( $data['_wpnonce'] ) || empty( $data['note_id'] ) ) {
		return;
	}

	// Bail if nonce fails
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_delete_note_' . $data['note_id'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	// Try to delete
	edd_delete_note( $data['note_id'] );

	edd_redirect( edd_get_note_delete_redirect_url() );
}
add_action( 'edd_delete_note', 'edd_admin_delete_note' );

/**
 * Delete a discount note via AJAX.
 *
 * @since 3.0
 */
function edd_admin_ajax_delete_note() {

	// Check AJAX referrer
	check_ajax_referer( 'edd_note', 'nonce' );

	// Bail if user cannot delete notes
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( -1 );
	}

	// Get note ID
	$note_id = ! empty( $_POST['note_id'] )
		? absint( $_POST['note_id'] )
		: 0;

	// Bail if no note
	if ( empty( $note_id ) ) {
		wp_die( -1 );
	}

	// Delete note
	if ( edd_delete_note( $note_id ) ) {
		wp_die( 1 );
	}

	wp_die( 0 );
}
add_action( 'wp_ajax_edd_delete_note', 'edd_admin_ajax_delete_note' );
