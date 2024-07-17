<?php
/**
 * Show a notice on the emails screen.
 */

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Promos\Notices\Notice;

/**
 * Class Reset
 *
 * @since 3.3.0
 * @package EDD\Admin\Promos\Notices
 */
class Reset extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-download_page_edd-emails';

	/**
	 * The priority for the display hook.
	 */
	const DISPLAY_PRIORITY = 5;

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * Sets the notice to not be dismissible.
	 */
	const DISMISSIBLE = false;

	/**
	 * Displays the notice content.
	 *
	 * @return void
	 */
	protected function _display() {
		$email_id = filter_input( INPUT_GET, 'email', FILTER_SANITIZE_SPECIAL_CHARS );
		?>
		<h2><?php esc_html_e( 'Restore Default Email Content?', 'easy-digital-downloads' ); ?></h2>
		<p><?php esc_html_e( 'Restoring the default content will remove any customizations that you have made to the current email content. Do you want to continue?', 'easy-digital-downloads' ); ?></p>
		<div class="edd-promo-notice__actions">
			<button class="button button-secondary edd-promo-notice-dismiss"><?php esc_html_e( 'Cancel', 'easy-digital-downloads' ); ?></button>
			<button class="button button-primary" id="edd-email-reset" data-email="<?php echo esc_attr( $email_id ); ?>"><?php esc_html_e( 'Confirm Restore', 'easy-digital-downloads' ); ?></button>
		</div>
		<?php
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
	 * @since 3.3.0
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}
		if ( empty( $_GET['email'] ) ) {
			return false;
		}

		return true;
	}
}
