<?php
/**
 * Email Editor: Sender
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="edd-form-group edd-email__sender">
	<div class="edd-form-group__label">
		<?php esc_html_e( 'From', 'easy-digital-downloads' ); ?>
	</div>
	<div class="edd-form-group__control">
		<?php
		echo esc_attr( edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) ) );
		?>
	</div>
</div>
