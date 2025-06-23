<?php
/**
 * The Square Webhooks Event abstract class.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Event
 *
 * @since 3.4.0
 */
abstract class Event {
	/**
	 * The event object.
	 *
	 * @var array
	 * @since 3.4.0
	 */
	protected $event;

	/**
	 * The event data.
	 *
	 * @var array
	 * @since 3.4.0
	 */
	protected $data;

	/**
	 * The event object.
	 *
	 * This type will vary by webhook. Each webhook event should define this property with the appropriate type.
	 *
	 * @var object
	 * @since 3.4.0
	 */
	protected $object;


	/**
	 * Event constructor.
	 *
	 * @since 3.4.0
	 *
	 * @param array $event The event object.
	 */
	public function __construct( $event ) {
		$this->event  = $event;
		$this->data   = $event['data'];
		$this->object = $event['data']['object'];
	}

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	abstract public function process();

	/**
	 * Check if the requirements are met for processing the event.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function requirements_met() {
		return true;
	}
}
