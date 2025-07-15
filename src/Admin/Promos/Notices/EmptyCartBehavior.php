<?php
/**
 * Empty Cart Behavior Upgrade Notice
 *
 * @package     EDD\Admin\Promos\Notices
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Empty Cart Behavior Upgrade Notice class.
 *
 * @since 3.5.0
 */
class EmptyCartBehavior extends Notice {

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
	 * This is intentionally left blank as the content is rendered in the AJAX content.
	 *
	 * @return void
	 */
	protected function _display() {
		if ( ! $this->_should_display() ) {
			return '';
		}

		ob_start();
		$upgrade_url = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'settings',
				'utm_content' => 'empty-cart-behavior-overlay',
			)
		);
		$checkmark_image = EDD_PLUGIN_URL . 'assets/images/icons/edd-blue-checkmark.svg';
		?>
		<div class="edd-promo-notice__content">
			<h2>
				<?php esc_html_e( 'Unlock More Empty Cart Options', 'easy-digital-downloads' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Take control of your customer journey with advanced empty cart behavior options:', 'easy-digital-downloads' ); ?>
			</p>
			<ul class="edd-promo-notice__features">
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Redirect to a page.', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Redirect to a custom URL.', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Capture missed leads.', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Reduce cart abandonment.', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'Improve your store\'s conversion rate.', 'easy-digital-downloads' ); ?>
				</li>
				<li>
					<img src="<?php echo esc_url( $checkmark_image ); ?>" alt="" />
					<?php esc_html_e( 'And more!', 'easy-digital-downloads' ); ?>
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

		echo ob_get_clean();
	}

	/**
	 * Gets the notice ID.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	public function get_id() {
		return 'emptycartbehavior';
	}

	/**
	 * Determines if the notice should be displayed.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! edd_is_admin_page( 'settings', 'gateways' ) ) {
			return false;
		}

		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );

		if ( 'checkout' !== $section ) {
			return false;
		}

		return true;
	}
}
