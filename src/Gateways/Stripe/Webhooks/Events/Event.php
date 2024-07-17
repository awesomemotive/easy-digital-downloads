<?php
/**
 * The Stripe Webhooks Event abstract class.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks\Events
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Event
 *
 * @since 3.3.0
 */
abstract class Event {
	use Traits\Mode;

	/**
	 * The event object.
	 *
	 * @var \Stripe\Event
	 * @since 3.3.0
	 */
	protected $event;

	/**
	 * The event data.
	 *
	 * @var array
	 * @since 3.3.0
	 */
	protected $data;

	/**
	 * The event object.
	 *
	 * This type will vary by webhook. Each webhook event should define this property with the appropriate type.
	 *
	 * @var object
	 * @since 3.3.0
	 */
	protected $object;


	/**
	 * Event constructor.
	 *
	 * @since 3.3.0
	 *
	 * @param \Stripe\Event $event The event object.
	 */
	public function __construct( $event ) {
		$this->event  = $event;
		$this->data   = $event->data;
		$this->object = $event->data->object;
	}

	/**
	 * Check the event mode against the store mode.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	abstract public function process();

	/**
	 * Check if the requirements are met for processing the event.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function requirements_met() {
		return true;
	}
}
