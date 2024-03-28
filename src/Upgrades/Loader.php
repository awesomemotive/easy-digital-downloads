<?php

namespace EDD\Upgrades;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\EventManagement\EventManager;

/**
 * Class Loader
 *
 * @since 3.2.10
 * @package EDD\Upgrades
 */
class Loader implements SubscriberInterface {

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.2.10
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'plugins_loaded' => 'add_events',
		);
	}

	/**
	 * Add the upgrade events.
	 *
	 * @since 3.2.10
	 * @return void
	 */
	public function add_events() {
		$upgrade_classes = array(
			new Orders\MigrateAfterActionsDate(),
			new Adjustments\DiscountsStartEnd(),
		);

		$events = new EventManager();
		foreach ( $upgrade_classes as $upgrade_class ) {
			$events->add_subscriber( $upgrade_class );
		}
	}
}
