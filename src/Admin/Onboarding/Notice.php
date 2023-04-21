<?php
/**
 * Show a dismissal notice when skipping the onboarding wizard.
 */
namespace EDD\Admin\Onboarding;

class Notice extends \EDD\Admin\Promos\Notices\Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'download_page_edd-onboarding-wizard';

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
	 * @todo Chris set up words
	 * @since 3.1.1
	 * @return void
	 */
	protected function _display() {
		?>
		<h2><?php esc_html_e( 'Wait! We still haven\'t finished the setup.', 'easy-digital-downloads' ); ?></h2>
		<p><?php esc_html_e( 'Our Quick Setup Wizard can help you get you ready to sell your first product in minutes. If you choose to exit now, you will need to manually configure key aspects of your store. You can always restart this wizard by going to Downloads > Tools, and clicking on the \'Restart Setup Wizard\' button.', 'easy-digital-downloads' ); ?></p>
		<div class="edd-onboarding__actions">
			<button class="button button-primary edd-promo-notice-dismiss"><?php esc_html_e( 'Let\'s finish now!', 'easy-digital-downloads' ); ?></button>
			<button class="button button-secondary edd-promo-notice-dismiss edd-onboarding__dismiss"><?php esc_html_e( 'No thanks, I\'ll do it all myself.', 'easy-digital-downloads' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Registers a custom notice ID so it's not created from the class name.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_id() {
		return 'onboarding-dismiss';
	}

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * Setting to 1 so that it's always available to the wizard.
	 *
	 * @since 3.1.1
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	protected function _should_display() {
		return ! get_option( 'edd_onboarding_completed' );
	}
}
