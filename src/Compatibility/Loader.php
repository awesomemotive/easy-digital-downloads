<?php
/**
 * Loader for compatibility classes.
 *
 * @package     EDD
 * @subpackage  Compatibility\Load
 * @since       3.2.8
 */

namespace EDD\Compatibility;

use EDD\EventManagement\SubscriberInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility loader
 *
 * @since 3.2.8
 */
class Loader implements SubscriberInterface {

	/**
	 * Loaded compatibility classes
	 *
	 * @var array
	 */
	private static $loaded = array();

	/**
	 * Load the compatibility classes
	 *
	 * @since 3.2.8
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'plugins_loaded' => array( 'load_plugin_compatibility', 9999 ),
		);
	}

	/**
	 * Load the compatibility classes
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	public static function load_plugin_compatibility() {
		$plugin_compatibility_classes = array(
			Plugins\Wordfence::class,
		);

		foreach ( $plugin_compatibility_classes as $plugin_compatibility_class ) {
			if (
				class_exists( $plugin_compatibility_class ) &&
				is_subclass_of( $plugin_compatibility_class, 'EDD\Compatibility\Plugins\Plugin' )
			) {
				// Instantiate the class which checks if it is active, registers hooks/filters if it is.
				$plugin_class     = new $plugin_compatibility_class();
				$plugin_is_active = $plugin_class->is_active();

				// Store the result of the check.
				self::$loaded[ $plugin_compatibility_class ] = $plugin_is_active;
			}
		}
	}

	/**
	 * Get the loaded compatibility classes
	 *
	 * @since 3.2.8
	 *
	 * @return array
	 */
	public static function get_loaded() {
		return self::$loaded;
	}
}
