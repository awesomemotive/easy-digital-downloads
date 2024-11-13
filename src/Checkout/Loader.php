<?php
/**
 * Handles loading the checkout classes.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.5
 */

namespace EDD\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\EventManagement\EventManager;

/**
 * Class Loader
 *
 * @since 3.3.5
 * @package EDD\Checkout
 */
class Loader implements SubscriberInterface {

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.3.5
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'plugins_loaded' => 'add_events',
		);
	}

	/**
	 * Add the email events.
	 *
	 * @since 3.3.5
	 * @return void
	 */
	public function add_events() {
		$checkout_classes = array(
			AutoRegister::get_instance(),
			new Errors(),
		);

		$events = new EventManager();
		foreach ( $checkout_classes as $checkout_class ) {
			$events->add_subscriber( $checkout_class );
		}
	}
}
