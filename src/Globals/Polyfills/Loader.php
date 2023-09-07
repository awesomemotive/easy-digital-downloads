<?php
/**
 * Loads the polyfills for WordPress functions which may not be available in older versions.
 *
 * These need to be done via a hook, to ensure WordPress is loaded but before the pluggable functions are defined.
 *
 * @since 3.2.0
 *
 * @package     EDD
 * @subpackage  Globals\Polyfills
 */

namespace EDD\Globals\Polyfills;

use EDD\EventManagement\SubscriberInterface;

/**
 * Polyfills for PHP and WordPress functions which may not be available in older versions.
 */
class Loader implements SubscriberInterface {

	/**
	 * Get the events that this subscriber is subscribed to.
	 *
	 * @since 3.2.0
	 */
	public static function get_subscribed_events() {
		return array(
			'plugins_loaded' => 'include_polyfills', // We use plugins_loaded as this is run before pluggable functions are loaded.
		);
	}

	/**
	 * Include the WordPress polyfills file.
	 *
	 * @since 3.2.0
	 */
	public function include_polyfills() {
		require_once __DIR__ . '/WordPress.php';
	}
}
