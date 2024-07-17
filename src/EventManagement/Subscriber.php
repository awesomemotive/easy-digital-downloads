<?php

namespace EDD\EventManagement;

defined( 'ABSPATH' ) || exit;

/**
 * Class Subscriber
 *
 * @since 3.3.0
 * @package EDD\EventManagement
 */
abstract class Subscriber implements SubscriberInterface {

	/**
	 * The instance of this class.
	 *
	 * @var Subscriber
	 */
	public static $instance;

	/**
	 * Gets the instance of this class.
	 *
	 * @since 3.3.0
	 * @return Subscriber
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}
}
