<?php
/**
 * Email Editor: Subject
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $email->can_edit( 'subject' ) ) {
	?>
	<div class="edd-form-group edd-email__subject">
		<div class="edd-form-group__label"><?php esc_html_e( 'Subject', 'easy-digital-downloads' ); ?></div>
		<div class="edd-form-group__control">
			<?php echo esc_attr( $email->subject ); ?>
		</div>
	</div>
	<?php
	return;
}
?>
<div class="edd-form-group">
	<label for="edd-email-subject" class="edd-form-group__label">
		<?php esc_html_e( 'Subject', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<input
			type="text"
			id="edd-email-subject"
			name="subject"
			value="<?php echo esc_attr( $email->subject ); ?>"
			class="regular-text"
			required
		>
	</div>
</div>
