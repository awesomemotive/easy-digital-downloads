<?php
/**
 * Email Editor: Heading
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $email->can_edit( 'heading' ) ) {
	return;
}
?>
<div class="edd-form-group">
	<label for="edd-email-heading" class="edd-form-group__label">
		<?php esc_html_e( 'Heading', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<input
			type="text"
			id="edd-email-heading"
			name="heading"
			value="<?php echo esc_attr( $email->heading ); ?>"
			class="regular-text"
		>
	</div>
</div>
