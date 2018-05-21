<?php
/**
 * Discount Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Builds the HTML for display a discount note.
 *
 * @since 3.0
 *
 * @param object|int $note        The note object or ID.
 * @param int        $discount_id The discount ID the note is connected to.
 * @return string HTML for display.
 */
function edd_get_discount_note_html( $note = 0, $discount_id = 0 ) {

	// Get the note
	if ( is_numeric( $note ) ) {
		$note = edd_get_note( $note );
	}

	// No note, so bail
	if ( empty( $note ) ) {
		return;
	}

	// User
	$user = ! empty( $note->get_user_id() )
		? get_userdata( $note->get_user_id() )->display_name
		: __( 'EDD Bot', 'easy-digital-downloads' );

	// URL to delete note
	$delete_note_url = wp_nonce_url( add_query_arg( array(
		'edd-action'  => 'delete_discount_note',
		'note_id'     => $note->get_id(),
		'discount_id' => $discount_id,
	), admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ), 'edd_delete_discount_note_' . $note->get_id() );

	// Output
	$note_html = '<div class="edd-discount-note" id="edd-discount-note-' . esc_attr( $note->get_id() ) . '">';
		$note_html .='<div><strong class="edd-discount-note-author">' . esc_html( $user ) . '</strong>';
		$note_html .= '<time datetime="' . esc_attr( $note->get_date_created() ) . '">' . edd_date_i18n( $note->get_date_created(), 'datetime' ) . '</time>';
		$note_html .= '<p>' . make_clickable( $note->get_content() ) . '</p>';
		$note_html .= '<a href="' . esc_url( $delete_note_url ) . '#edd-discount-notes" class="edd-delete-discount-note" data-note-id="' . esc_attr( $note->get_id() ) . '" data-discount-id="' . esc_attr( $discount_id ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';
		$note_html .= '</div>';
	$note_html .= '</div>';

	// Return
	return $note_html;
}
