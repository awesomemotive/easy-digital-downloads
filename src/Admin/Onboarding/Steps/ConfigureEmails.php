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

use EDD\Emails\Templates\OrderReceipt;
use EDD\Admin\Settings\Tabs\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Configure Emails
 */
class ConfigureEmails extends Step {

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	public function step_html() {
		// Get an empty instance of the order receipt email, so we can use it to get the raw body content.
		$order_receipt   = new OrderReceipt();
		$emails_settings = new Emails();
		$settings        = $emails_settings->get()['main'];

		?>
		<form method="post" class="edd-settings-form edd-settings-form__email">
			<div class="edd-form-group">
				<label for="email_logo">
					<?php esc_html_e( 'Logo', 'easy-digital-downloads' ); ?>
				</label>
				<div class="edd-form-group__control">
					<?php
					$uploader = new \EDD\HTML\Upload(
						array(
							'value' => edd_get_option( 'email_logo', '' ),
							'desc'  => $settings['email_logo']['desc'],
							'id'    => 'email_logo',
							'name'  => 'email_logo',
						)
					);
					$uploader->output();
					?>
				</div>
			</div>

			<div class="edd-form-group">
				<th>
					<label for="from_name">
						<?php esc_html_e( 'From Name', 'easy-digital-downloads' ); ?>
					</label>
				</th>
				<div class="edd-form-group__control">
					<?php
					$text = new \EDD\HTML\Text(
						array(
							'value' => edd_get_option( 'from_name', $settings['from_name']['std'] ),
							'id'    => 'from_name',
							'name'  => 'from_name',
						)
					);
					$text->output();
					?>
					<p class="description">
						<?php echo wp_kses_post( $settings['from_name']['desc'] ); ?>
					</p>
				</div>
			</div>

			<div class="edd-form-group">
				<label for="from_email">
					<?php esc_html_e( 'From Email', 'easy-digital-downloads' ); ?>
				</label>
				<div class="edd-form-group__control">
					<?php
					$text = new \EDD\HTML\Text(
						array(
							'value' => edd_get_option( 'from_email', $settings['from_email']['std'] ),
							'id'    => 'from_email',
							'name'  => 'from_email',
						)
					);
					$text->output();
					?>
					<p class="description">
						<?php echo wp_kses_post( $settings['from_email']['desc'] ); ?>
					</p>
				</div>
			</div>

			<div class="edd-form-group edd-form-group__wide">
				<label for="edd_settings_purchase_receipt">
					<?php esc_html_e( 'Message', 'easy-digital-downloads' ); ?>
				</label>
				<div class="edd-form-group__control">
					<?php
					edd_load_email_tags();
					edd_email_tags_inserter_enqueue_scripts( $order_receipt->context, $order_receipt->recipient );
					edd_email_tags_inserter_thickbox_content( $order_receipt->context, $order_receipt->recipient );
					edd_email_tags_inserter_media_button( true );
					// Hidden template: cloned into .wp-media-buttons after wp.editor.initialize() when step is loaded via AJAX (client-side init only adds Add Media).
					?>
					<div id="edd-onboarding__insert-tag-button-template" class="edd-hidden" aria-hidden="true">
						<button type="button" class="button edd-email-tags-inserter" data-dialog-id="edd-insert-email-tag-dialog" aria-label="<?php esc_attr_e( 'Insert email tag', 'easy-digital-downloads' ); ?>">
							<span class="wp-media-buttons-icon dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'Insert Tag', 'easy-digital-downloads' ); ?>
						</button>
					</div>
					<?php
					wp_editor(
						wpautop( $order_receipt->content ),
						'edd_settings_purchase_receipt',
						array(
							'textarea_name' => 'content',
							'tinymce'       => array(
								'wpautop'  => true,
								'plugins'  => 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
								'toolbar1' => 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
								'toolbar2' => 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
							),
							'quicktags'     => true,
							'media_buttons' => true,
						)
					);
					?>
				</div>
			</div>
			<input type="hidden" name="edd_tab_override" value="emails" />
			<input type="hidden" name="edd_section_override" value="purchase_receipts" />
		</form>
		<?php
	}
}
