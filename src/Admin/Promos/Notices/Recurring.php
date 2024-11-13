<?php
/**
 * Show a notice on the Stripe settings screen.
 *
 * @since 3.3.5
 * @package EDD\Admin\Promos\Notices
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Recurring
 *
 * @since 3.3.5
 * @package EDD\Admin\Promos\Notices
 */
class Recurring extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-download_page_edd-settings';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Displays the notice content.
	 *
	 * @return void
	 */
	protected function _display() {
		$recurring = new \EDD\Admin\Settings\Recurring();
		$recurring->do_single_extension_card( 28530 );
	}

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * @inheritDoc
	 * @since 3.3.5
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		$tab     = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_SPECIAL_CHARS );

		return 'gateways' === $tab && 'edd-stripe' === $section;
	}
}
