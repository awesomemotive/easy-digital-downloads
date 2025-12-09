<?php
/**
 * Preview Recommendations Upgrade Notice
 *
 * @package     EDD\Lite\Admin\Promos\Notices
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\Lite\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Promos\Notices\Notice;

/**
 * Preview Recommendations Upgrade Notice class.
 *
 * @since 3.6.2
 */
class PreviewRecommendations extends Notice {

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
	 * @since 3.6.2
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the content.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Gets the notice ID.
	 *
	 * @since 3.6.2
	 * @return string
	 */
	public function get_id() {
		return 'previewrecommendations';
	}

	/**
	 * Displays the notice content.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	protected function _display() {
		if ( ! $this->_should_display() ) {
			return;
		}

		ob_start();
		$upgrade_url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'settings',
				'utm_content' => 'preview-recommendations',
			)
		);
		?>
		<div class="edd-promo-notice__image">
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/product-recommendations.png' ); ?>" alt="" />
		</div>

		<div class="edd-promo-notice__content">
			<h2>
				<?php esc_html_e( 'Boost Your Average Order Value', 'easy-digital-downloads' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Customers who see relevant recommendations in their cart spend more. Pro unlocks automatic cross-sells and upsells in your cart preview—no setup required.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<div class="edd-promo-notice__actions">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank">
				<?php esc_html_e( 'Unlock This', 'easy-digital-downloads' ); ?>
			</a>
			<button class="button button-secondary edd-promo-notice-dismiss">
				<?php esc_html_e( 'Maybe Later', 'easy-digital-downloads' ); ?>
			</button>
		</div>
		<?php

		echo ob_get_clean();
	}

	/**
	 * Determines if the notice should be displayed.
	 *
	 * @since 3.6.2
	 * @return bool
	 */
	protected function _should_display(): bool {
		return 'gateways' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS ) && 'cart' === filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );
	}
}
