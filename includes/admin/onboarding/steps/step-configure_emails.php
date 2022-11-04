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
					<th scope="row"><?php echo esc_html( __( 'Sale Notifications', 'easy-digital-downloads' ) ); ?></th>
					<td>
						<input type="hidden" name="edd_settings[disable_admin_notices]" value="1">
						<label class="edd-toggle">
							<input type="checkbox" name="edd_settings[disable_admin_notices]" id="edd_settings[disable_admin_notices]" value="0" <?php checked( (bool) edd_get_option( 'disable_admin_notices', false ), false, true ); ?>>
							<br> <?php echo esc_html( __( 'Receive sales notification emails.', 'easy-digital-downloads' ) ); ?>
						</label>
					</td>
				</tr>
				<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections_purchase_receipt ) ); ?>
				<tr>
					<th scope="row">
						<label for="edd_settings[purchase_receipt]"><?php echo esc_html( __( 'Purchase Receipt Email', 'easy-digital-downloads' ) ); ?></label>
					</th>
				</tr>

				<tr>
					<td colspan="2">
						<?php do_action( 'edd_settings_tab_top_emails_purchase_receipts' ); ?>

						<a href="#TB_inline?width=640&inlineId=edd-insert-email-tag" class="edd-email-tags-inserter thickbox button edd-thickbox" style="padding-left: 0.4em;">
							<span class="wp-media-buttons-icon dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'Insert Marker', 'easy-digital-downloads' ); ?>
						</a>

						<textarea name="edd_settings_purchase_receipt" id="edd_settings_purchase_receipt" rows="12" style="width: 100%;"></textarea>
						<?php
						// echo edd_rich_editor_callback(
						// 	array(
						// 		'field_class' => 'purchase_receipt',
						// 		'id'          => 'purchase_receipt',
						// 		'label_for'   => 'edd_settings[purchase_receipt]',
						// 		'type'        => 'rich_editor',
						// 		'size'        => 12,
						// 		'std'         => __( "Dear", "easy-digital-downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n{download_list}\n\n{sitename}",
						// 	)
						// );
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php

	return ob_get_clean();
}
