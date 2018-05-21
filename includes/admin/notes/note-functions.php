<?php
/**
 * Notes Functions
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Pippin Williamson
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

	<div class="edd-notes" id="edd-notes">
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
	$user_id = $note->get_user_id();
	$author  = ! empty( $user_id )
		? get_userdata( $user_id )->display_name
		: __( 'EDD Bot', 'easy-digital-downloads' );

	// URL to delete note
	$delete_note_url = wp_nonce_url( add_query_arg( array(
		'edd-action' => 'delete_note',
		'note_id'    => $note->get_id()
	) ), 'edd_delete_note_' . $note->get_id() );

	// Start a buffer
	ob_start(); ?>

	<div class="edd-note" id="edd-note-<?php echo esc_attr( $note->get_id() ); ?>">
		<div>
			<strong class="edd-note-author"><?php echo esc_html( $author ); ?></strong>
			<time datetime="<?php echo esc_attr( $note->get_date_created() ); ?>"><?php echo edd_date_i18n( $note->get_date_created(), 'datetime' ); ?></time>
			<p><?php echo make_clickable( $note->get_content() ); ?></p>
			<a href="<?php echo esc_url( $delete_note_url ); ?>#edd-notes" class="edd-delete-note" data-note-id="<?php echo esc_attr( $note->get_id() ); ?>" data-object-id="<?php echo esc_attr( $note->get_object_id() ); ?>" data-object-type="<?php echo esc_attr( $note->get_object_type() ); ?>">
				<?php _e( 'Delete', 'easy-digital-downloads' ); ?>
			</a>
		</div>
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

	// Start a buffer
	ob_start();?>

	<div class="edd-add-note">
		<textarea name="edd-note" id="edd-note"></textarea>

		<p>
			<button type="button" id="edd-add-note" class="edd-note-submit button button-secondary left" data-object-id="<?php echo esc_attr( $object_id ); ?>" data-object-type="<?php echo esc_attr( $object_type ); ?>">
				<?php _e( 'Add Note', 'easy-digital-downloads' ); ?>
			</button>
		</p>
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
 * @param int $total The total number of notes for this view
 */
function edd_admin_get_notes_pagination( $total = 0, $pag_arg = 'paged' ) {

	// Default pagination arg
	if ( empty( $pag_arg ) ) {
		$pag_arg = 'paged';
	}

	// Don't allow pagination beyond the boundaries
	$paged = ! empty( $_GET[ $pag_arg ] ) && is_numeric( $_GET[ $pag_arg ] )
		? absint( $_GET[ $pag_arg ] )
		: 1;

	// Maximum notes per page
	$per_page        = apply_filters( 'edd_notes_per_page', 20 );
	$total_pages     = ceil( $total / $per_page );
	$pagination_args = array(
		'base'     => '%_%',
		'format'   => "?{$pag_arg}=%#%",
		'total'    => $total_pages,
		'current'  => $paged,
		'show_all' => true
	);

	// Start a buffer
	ob_start(); ?>

	<div class="edd-note-pagination">
		<?php echo paginate_links( $pagination_args ); ?>
	</div>

	<?php

	// Return the current buffer
	return ob_get_clean();
}
