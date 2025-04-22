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
	 * @since 3.3.4 Added host compatibility.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'plugins_loaded' => array( 'load_compatibility_layers', 9999 ),
		);
	}

	/**
	 * Load the compatibility layers.
	 *
	 * @since 3.3.8
	 *
	 * @return void
	 */
	public static function load_compatibility_layers() {
		self::load_wp_core_compatibility();
		self::load_plugin_compatibility();
		self::load_host_compatibility();
	}

	/**
	 * Load compatibility classes for core WordPress features.
	 *
	 * @since 3.3.8
	 *
	 * @return void
	 */
	private static function load_wp_core_compatibility() {
		new WP\Performance();
		self::$loaded['wp_core'] = true;
	}

	/**
	 * Load the compatibility classes
	 *
	 * @since 3.2.8
	 *
	 * @return void
	 */
	private static function load_plugin_compatibility() {
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
				self::$loaded['plugins'][ $plugin_class->plugin_name ] = $plugin_is_active;
			}
		}
	}

	/**
	 * Load the host compatibility classes
	 *
	 * @since 3.3.4
	 *
	 * @return void
	 */
	private static function load_host_compatibility() {
		$host_compatibility_classes = array(
			Hosts\Pantheon::class,
			Hosts\WPEngine::class,
		);

		foreach ( $host_compatibility_classes as $host_compatibility_class ) {
			if (
				class_exists( $host_compatibility_class ) &&
				is_subclass_of( $host_compatibility_class, 'EDD\Compatibility\Hosts\Host' )
			) {
				$host_class       = new $host_compatibility_class();
				$host_is_detected = $host_class->is_host_detected();

				// Store the result of the check.
				self::$loaded['hosts'][ $host_class->host_name ] = $host_is_detected;
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
