<?php
/**
 * Onboarding Wizard Configure Emails Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\ConfigureEmails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Onboarding\Helpers;

/**
 * Initialize step.
 *
 * @since 3.2
 */
function initialize() {}

/**
 * Get step view.
 *
 * @since 3.2
 */
function step_html() {
	$sections = array(
		'edd_settings_emails_main' => array(
			'email_logo',
			'from_name',
			'from_email',
		),

	);

	$sections_purchase_receipt = array(
		'edd_settings_emails_purchase_receipts' => array(
			'purchase_subject',
		),
	);

	ob_start();
	?>
	<form method="post" action="options.php" class="edd-settings-form">
		<?php settings_fields( 'edd_settings' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections ) ); ?>
				<tr>
					<th scope="row"><?php echo esc_html_e( 'Sale Notifications', 'easy-digital-downloads' ); ?></th>
					<td>
						<input type="hidden" name="edd_settings[disable_admin_notices]" value="1">
						<label class="edd-toggle">
							<input type="checkbox" name="edd_settings[disable_admin_notices]" id="edd_settings[disable_admin_notices]" value="0" <?php checked( (bool) edd_get_option( 'disable_admin_notices', false ), false, true ); ?>>
							<br> <?php echo esc_html_e( 'Receive sales notification emails.', 'easy-digital-downloads' ); ?>
						</label>
					</td>
				</tr>
				<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections_purchase_receipt ) ); ?>
				<tr>
					<th scope="row">
						<label for="edd_settings[purchase_receipt]"><?php echo esc_html_e( 'Purchase Receipt Email', 'easy-digital-downloads' ); ?></label>
					</th>
				</tr>

				<tr>
					<td colspan="2">
						<?php do_action( 'edd_settings_tab_top_emails_purchase_receipts' ); ?>
						<div id="edd-onboarding__insert-marker-button" style="display: none;">
							<a href="#TB_inline?width=640&inlineId=edd-insert-email-tag" class="edd-email-tags-inserter thickbox button edd-thickbox" style="padding-left: 0.4em;">
								<span class="wp-media-buttons-icon dashicons dashicons-editor-code"></span>
								<?php esc_html_e( 'Insert Marker', 'easy-digital-downloads' ); ?>
							</a>
						</div>
						<textarea name="edd_settings[purchase_receipt]" id="edd_settings_purchase_receipt" rows="12" style="width: 100%;"><?php echo wpautop( wp_kses_post( edd_get_option( 'purchase_receipt' ) ) ); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php

	return ob_get_clean();
}
