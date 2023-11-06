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
			'admin_init' => 'register_admin_integrations',
		);
	}

	/**
	 * Registers the integrations.
	 *
	 * @since 3.2.4
	 * @return void
	 */
	public function register_admin_integrations() {
		$integrations = array(
			WPCode::class,
		);

		foreach ( $integrations as $integration ) {
			if ( ! class_exists( $integration ) ) {
				continue;
			}

			try {
				$integration = new $integration();
				$integration->subscribe();
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}
	}
}
