<?php
/**
 * Notes Functions
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
 * Get the HTML used to output all of the notes for a single object
 *
 * @since 3.0
 *
 * @param array $notes
 * @return string
 */
function edd_admin_get_notes_html( $notes = array() ) {

	// Whether to show or hide the "No notes" default text
	$no_notes_display = ! empty( $notes )
		? ' style="display:none;"'
		: '';

	// Start a buffer
	ob_start(); ?>

	<div id="edd-notes" class="edd-notes">
		<?php

		// Output notes
		foreach ( $notes as $note ) {
			echo edd_admin_get_note_html( $note );
		}

		?>

		<p class="edd-no-notes"<?php echo $no_notes_display; ?>>
			<?php _e( 'No notes.', 'easy-digital-downloads' ); ?>
		</p>
	</div>

	<?php

	// Return the current buffer
	return ob_get_clean();
}

/**
 * Get the HTML used to output a single note, from an array of notes
 *
 * @since 3.0
 * @param int $note_id
 *
 * @return string
 */
function edd_admin_get_note_html( $note_id = 0 ) {

	/** @var $note EDD\Notes\Note For IDE type-hinting purposes. */

	// Get the note
	$note = is_numeric( $note_id )
		? edd_get_note( $note_id )
		: $note_id;

	// No note, so bail
	if ( empty( $note ) ) {
		return;
	}

	// User
	$user_id = $note->user_id;
	$author  = edd_get_bot_name();
	if ( ! empty( $user_id ) ) {
		/* translators: user ID */
		$author      = sprintf( __( 'User ID #%s', 'easy-digital-downloads' ), $user_id );
		$user_object = get_userdata( $user_id );
		if ( $user_object ) {
			$author = ! empty( $user_object->display_name ) ? $user_object->display_name : $user_object->user_login;
		}
	}

	// URL to delete note
	$delete_note_url = wp_nonce_url( add_query_arg( array(
		'edd-action' => 'delete_note',
		'note_id'    => absint( $note->id ),
	) ), 'edd_delete_note_' . absint( $note->id ) );

	// Start a buffer
	ob_start();
	?>

	<div class="edd-note" id="edd-note-<?php echo esc_attr( $note->id ); ?>">
		<div class="edd-note__header">
			<strong class="edd-note-author"><?php echo esc_html( $author ); ?></strong>
			<time datetime="<?php echo esc_attr( EDD()->utils->date( $note->date_created, null, true )->toDateTimeString() ); ?>"><?php echo edd_date_i18n( $note->date_created, 'datetime' ); ?></time>
			<a href="<?php echo esc_url( $delete_note_url ); ?>#edd-notes" class="edd-delete-note" data-note-id="<?php echo esc_attr( $note->id ); ?>" data-object-id="<?php echo esc_attr( $note->object_id ); ?>" data-object-type="<?php echo esc_attr( $note->object_type ); ?>">
				<?php echo esc_html_x( '&times;', 'Delete note', 'easy-digital-downloads' ); ?>
			</a>
		</div>

		<?php echo wpautop( make_clickable( $note->content ) ); ?>
	</div>

	<?php

	// Return the current buffer
	return ob_get_clean();
}

/**
 * Get the HTML used to add a note to an object ID and type
 *
 * @since 3.0
 *
 * @param int    $object_id
 * @param string $object_type
 *
 * @return string
 */
function edd_admin_get_new_note_form( $object_id = 0, $object_type = '' ) {

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return '';
	}

	// Start a buffer
	ob_start();?>

	<div class="edd-add-note">
		<div class="edd-form-group">
			<label class="edd-form-group__label screen-reader-text" for="edd-note">
				<?php esc_html_e( 'Note', 'easy-digital-downloads' ); ?>
			</label>

			<div id="edd-form-group__control">
				<textarea name="edd-note" id="edd-note" class="edd-form-group__input"></textarea>
			</div>
		</div>

		<div class="edd-form-group">
			<button type="button" id="edd-add-note" class="edd-note-submit button button-secondary left" data-object-id="<?php echo esc_attr( $object_id ); ?>" data-object-type="<?php echo esc_attr( $object_type ); ?>">
				<?php _e( 'Add Note', 'easy-digital-downloads' ); ?>
			</button>
			<span class="spinner"></span>
		</div>

		<?php wp_nonce_field( 'edd_note', 'edd_note_nonce' ); ?>
	</div>

	<?php

	// Return the current buffer
	return ob_get_clean();
}

/**
 * Return the URL to redirect to after deleting a note
 *
 * For now, this is always the current URL, because we aren't ever sure where
 * notes are being used. Maybe this will need a filter or something, someday.
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_note_delete_redirect_url() {

	// HTTP or HTTPS
	$scheme = is_ssl()
		? 'https'
		: 'http';

	// Return the concatenated URL
	return "{$scheme}://{$_SERVER[HTTP_HOST]}{$_SERVER[REQUEST_URI]}";
}

/**
 * Return the HTML used to paginate through notes.
 *
 * @since 3.0
 * @param array $args
 */
function edd_admin_get_notes_pagination( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'total'        => 0,
		'pag_arg'      => 'paged',
		'base'         => '%_%',
		'show_all'     => true,
		'prev_text'    => is_rtl() ? '&rarr;' : '&larr;',
		'next_text'    => is_rtl() ? '&larr;' : '&rarr;',
		'add_fragment' => ''
	) );

	// Maximum notes per page
	$per_page    = apply_filters( 'edd_notes_per_page', 20 );
	$r['total']  = ceil( $r['total'] / $per_page );
	$r['format'] = "?{$r['pag_arg']}=%#%";

	// Don't allow pagination beyond the boundaries
	$r['current'] = ! empty( $_GET[ $r['pag_arg'] ] ) && is_numeric( $_GET[ $r['pag_arg'] ] )
		? absint( $_GET[ $r['pag_arg'] ] )
		: 1;

	// Start a buffer
	ob_start(); ?>

	<div class="edd-note-pagination">
		<?php echo paginate_links( $r ); ?>
	</div>

	<?php

	// Return the current buffer
	return ob_get_clean();
}
