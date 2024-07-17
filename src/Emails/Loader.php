<?php
/**
 * Handles loading the email classes.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.3.0
 */

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\EventManagement\EventManager;

/**
 * Class Loader
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Loader implements SubscriberInterface {

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.3.0
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
	 * @since 3.3.0
	 * @return void
	 */
	public function add_events() {
		$email_classes = array(
			new Handler(),
			new Triggers(),
			new Legacy(),
		);

		if ( is_admin() ) {
			$email_classes[] = new \EDD\Admin\Emails\Manager();
			$email_classes[] = new \EDD\Admin\Emails\Messages();
		}

		$events = new EventManager();
		foreach ( $email_classes as $email_class ) {
			$events->add_subscriber( $email_class );
		}
	}
}
