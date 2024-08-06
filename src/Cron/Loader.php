<?php
/**
 * Class for loading our Cron Integrations.
 *
 * @package EDD
 * @subpackage Cron
 *
 * @since 3.3.0
 */

namespace EDD\Cron;

use EDD\EventManagement\SubscriberInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Loader Class
 *
 * @since 3.3.0
 */
class Loader implements SubscriberInterface {

	/**
	 * Get the events that this class is subscribed to.
	 *
	 * @since 3.3.0
	 */
	public static function get_subscribed_events() {
		return array(
			'cron_schedules' => 'load_schedules',
			'init'           => 'load_events',
			'plugins_loaded' => array( 'load_components', 999 ), // Run this late, to ensure plugins are loaded.
		);
	}

	/**
	 * Add our custom schedules to the cron schedules.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function load_schedules( $schedules ) {
		foreach ( $this->get_registered_schedules() as $schedule ) {
			// If this isn't a subclass of Schedule, skip it.
			if ( ! is_subclass_of( $schedule, 'EDD\Cron\Schedules\Schedule' ) ) {
				continue;
			}

			if ( $schedule->valid ) {
				$schedules[ $schedule->id ] = array(
					'interval' => $schedule->interval,
					'display'  => $schedule->display_name,
				);
			}
		}

		return $schedules;
	}

	/**
	 * Load any cron events we need to.
	 *
	 * Cron Events are the 'do_action' events that are fired by WordPress, on a defined schedule.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function load_events() {
		foreach ( $this->get_registered_events() as $event ) {
			// If this isn't a subclass of Event, skip it.
			if ( ! is_subclass_of( $event, 'EDD\Cron\Events\Event' ) ) {
				continue;
			}

			$event->schedule();
		}
	}

	/**
	 * Load any components registered that have cron events.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function load_components() {
		// We'll collect the final list of components to register here.
		$final_components_list = array();

		foreach ( $this->get_registered_components() as $component_class ) {
			// If this isn't a subclass of Component, skip it.
			if ( ! is_subclass_of( $component_class, 'EDD\Cron\Components\Component' ) ) {
				continue;
			}

			// If the array key exists, and the class isn't set to overload, skip it.
			if ( array_key_exists( $component_class::get_id(), $final_components_list ) && ! $component_class::should_overload() ) {
				continue;
			}

			// Either the array key doesn't exist already, or we are intentionally overloading it, so add it to the list.
			$final_components_list[ $component_class::get_id() ] = $component_class;
		}

		/**
		 * Load the components.
		 *
		 * Since these extend the Component class, they should all be using the SubscriberInterface to hook into the events.
		 */
		foreach ( $final_components_list as $component_class ) {
			$component = new $component_class();

			// Since this loads to late, we need to trigger the event management now.
			$component->subscribe();
		}
	}

	/**
	 * Get the registered schedules.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function get_registered_schedules() {
		$registered_schedules = array();

		/**
		 * Filter the registered cron schedules.
		 *
		 * @since 3.3.0
		 *
		 * @param array $registered_schedules The currently registered cron schedules.
		 *
		 * Example:
		 * add_filter( 'edd_cron_schedules', function( $registered_schedules ) {
		 *    $registered_schedules[] = new MyCustomSchedule();
		 *   return $registered_schedules;
		 * } );
		 *
		 * @return array
		 */
		$registered_schedules = apply_filters( 'edd_cron_schedules', $registered_schedules );

		// Since we have a filter here, if something goes wrong return an empty array.
		if ( ! is_array( $registered_schedules ) ) {
			return array();
		}

		return $registered_schedules;

	}

	/**
	 * Get the registered events.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function get_registered_events() {
		$registered_events = array(
			new Events\DailyEvents(),
			new Events\WeeklyEvents(),
			new Events\StripeRateLimitingCleanup(),
		);

		/**
		 * Filter the registered cron events.
		 *
		 * @since 3.3.0
		 *
		 * @param array $registered_events The currently registered cron events.
		 *
		 * Example:
		 * add_filter( 'edd_cron_events', function( $registered_events ) {
		 *    $registered_events[] = new MyCustomEvent();
		 *   return $registered_events;
		 * } );
		 *
		 * @return array
		 */
		$registered_events = apply_filters( 'edd_cron_events', $registered_events );

		// Since we have a filter here, if something goes wrong return an empty array.
		if ( ! is_array( $registered_events ) ) {
			return array();
		}

		return $registered_events;
	}

	/**
	 * Get the registered components.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function get_registered_components() {
		// Register our components.
		$components_to_register = array(
			Components\Cart::class,
			Components\EmailSummaries::class,
			Components\Exports::class,
			Components\Notifications::class,
			Components\Orders::class,
			Components\Passes::class,
			Components\Store::class,
			Components\Stripe::class,
			Components\EmailSummariesBlurbs::class,
		);

		/**
		 * Filter the components to register.
		 *
		 * @since 3.3.0
		 *
		 * @param array $components_to_register The currently registered cron components.
		 *
		 * Example:
		 * add_filter( 'edd_cron_components', function( $components_to_register ) {
		 *    $components_to_register[] = MyNameSpace\MyClass::class;
		 *    return $components_to_register;
		 * } );
		 *
		 * @return array
		 */
		$components_to_register = apply_filters( 'edd_cron_components', $components_to_register );

		// Since we have a filter here, if something goes wrong return an empty array.
		if ( ! is_array( $components_to_register ) ) {
			return array();
		}

		return $components_to_register;
	}
}
