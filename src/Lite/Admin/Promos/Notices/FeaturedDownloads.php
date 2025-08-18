<?php
/**
 * Featured Downloads Upgrade Notice
 *
 * @package     EDD\Lite\Admin\Promos\Notices
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Lite\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Promos\Notices\Notice;

/**
 * Featured Downloads Upgrade Notice class.
 *
 * @since 3.5.0
 */
class FeaturedDownloads extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-post.php';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Capability required to dismiss the notice.
	 */
	const CAPABILITY = 'edit_posts';

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @since 3.5.1
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Renders the dismiss button for the notice.
	 * This is intentionally left blank as the dismiss button is rendered in the AJAX content.
	 *
	 * @since 3.5.1
	 * @return void
	 */
	public function dismiss_button() {}

	/**
	 * Displays the notice content.
	 *
	 * @since 3.5.1
	 */
	public function get_ajax_content() {
		ob_start();
		$upgrade_url     = edd_link_helper(
			'https://easydigitaldownloads.com/lite-upgrade/',
			array(
				'utm_medium'  => 'settings',
				'utm_content' => 'featured-downloads-overlay',
			)
		);
		?>
		<div class="edd-promo-notice__image">
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/featured-downloads-styling.png' ); ?>" alt="" />
		</div>
		<div class="edd-promo-notice__content">
			<h2>
				<?php esc_html_e( 'Turn Your Best Products into Bestsellers', 'easy-digital-downloads' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Get more clicks and sales by making your top downloads impossible to miss. With EDD Pro, you can style featured products with custom borders, badges, and colors—grabbing attention and boosting conversions instantly.', 'easy-digital-downloads' ); ?>
			</p>
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
		return ob_get_clean();
	}

	/**
	 * Displays the notice content.
	 * This is intentionally left blank as the content is rendered in the AJAX content.
	 *
	 * @return void
	 */
	protected function _display() {}
}
