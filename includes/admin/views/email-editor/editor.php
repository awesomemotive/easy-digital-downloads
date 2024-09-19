<?php
/**
 * edit-email.php
 *
 * @package   edd
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.0
 *
 * @var \EDD\Emails\Email $email
 */

// Ensure we mark this as an editor page.
get_current_screen()->action = 'edit';

wp_enqueue_style( 'edd-admin-emails' );
if ( $email->can_edit( 'content' ) ) {
	$email->maybe_add_required_tag();
	$html_allowed = $email->supports_html();
	edd_email_tags_inserter_media_button( $html_allowed );
	if ( ! $html_allowed ) {
		add_filter( 'user_can_richedit', '__return_false', 50 );
	}
} else {
	remove_action( 'media_buttons', 'media_buttons' );
}
?>
<form method="POST">
	<?php require_once 'header.php'; ?>
	<div class="wrap">
		<?php do_action( 'edd_email_editor_top', $email ); ?>

		<div class="edd-form edd-form__email">
			<?php
			do_action( 'edd_email_editor_form', $email );
			require_once 'sender.php';
			require_once 'subject.php';
			require_once 'heading.php';
			require_once 'recipient.php';
			require_once 'content.php';
			require_once 'legacy.php';
			require_once 'hidden.php';
			?>
		</div>
	</form>
</div>
