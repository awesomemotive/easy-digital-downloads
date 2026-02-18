<?php
/**
 * Email Editor: Hidden Inputs
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="edd-form-group hidden">
	<input type="hidden" name="edd-action" value="save_email_settings">
	<input type="hidden" name="email_id" value="<?php echo esc_attr( $email->email_id ); ?>">
	<input type="hidden" name="sender" value="<?php echo esc_attr( $email->sender ); ?>">
	<input type="hidden" name="context" value="<?php echo esc_attr( $email->context ); ?>">
	<input type="hidden" name="recipient" value="<?php echo esc_attr( $email->recipient ); ?>">
	<input type="hidden" name="page" value="<?php echo esc_attr( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS ) ); ?>">
	<input type="hidden" name="tab" value="<?php echo esc_attr( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS ) ); ?>">
	<?php wp_nonce_field( 'edd_save_email', 'edd_save_email_nonce' ); ?>
</div>
