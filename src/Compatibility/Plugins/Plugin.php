<?php
/**
 * Base class for a plugin compatibility class
 *
 * @package     EDD
 * @subpackage  Compatibility/Plugins
 * @since       3.2.8
 */

namespace EDD\Compatibility\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin compatibility
 *
 * @since 3.2.8
 */
abstract class Plugin {
	/**
	 * Determine if the plugin is active, and if so, register the events.
	 *
	 * To avoid duplicate code, the constructor is not abstract, so we can handle the initial check for the plugin
	 * and then call the abstract methods to check if the plugin is active and register the events.
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	final public function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

		// Now that we've confirmed the plugin is active, let's register the events.
		$this->register_events();
	}

	/**
	 * Check if the plugin is active
	 *
	 * If the plugin is not active, the compatibility class will not register its events.
	 * For each plugin compatibility class, this method should be overridden to check if the plugin is active
	 * using the best method for the plugin, usually by class_exists or function_exists.
	 *
	 * @since 3.2.8
	 *
	 * @return bool
	 */
	abstract public function is_active(): bool;

	/**
	 * Register the events for the plugin compatibility.
	 *
	 * This should typically hold any add_action or add_filter calls for the plugin compatibility.
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	abstract protected function register_events();
}
