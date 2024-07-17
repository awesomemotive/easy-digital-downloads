<?php
/**
 * Email Editor: Legacy
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

// If the email has no ID, it does not exist in the database and we cannot delete legacy data.
if ( ! $email->email->id ) {
	return;
}

// If the email has no legacy data, we don't need to show this option.
if ( ! $email->has_legacy_data() ) {
	return;
}

// If the email has a scheduled event to remove the legacy data, show when the legacy data will be removed.
$event = wp_get_scheduled_event( 'edd_email_legacy_data_cleanup', array( absint( $email->email->id ) ) );
if ( empty( $event->timestamp ) ) {
	return;
}

?>
<div class="notice notice-success inline">
	<p>
		<span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
		<?php
		printf(
			/* translators: %s: Date and time of the scheduled event. */
			esc_html__( 'Success! This email has been converted to the new email management system. We will keep a backup of the old settings which are no longer used, until %s, after which we will remove them to improve site performance.', 'easy-digital-downloads' ),
			date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $event->timestamp )
		);
		?>
	</p>
</div>
