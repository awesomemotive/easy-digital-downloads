<?php
/**
 * Handles compatibility with WPEngine
 *
 * @package     EDD\Compatibility\Hosts
 * @since       3.3.4
 */

namespace EDD\Compatibility\Hosts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WPEngine compatibility
 *
 * @since 3.3.4
 */
class WPEngine extends Host {

	/**
	 * The host name
	 *
	 * @since 3.3.4
	 *
	 * @var string
	 */
	public $host_name = 'wpengine';

	/**
	 * Check if the host is active
	 *
	 * @since 3.3.4
	 * @see   https://wpengine.com/support/determining-wp-engine-environment/
	 *
	 * @return bool
	 */
	public function is_host_detected(): bool {
		return (bool) is_callable( 'is_wpe' ) && is_wpe();
	}

	/**
	 * Return the events for WPEngine compatibility
	 *
	 * @since 3.3.4
	 *
	 * @return void
	 */
	protected function register_events() {
		/**
		 * Add a cookie prefix to the EDD session cookie to bypass cache on WPEngine.
		 *
		 * @since 3.3.4
		 * @see   https://wpengine.com/support/cache/#Default_Cache_Exclusions
		 */
		add_filter(
			'edd_session_cookie_prefix',
			function ( $cookie_prefix ) {
				return 'wordpress_';
			}
		);
	}
}
