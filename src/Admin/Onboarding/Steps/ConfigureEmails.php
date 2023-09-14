<?php
/**
 * Onboarding Wizard Configure Emails Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding\Steps;

use EDD\Emails\Registry;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class ConfigureEmails extends Step {

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	public function step_html() {
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

		// Get an empty instance of the order receipt email, so we can use it to get the raw body content.
		$order_receipt = Registry::get( 'order_receipt', array( false ) );

		?>
		<form method="post" action="options.php" class="edd-settings-form">
			<?php settings_fields( 'edd_settings' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php echo $this->settings_html( $this->extract_settings_fields( $sections ) ); ?>
					<?php echo $this->settings_html( $this->extract_settings_fields( $sections_purchase_receipt ) ); ?>
					<tr>
						<th scope="row">
							<label for="edd_settings[purchase_receipt]"><?php echo esc_html_e( 'Purchase Receipt Email', 'easy-digital-downloads' ); ?></label>
						</th>
					</tr>

					<tr>
						<td colspan="2">
							<?php edd_email_tags_inserter_thickbox_content(); ?>
							<div id="edd-onboarding__insert-marker-button" style="display: none;">
								<a href="#TB_inline?width=640&inlineId=edd-insert-email-tag" class="edd-email-tags-inserter thickbox button edd-thickbox" style="padding-left: 0.4em;">
									<span class="wp-media-buttons-icon dashicons dashicons-editor-code"></span>
									<?php esc_html_e( 'Insert Marker', 'easy-digital-downloads' ); ?>
								</a>
							</div>
							<textarea name="edd_settings[purchase_receipt]" id="edd_settings_purchase_receipt" rows="12" style="width: 100%;"><?php echo wp_kses_post( wpautop( $order_receipt->get_raw_body_content() ) ); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="edd_tab_override" value="emails" />
			<input type="hidden" name="edd_section_override" value="purchase_receipts" />
		</form>
		<?php
	}
}
