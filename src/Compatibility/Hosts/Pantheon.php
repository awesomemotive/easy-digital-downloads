<?php
/**
 * Handles compatibility with Pantheon
 *
 * @package     EDD\Compatibility\Hosts
 * @since       3.3.4
 */

namespace EDD\Compatibility\Hosts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Pantheon compatibility
 *
 * @since 3.3.4
 */
class Pantheon extends Host {

	/**
	 * The host name
	 *
	 * @since 3.3.4
	 *
	 * @var string
	 */
	public $host_name = 'pantheon';

	/**
	 * Check if the host is active
	 *
	 * @since 3.3.4
	 * @see   https://docs.pantheon.io/guides/environment-configuration/read-environment-config
	 *
	 * @return bool
	 */
	public function is_host_detected(): bool {
		return defined( 'PANTHEON_ENVIRONMENT' ) && ! empty( 'PANTHEON_ENVIRONMENT' );
	}

	/**
	 * Return the events for Pantheon compatibility
	 *
	 * @since 3.3.4
	 *
	 * @return void
	 */
	protected function register_events() {

		/**
		 * Add a cookie prefix to the EDD session cookie to bypass cache on Pantheon.
		 *
		 * @since 3.3.4
		 */
		add_filter(
			'edd_session_cookie_prefix',
			function ( $cookie_prefix ) {
				return 'wp-';
			}
		);
	}
}
