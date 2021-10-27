<?php

namespace EDD\Admin\Settings;

class WP_SMTP {

	/**
	 * Array of configuration data for WP Mail SMPT.
	 *
	 * @var array
	 */
	private $config = array(
		'lite_plugin'       => 'wp-mail-smtp/wp_mail_smtp.php',
		'lite_wporg_url'    => 'https://wordpress.org/plugins/wp-mail-smtp/',
		'lite_download_url' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
		'pro_plugin'        => 'wp-mail-smtp-pro/wp_mail_smtp.php',
		'smtp_settings'     => 'admin.php?page=wp-mail-smtp',
		'smtp_wizard'       => 'admin.php?page=wp-mail-smtp-setup-wizard',
	);

	/**
	 * Array of information about the plugins installed/active on the site.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * The Extension Manager
	 *
	 * @var \EDD\Admin\Extension_Manager
	 */
	private $manager;

	public function __construct() {
		add_filter( 'edd_settings_emails', array( $this, 'register_setting' ) );
		add_action( 'edd_wpsmtp', array( $this, 'settings_field' ) );

		$this->manager = new \EDD\Admin\Extension_Manager();
	}

	/**
	 * Register the setting to show the WP SMTP installer if it isn't active.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function register_setting( $settings ) {
		if ( $this->is_smtp_configured() ) {
			return $settings;
		}
		$settings['main']['wpsmtp'] = array(
			'id'   => 'wpsmtp',
			'name' => __( 'Improve Email Deliverability', 'easy-digital-downloads' ),
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
		echo '<p>';
		esc_html_e( 'WP Mail SMTP allows you to easily set up WordPress to use a trusted provider to reliably send emails, including sales notifications.', 'easy-digital-downloads' );
		echo '</p>';

		$this->manager->button( $this->get_button_parameters() );
	}

	/**
	 * Gets the button parameters.
	 *
	 * @return array
	 */
	private function get_button_parameters() {
		$data   = $this->get_data();
		$button = array();
		if ( ! $this->data['plugin_installed'] && ! $this->data['pro_plugin_installed'] ) {
			$button['data-plugin'] = $this->config['lite_download_url'];
			$button['data-action'] = 'install';
			$button['button_text'] = __( 'Install & Activate WP Mail SMTP', 'easy-digital-downloads' );
		} elseif ( ! $this->is_smtp_activated() ) {
			$button['data-plugin'] = $this->config['lite_plugin'];
			$button['data-action'] = 'activate';
			$button['button_text'] = __( 'Activate WP Mail SMTP', 'easy-digital-downloads' );
		} elseif ( ! $this->is_smtp_configured() ) {
			$button['button_text'] = __( 'Configure WP Mail SMTP', 'easy-digital-downloads' );
			$button['href']        = admin_url( $this->config['smtp_wizard'] );
		}

		return $button;
	}

	/**
	 * Get the data needed to manage plugin installation.
	 *
	 * @return array
	 */
	private function get_data() {
		$this->data                         = array(
			'all_plugins'      => \get_plugins(),
			'plugin_activated' => false,
			'plugin_setup'     => false,
		);
		$this->data['plugin_installed']     = array_key_exists( $this->config['lite_plugin'], $this->data['all_plugins'] );
		$this->data['pro_plugin_installed'] = array_key_exists( $this->config['pro_plugin'], $this->data['all_plugins'] );
		$this->data['plugin_activated']     = $this->is_smtp_activated();

		return $this->data;
	}

	/**
	 * Whether WP Mail SMTP plugin configured or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if some mailer is selected and configured properly.
	 */
	protected function is_smtp_configured() {

		if ( ! $this->is_smtp_activated() || ! class_exists( '\\WPMailSMTP\\Options' ) ) {
			return false;
		}

		$phpmailer = $this->get_phpmailer();

		$mailer             = \WPMailSMTP\Options::init()->get( 'mail', 'mailer' );
		$is_mailer_complete = empty( $mailer ) ? false : wp_mail_smtp()->get_providers()->get_mailer( $mailer, $phpmailer )->is_mailer_complete();

		return 'mail' !== $mailer && $is_mailer_complete;
	}

	/**
	 * Whether WP Mail SMTP plugin active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if SMTP plugin is active.
	 */
	protected function is_smtp_activated() {
		return function_exists( 'wp_mail_smtp' ) && ( is_plugin_active( $this->config['lite_plugin'] ) || is_plugin_active( $this->config['pro_plugin'] ) );
	}

	/**
	 * Get $phpmailer instance.
	 *
	 * @since 2.11.x
	 *
	 * @return \PHPMailer|\PHPMailer\PHPMailer\PHPMailer Instance of PHPMailer.
	 */
	protected function get_phpmailer() {

		if ( version_compare( get_bloginfo( 'version' ), '5.5-alpha', '<' ) ) {
			$phpmailer = $this->get_phpmailer_v5();
		} else {
			$phpmailer = $this->get_phpmailer_v6();
		}

		return $phpmailer;
	}

	/**
	 * Get $phpmailer v5 instance.
	 *
	 * @since 2.11.x
	 *
	 * @return \PHPMailer Instance of PHPMailer.
	 */
	private function get_phpmailer_v5() {

		global $phpmailer;

		if ( ! ( $phpmailer instanceof \PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new \PHPMailer( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}

	/**
	 * Get $phpmailer v6 instance.
	 *
	 * @since 2.11.x
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer Instance of PHPMailer.
	 */
	private function get_phpmailer_v6() {

		global $phpmailer;

		if ( ! ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}
}

new WP_SMTP();
