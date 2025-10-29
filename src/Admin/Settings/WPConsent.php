<?php
/**
 * WP Consent
 *
 * Manages automatic installation/activation for WP Consent.
 *
 * @package     EDD
 * @subpackage  WPConsent
 * @copyright   Copyright (c) 2025, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Admin\Settings;

use EDD\EventManagement\SubscriberInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class WPConsent
 *
 * @since 3.6.0
 */
class WPConsent implements SubscriberInterface {

	/**
	 * Array of configuration data for WP Consent.
	 *
	 * @var array
	 */
	private $config = array(
		'lite_plugin'       => 'wpconsent-cookies-banner-privacy-suite/wpconsent.php',
		'lite_wporg_url'    => 'https://wordpress.org/plugins/wpconsent-cookies-banner-privacy-suite/',
		'lite_download_url' => 'https://downloads.wordpress.org/plugin/wpconsent-cookies-banner-privacy-suite.zip',
		'pro_plugin'        => 'wpconsent-premium/wpconsent-premium.php',
		'consent_settings'  => 'admin.php?page=wpconsent',
	);

	/**
	 * The Extension Manager
	 *
	 * @var \EDD\Admin\Extensions\Extension_Manager
	 */
	private $manager;

	public function __construct() {
		$this->manager = new \EDD\Admin\Extensions\Extension_Manager();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_settings_privacy' => 'register_setting',
			'edd_wpconsent'        => 'settings_field',
		);
	}

	/**
	 * Register the setting to show the WP Consent installer if it isn't active.
	 *
	 * @param array $settings The settings array.
	 * @return array
	 */
	public function register_setting( $settings ) {
		if ( ! edd_is_admin_page( 'privacy' ) ) {
			return $settings;
		}

		$settings['main']['wpconsent'] = array(
			'id'   => 'wpconsent',
			'name' => __( 'Cookie Management', 'easy-digital-downloads' ),
			'desc' => '',
			'type' => 'hook',
		);

		return $settings;
	}

	/**
	 * Output the settings field (installation helper).
	 *
	 * @param array $args
	 * @return void
	 */
	public function settings_field( $args ) {
		$this->manager->enqueue();
		?>
		<div class="edd-extension-manager__body">
			<p class="edd-extension-manager__description">
				<?php esc_html_e( 'WPConsent is the easiest way to add a GDPR / CCPA cookie consent banner to your WordPress website and EDD store.', 'easy-digital-downloads' ); ?>
			</p>

			<div class="edd-extension-manager__group edd-extension-manager__actions">
				<div class="edd-extension-manager__step">
					<?php $this->manager->button( $this->get_button_parameters() ); ?>
				</div>

				<?php if ( ! $this->is_consent_activated() ) : ?>
				<div class="edd-extension-manager__step" style="display:none;">
					<?php $this->manager->link( $this->get_link_parameters() ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the button parameters.
	 *
	 * @return array
	 */
	private function get_button_parameters() {
		$button = array();
		// If neither the lite nor pro plugin is installed, the button will prompt to install and activate the lite plugin.
		if ( ! $this->manager->is_plugin_installed( $this->config['lite_plugin'] ) && ! $this->manager->is_plugin_installed( $this->config['pro_plugin'] ) ) {
			$button['plugin']      = $this->config['lite_download_url'];
			$button['action']      = 'install';
			$button['button_text'] = __( 'Install & Activate WPConsent', 'easy-digital-downloads' );
		} elseif ( ! $this->is_consent_activated() ) {
			// If one of the plugins is installed, but not activated, the button will prompt to activate it.
			// Prefer the pro version if both are installed.
			if ( $this->manager->is_plugin_installed( $this->config['pro_plugin'] ) ) {
				$button['plugin'] = $this->config['pro_plugin'];
			} else {
				$button['plugin'] = $this->config['lite_plugin'];
			}
			$button['action']      = 'activate';
			$button['button_text'] = __( 'Activate WPConsent', 'easy-digital-downloads' );
		} else {
			// If the plugin is active, the button will send them to the settings.
			$button = $this->get_link_parameters();
		}

		return $button;
	}

	/**
	 * Gets the array of parameters for the link to configure WP Consent.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	private function get_link_parameters() {
		return array(
			'button_text' => __( 'Configure WPConsent', 'easy-digital-downloads' ),
			'href'        => admin_url( $this->config['consent_settings'] ),
		);
	}

	/**
	 * Whether WP Consent plugin is active or not.
	 *
	 * @since 3.6.0
	 *
	 * @return bool True if WP Consent plugin is active.
	 */
	protected function is_consent_activated() {
		return function_exists( 'wpconsent' ) && ( is_plugin_active( $this->config['lite_plugin'] ) || is_plugin_active( $this->config['pro_plugin'] ) );
	}
}
