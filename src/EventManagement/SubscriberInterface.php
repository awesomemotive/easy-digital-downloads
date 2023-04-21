<?php
/**
 * Subscriber interface for registering event listeners.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\EventManagement;

interface SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('event_name' => 'method_name')
	 *  * array('event_name' => array('method_name', $priority))
	 *  * array('event_name' => array('method_name', $priority, $accepted_args))
	 *
	 * @return array
	 */
	public static function get_subscribed_events();
}
