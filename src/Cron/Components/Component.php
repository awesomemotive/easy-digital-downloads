<?php
/**
 * Abstract class for adding support for a Cron component.
 *
 * @package EDD
 * @subpackage Cron/Components
 *
 * @since 3.3.0
 */

namespace EDD\Cron\Components;

use EDD\EventManagement\EventManager;
use EDD\EventManagement\SubscriberInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Component
 *
 * @since 3.3.0
 */
abstract class Component implements SubscriberInterface {

	/**
	 * The ID of the component.
	 *
	 * @var string
	 */
	protected static $id;

	/**
	 * Allows overloading of a previously registered component.
	 *
	 * This is useful for if you want to schedule different cron events for a component based on the license level, or other conditions.
	 *
	 * During the component registration process, if a component with the same ID is already registered,
	 * the last loaded component will be used, so long as it is set to overload.
	 *
	 * Example:
	 * If you want to replace the `stripe` component with a custom stripe component, you would set your id to `stripe` and set
	 * `should_overload` to true.
	 *
	 * This will prevent the original stripe component from being registered and will instead use your custom stripe component.
	 *
	 * @var bool
	 */
	protected static $should_overload = false;

	/**
	 * Get the ID for this class.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	public static function get_id() {
		return static::$id;
	}

	/**
	 * Get if this component should overload.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function should_overload() {
		return static::$should_overload;
	}

	/**
	 * Get the events that this class is subscribed to.
	 *
	 * @note Due to the nature of the EventManager, we have to call this directly as there is a limitation that does not allow
	 * a class that implements the SubscriberInterface to load another class that implements the SubscriberInterface.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function subscribe() {
		$manager = new EventManager();
		$manager->add_subscriber( $this );
	}
}
