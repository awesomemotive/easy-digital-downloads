<?php
/**
 * VAT Handling Upgrade Notice
 *
 * @package     EDD\Admin\Promos\Settings
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * VAT Handling Upgrade Notice class.
 *
 * @since 3.5.0
 */
class VATHandling extends Notice {

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
	 * @since 3.5.0
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the AJAX content.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Displays the notice content.
	 *
	 * @since 3.5.0
	 */
	protected function _display() {
		if ( ! $this->_should_display() ) {
			return;
		}

		$upgrade_url     = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'settings',
				'utm_content' => 'vat-handling-overlay',
			)
		);
		$checkmark_image = EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg';
		?>
		<div class="edd-promo-notice__content">
			<h2>
				<?php esc_html_e( 'Selling to the EU? Pro Makes VAT Simple', 'easy-digital-downloads' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Unlock powerful EU VAT tools with EDD Pro:', 'easy-digital-downloads' ); ?>
			</p>
			<ul class="edd-promo-notice__features">
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Automatic VAT rate updates', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Built in VAT number validation', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'VAT calculation at checkout', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Country-based tax rules', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Detailed VAT reports', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Easy data export for accounting', 'easy-digital-downloads' ); ?>
				</li>
			</ul>
		</div>
		<div class="edd-promo-notice__actions">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank">
				<?php esc_html_e( 'Upgrade to Pro', 'easy-digital-downloads' ); ?>
			</a>
			<button class="button button-secondary edd-promo-notice-dismiss">
				<?php esc_html_e( 'Maybe Later', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Gets the notice ID.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	public function get_id() {
		return 'vathandling';
	}

	/**
	 * Determines if the notice should be displayed.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	protected function _should_display(): bool {
		if ( edd_is_pro() && ! edd_is_inactive_pro() ) {
			return false;
		}

		if ( ! edd_is_admin_page( 'settings', 'taxes' ) ) {
			return false;
		}

		return empty( filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS ) );
	}
}
