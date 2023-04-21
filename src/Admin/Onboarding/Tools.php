<?php
/**
 * Adds a tool section to allow a user to revisit the onboarding wizard.
 */
namespace EDD\Admin\Onboarding;

class Tools implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! current_user_can( 'manage_options' ) || ! get_option( 'edd_onboarding_completed' ) ) {
			return array();
		}

		return array(
			'edd_tools_tab_general' => 'restart_onboarding',
		);
	}

	/**
	 * Adds a "tool" to allow users to restart the onboarding wizard.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function restart_onboarding() {
		?>
		<div class="postbox">
			<h3><?php esc_html_e( 'Restart the Setup Wizard', 'easy-digital-downloads' ); ?></h3>
			<div class="inside edd-onboarding">
				<p><?php esc_html_e( 'If you would like to revisit our setup wizard, you can at any time.', 'easy-digital-downloads' ); ?></p>
				<a class="button button-secondary" href="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-onboarding-wizard' ) ) ); ?>"><?php esc_html_e( 'Restart the Setup Wizard', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>
		<?php
	}
}
