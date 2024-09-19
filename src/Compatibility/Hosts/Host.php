<?php
/**
 * Base class for a hosting compatibility class
 *
 * @package     EDD\Compatibility\Hosts
 * @since       3.3.4
 */

namespace EDD\Compatibility\Hosts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Host compatibility
 *
 * @since 3.3.4
 */
abstract class Host {
	/**
	 * Determine if we've detected this specific host.
	 *
	 * To avoid duplicate code, the constructor is not abstract, so we can handle the initial check for the host
	 * and then call the abstract methods to check if we've found a specific host.
	 *
	 * @since 3.3.4
	 *
	 * @return void
	 */
	final public function __construct() {
		if ( ! $this->is_host_detected() ) {
			return;
		}

		// Now that we've confirmed the plugin is active, let's register the events.
		$this->register_events();
	}

	/**
	 * Check if the we've found a specific host.
	 *
	 * If the host is not found, the compatibility class will not register its events.
	 * For each host compatibility class, this method should be overridden to check if the host is detected
	 * using the best method for the host, usually by class_exists, function_exists, or a specific constant.
	 *
	 * @since 3.3.4
	 *
	 * @return bool
	 */
	abstract public function is_host_detected(): bool;

	/**
	 * Register the events for the host compatibility.
	 *
	 * This should typically hold any add_action or add_filter calls for the host compatibility.
	 *
	 * @since 3.3.4
	 *
	 * @return void
	 */
	abstract protected function register_events();
}
