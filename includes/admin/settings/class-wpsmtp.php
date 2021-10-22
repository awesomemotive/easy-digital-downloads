<?php

namespace EDD\Admin;

class WP_SMTP {

	private $config = array(
		'lite_plugin'       => 'wp-mail-smtp/wp_mail_smtp.php',
		'lite_wporg_url'    => 'https://wordpress.org/plugins/wp-mail-smtp/',
		'lite_download_url' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
		'pro_plugin'        => 'wp-mail-smtp-pro/wp_mail_smtp.php',
		'smtp_settings'     => 'admin.php?page=wp-mail-smtp',
		'smtp_wizard'       => 'admin.php?page=wp-mail-smtp-setup-wizard',
	);

	private $data = array();

	public function __construct() {
		add_filter( 'edd_settings_emails', array( $this, 'register_setting' ) );
		add_action( 'edd_wpsmtp', array( $this, 'settings_field' ) );
	}

	public function register_setting( $settings ) {
		$data = $this->get_data();
		if ( ! empty( $data['plugin_activated'] ) ) {
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

	public function settings_field( $args ) {
		echo '<p>';
		esc_html_e( 'WP Mail SMTP allows you to easily set up WordPress to use a trusted provider to reliably send emails, including sales notifications.', 'easy-digital-downloads' );
		echo '</p>';

		if ( ! $this->data['plugin_installed'] && ! $this->data['pro_plugin_installed'] ) {
			?>
			<button class="button button-primary" data-plugin="https://downloads.wordpress.org/plugin/wp-mail-smtp.zip" data-action="install"><?php esc_html_e( 'Install WP Mail SMTP', 'easy-digital-downloads' ); ?></button>
			<?php
		}
	}

	private function get_data() {
		$this->data                         = array(
			'all_plugins'      => \get_plugins(),
			'plugin_activated' => false,
			'plugin_setup'     => false,
		);
		$this->data['plugin_installed']     = array_key_exists( $this->config['lite_plugin'], $this->data['all_plugins'] );
		$this->data['pro_plugin_installed'] = array_key_exists( $this->config['pro_plugin'], $this->data['all_plugins'] );

		return $this->data;
	}
}

new WP_SMTP();
