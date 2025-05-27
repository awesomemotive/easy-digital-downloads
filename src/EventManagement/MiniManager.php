<?php
/**
 * Abstract class for managing events.
 *
 * @package     EDD\EventManagement
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\EventManagement;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class MiniManager
 *
 * @since 3.3.9
 */
abstract class MiniManager implements SubscriberInterface {

	/**
	 * The hook to subscribe to.
	 *
	 * @since 3.3.9
	 * @var string
	 */
	protected static $hook = 'plugins_loaded';

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			static::$hook => 'add_events',
		);
	}

	/**
	 * Add the events.
	 *
	 * @since 3.3.9
	 * @return void
	 */
	public function add_events() {
		$events = new EventManager();
		foreach ( $this->get_event_classes() as $event_class ) {
			$events->add_subscriber( $event_class );
		}
	}

	/**
	 * Get the event classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	abstract protected function get_event_classes(): array;
}
