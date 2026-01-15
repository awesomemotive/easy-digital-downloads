<?php
/**
 * REST Manager
 *
 * Manages the REST API endpoints.
 *
 * @package     EDD\REST
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\REST;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Manager class
 *
 * Manages the REST API endpoints.
 *
 * @since 3.6.2
 */
class Manager implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.6.2
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_rest_routes',
		);
	}

	/**
	 * Register REST routes.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function register_rest_routes() {
		foreach ( $this->get_routes() as $route ) {
			$route->register();
		}
	}

	/**
	 * Get the routes.
	 *
	 * @since 3.6.2
	 * @return array
	 */
	private function get_routes() {
		return array(
			new Routes\Cart(),
			new Routes\LogPruning(),
		);
	}
}
