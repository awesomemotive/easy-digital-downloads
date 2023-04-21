<?php

namespace EDD\EventManagement;

/**
 * The WordPress event manager manages events using the WordPress plugin API.
 */
class EventManager extends PluginAPIManager {

	/**
	 * Add an event subscriber.
	 *
	 * The event manager registers all the hooks that the given subscriber
	 * wants to register with the WordPress Plugin API.
	 *
	 * @param SubscriberInterface $subscriber
	 */
	public function add_subscriber( SubscriberInterface $subscriber ) {
		foreach ( $subscriber->get_subscribed_events() as $hook_name => $parameters ) {
			$this->add_subscriber_callback( $subscriber, $hook_name, $parameters );
		}
	}

	/**
	 * Remove an event subscriber.
	 *
	 * The event manager removes all the hooks that the given subscriber
	 * wants to register with the WordPress Plugin API.
	 *
	 * @param SubscriberInterface $subscriber
	 */
	public function remove_subscriber( SubscriberInterface $subscriber ) {
		foreach ( $subscriber->get_subscribed_events() as $hook_name => $parameters ) {
			$this->remove_subscriber_callback( $subscriber, $hook_name, $parameters );
		}
	}

	/**
	 * Adds the given subscriber's callback to a specific hook
	 * of the WordPress plugin API.
	 *
	 * @param SubscriberInterface $subscriber
	 * @param string              $hook_name
	 * @param mixed               $parameters
	 */
	private function add_subscriber_callback( SubscriberInterface $subscriber, $hook_name, $parameters ) {
		if ( is_string( $parameters ) ) {
			$this->add_callback( $hook_name, array( $subscriber, $parameters ) );
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$this->add_callback( $hook_name, array( $subscriber, $parameters[0] ), isset( $parameters[1] ) ? $parameters[1] : 10, isset( $parameters[2] ) ? $parameters[2] : 1 );
		}
	}

	/**
	 * Removes the given subscriber's callback to a specific hook
	 * of the WordPress plugin API.
	 *
	 * @param SubscriberInterface $subscriber
	 * @param string              $hook_name
	 * @param mixed               $parameters
	 */
	private function remove_subscriber_callback( SubscriberInterface $subscriber, $hook_name, $parameters ) {
		if ( is_string( $parameters ) ) {
			$this->remove_callback( $hook_name, array( $subscriber, $parameters ) );
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$this->remove_callback( $hook_name, array( $subscriber, $parameters[0] ), isset( $parameters[1] ) ? $parameters[1] : 10 );
		}
	}
}
