<?php

namespace EDD\Integrations;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;
/**
 * Class Registry
 *
 * @since 3.2.4
 * @package EDD
 */
class Registry implements SubscriberInterface {

	/**
	 * Gets the events that this subscriber is subscribed to.
	 *
	 * @since 3.2.4
	 * @return array The subscribed events.
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_init'     => 'register_admin_integrations',
			'plugins_loaded' => 'register_integrations',
		);
	}

	/**
	 * Registers the admin only integrations.
	 *
	 * @since 3.2.4
	 * @return void
	 */
	public function register_admin_integrations() {
		$this->register_integration_classes(
			array(
				WPCode::class,
			)
		);
	}

	/**
	 * Registers the integrations.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function register_integrations() {
		$this->register_integration_classes(
			array(
				Elementor::class,
			)
		);
	}

	/**
	 * Register integration classes.
	 *
	 * @since 3.6.0
	 * @param array $integration_classes The integration classes to register.
	 */
	private function register_integration_classes( array $integration_classes ) {
		foreach ( $integration_classes as $integration_class ) {
			if ( ! class_exists( $integration_class ) ) {
				continue;
			}

			try {
				$integration = new $integration_class();

				if ( ! $integration instanceof Integration ) {
					continue;
				}

				if ( ! $integration->can_load() ) {
					continue;
				}

				$integration->subscribe();
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}
	}
}
