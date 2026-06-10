<?php
/**
 * ActiveLayer
 *
 * Manages automatic installation/activation for ActiveLayer anti-spam.
 *
 * @package     EDD
 * @subpackage  ActiveLayer
 * @copyright   Copyright (c) 2026, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.10
 */

namespace EDD\Admin\Settings;

use EDD\EventManagement\SubscriberInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class ActiveLayer
 *
 * @since 3.6.10
 */
class ActiveLayer implements SubscriberInterface {

	/**
	 * Array of configuration data for ActiveLayer.
	 *
	 * @var array
	 */
	private $config = array(
		'plugin'       => 'activelayer-anti-spam-spam-protection-for-forms-comments/activelayer-anti-spam-spam-protection-for-forms-comments.php',
		'wporg_url'    => 'https://wordpress.org/plugins/activelayer-anti-spam-spam-protection-for-forms-comments/',
		'download_url' => 'https://downloads.wordpress.org/plugin/activelayer-anti-spam-spam-protection-for-forms-comments.zip',
		'settings'     => 'admin.php?page=activelayer-settings',
	);

	/**
	 * The Extension Manager.
	 *
	 * @var \EDD\Admin\Extensions\Extension_Manager
	 */
	private $manager;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->manager = new \EDD\Admin\Extensions\Extension_Manager();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.6.10
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_misc' => 'register_setting',
			'edd_activelayer'   => 'settings_field',
		);
	}

	/**
	 * Append the ActiveLayer installer to the bottom of the Spam Protection (captcha) section.
	 *
	 * Runs as a callback on the `edd_settings_misc` filter, which the Misc tab applies after
	 * building the captcha section, so appending here lands at the bottom of that section.
	 *
	 * @param array $settings The settings array.
	 * @return array
	 */
	public function register_setting( $settings ) {
		if ( ! edd_is_admin_page( 'misc' ) ) {
			return $settings;
		}

		$settings['captcha']['activelayer'] = array(
			'id'   => 'activelayer',
			'name' => __( 'Smart Spam Protection', 'easy-digital-downloads' ),
			'desc' => '',
			'type' => 'hook',
		);

		return $settings;
	}

	/**
	 * Output the settings field (installation helper).
	 *
	 * @param array $args The setting field arguments.
	 * @return void
	 */
	public function settings_field( $args ) {
		$this->manager->enqueue();
		?>
		<div class="edd-extension-manager__body">
			<p class="edd-extension-manager__description">
				<?php esc_html_e( 'AI-powered spam protection. No CAPTCHAs, no friction for real visitors, higher conversions. Free tier available, no credit-card required.', 'easy-digital-downloads' ); ?>
			</p>

			<div class="edd-extension-manager__group edd-extension-manager__actions">
				<div class="edd-extension-manager__step">
					<?php $this->manager->button( $this->get_button_parameters() ); ?>
				</div>

				<?php if ( ! $this->is_activelayer_activated() ) : ?>
				<div class="edd-extension-manager__step" style="display:none;">
					<?php $this->manager->link( $this->get_link_parameters() ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the button parameters for the three states (install / activate / configure).
	 *
	 * @since 3.6.10
	 * @return array
	 */
	private function get_button_parameters() {
		$button = array();

		if ( ! $this->manager->is_plugin_installed( $this->config['plugin'] ) ) {
			$button['plugin']      = $this->config['download_url'];
			$button['action']      = 'install';
			$button['button_text'] = __( 'Install & Activate ActiveLayer', 'easy-digital-downloads' );
		} elseif ( ! $this->is_activelayer_activated() ) {
			$button['plugin']      = $this->config['plugin'];
			$button['action']      = 'activate';
			$button['button_text'] = __( 'Activate ActiveLayer', 'easy-digital-downloads' );
		} else {
			$button = $this->get_link_parameters();
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure ActiveLayer.
	 *
	 * @since 3.6.10
	 * @return array
	 */
	private function get_link_parameters() {
		return array(
			'button_text' => __( 'Configure ActiveLayer', 'easy-digital-downloads' ),
			'href'        => admin_url( $this->config['settings'] ),
		);
	}

	/**
	 * Whether the ActiveLayer plugin is active.
	 *
	 * @since 3.6.10
	 * @return bool
	 */
	protected function is_activelayer_activated() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $this->config['plugin'] );
	}
}
