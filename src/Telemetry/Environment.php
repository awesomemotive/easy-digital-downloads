<?php
/**
 * Gets the environment to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Telemetry;

defined( 'ABSPATH' ) || exit;

/**
 * Class Environment
 *
 * @since 3.1.1
 * @package EDD\Telemetry
 */
class Environment {
	use Traits\Anonymize;

	/**
	 * Gets the array of environment data.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function get() {
		$data   = array(
			'php_version'    => phpversion(),
			'wp_version'     => $this->get_wp_version(),
			'edd_version'    => EDD_VERSION,
			'edd_pro'        => (int) (bool) edd_is_pro(),
			'locale'         => get_locale(),
			'active_theme'   => $this->get_active_theme(),
			'multisite'      => (int) (bool) is_multisite(),
			'is_ssl'         => (int) (bool) is_ssl(),
			'stripe_connect' => (int) (bool) edd_get_option( 'stripe_connect_account_id' ),
			'rest_enabled'   => (int) (bool) $this->is_rest_enabled(),
		);
		$server = $this->parse_server();

		return array_merge( $data, $server );
	}

	/**
	 * Adds the server data to the array of data.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function parse_server() {
		$server = ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown' );
		$server = explode( '/', $server );

		$data = array(
			'server' => $server[0],
		);
		if ( isset( $server[1] ) ) {
			$data['server_version'] = $server[1];
		}

		return $data;
	}

	/**
	 * Gets the WordPress version.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_wp_version() {
		$version = get_bloginfo( 'version' );
		$version = explode( '-', $version );

		return reset( $version );
	}

	/**
	 * Gets the active theme name.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_active_theme() {
		$active_theme = wp_get_theme();

		return $this->anonymize_site_name( $active_theme->name );
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function is_rest_enabled() {
		$checker = new \EDD\Utils\RESTChecker( 'wp/v2/edd-downloads' );

		return $checker->is_enabled();
	}
}
