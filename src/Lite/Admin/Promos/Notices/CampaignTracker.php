<?php
/**
 * Campaign Tracker Upgrade Notice
 *
 * @package     EDD\Lite\Admin\Promos\Notices
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.3
 */

namespace EDD\Lite\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Promos\Notices\Notice;

/**
 * Campaign Tracker Upgrade Notice class.
 *
 * @since 3.6.3
 */
class CampaignTracker extends Notice {

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
	 * @since 3.6.3
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the content.
	 *
	 * @since 3.6.3
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Gets the notice ID.
	 *
	 * @since 3.6.3
	 * @return string
	 */
	public function get_id() {
		return 'campaigntracker';
	}

	/**
	 * Displays the notice content.
	 *
	 * @since 3.6.3
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
				'utm_content' => 'campaign-tracker',
			)
		);
		?>
		<div class="edd-promo-notice__image">
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/campaign-tracker.png' ); ?>" alt="" />
		</div>

		<div class="edd-promo-notice__content">
			<h2>
				<?php esc_html_e( 'Track Your Marketing Campaigns', 'easy-digital-downloads' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'See which campaigns, sources, and mediums drive the most sales. Pro unlocks automatic UTM parameter tracking on every order—plus detailed reports to measure ROI.', 'easy-digital-downloads' ); ?>
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
	 * @since 3.6.3
	 * @return bool
	 */
	protected function _should_display(): bool {
		return 'marketing' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS )
			&& empty( filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS ) );
	}
}
