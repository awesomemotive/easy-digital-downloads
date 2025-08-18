<?php
/**
 * Prompt the user for their Square Personal Access Token, so we can register webhooks.
 *
 * @package EDD\Gateways\Square\Admin\Settings\Webhooks
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.4.0
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Promos\Notices\Notice;
use EDD\HTML\Text;

/**
 * Webhooks Personal Access Token Modal.
 *
 * @since 3.4.0
 */
class SquareWebhooksModal extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-download_page_edd-settings';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Capability required to dismiss the notice.
	 */
	const CAPABILITY = 'manage_shop_settings';

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @since 3.4.0
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Gets the notice content for AJAX requests.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_ajax_content() {
		if ( ! $this->_should_display() ) {
			return '';
		}

		ob_start();
		$action         = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_SPECIAL_CHARS );
		$button_classes = array(
			'button',
			'button-primary',
		);
		?>
		<div class="edd-promo-notice__content">
			<p>
				<h2>
					<?php esc_html_e( 'Personal Access Token Required', 'easy-digital-downloads' ); ?>
				</h2>
				<?php
					printf(
						/* translators: 1: Square Developer Dashboard opening anchor tag, 2: closing anchor tag */
						esc_html__( 'To receive events, create a webhook route by providing your Personal Access Token, which you can find after registering an app on the %1$sSquare Developer Dashboard%2$s. You can also set it up manually in the Advanced section.', 'easy-digital-downloads' ),
						'<a href="https://developer.squareup.com/apps" target="_blank">',
						'</a>'
					);
				?>
			</p>

			<p>
				<?php
					$token_input = new Text(
						array(
							'id' => 'edd-square-personal-access-token',
							'name' => 'token',
							'label' => '',
							'value' => '',
							'placeholder' => __( 'Enter your Personal Access Token', 'easy-digital-downloads' ),
							'class' => 'regular-text',
						)
					);
					$token_input->output();
				?>
			</p>
		</div>
		<div class="edd-promo-notice__actions">
			<button
				type="button"
				id="edd-square-register-webhooks"
				class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_square_register_webhooks' ) ); ?>"
			>
				<?php
				echo 'connect' === $action ? esc_html_e( 'Connect', 'easy-digital-downloads' ) : esc_html_e( 'Refresh', 'easy-digital-downloads' );
				?>
			</button>
			<button class="button button-secondary edd-promo-notice-dismiss">
				<?php esc_html_e( 'Cancel', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<div class="edd-promo-notice__info">
			<p id="edd-square-webhooks-spinner" class="edd-hidden"><span class="spinner is-active"></span></p>
			<p id="edd-square-webhooks-message" class="info edd-hidden"></p>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the AJAX content.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Displays the notice content.
	 * This is intentionally left blank as the content is rendered in the AJAX content.
	 *
	 * @return void
	 */
	protected function _display() {}
}
