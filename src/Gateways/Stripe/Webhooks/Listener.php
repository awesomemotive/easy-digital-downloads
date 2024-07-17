<?php
/**
 * The Stripe Webhooks Loader.
 *
 * @package     EDD
 * @subpackage  Gateways\Stripe\Webhooks
 * @since       3.3.0
 */

namespace EDD\Gateways\Stripe\Webhooks;
use EDD\EventManagement\SubscriberInterface;
use EDD\Utils;
use EDD_Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Loader
 *
 * @since 3.3.0
 */
final class Listener implements SubscriberInterface {

	/**
	 * The event.
	 *
	 * @var \Stripe\Event
	 *
	 * @since 3.3.0
	 */
	private $event;

	/**
	 * The subscribed events.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'init' => 'process_webhook',
		);
	}

	/**
	 * Processes the webhook.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function process_webhook() {
		if ( ! $this->should_capture_webhook() ) {
			return;
		}

		try {
			$this->retrieve_event();

			$event_type  = $this->event->type;
			$event_class = $this->parse_event_class( $event_type );

			/**
			 * We typically try and use the ::class constant, but we need to be able to support
			 * events that are not registered classes, so we will manually build the class name,
			 * so we don't trigger the catch block, which allows a hook to fire for the event.
			 */
			$event_class = __NAMESPACE__ . '\\Events\\' . $event_class;

			// Check our event classes to see if it exists.
			if ( class_exists( $event_class ) && is_subclass_of( $event_class, 'EDD\Gateways\Stripe\Webhooks\Events\Event' ) ) {
				$event = new $event_class( $this->event );
				if ( $event->verify_mode() && $event->requirements_met() ) {
					$event->process();
				}
			}

			/**
			 * Fires after the Stripe event has been processed.
			 *
			 * This is a backwards compatibility hook, meant to allow extending the webhook handling.
			 *
			 * @param string $event_type The event type.
			 * @parma \Stripe\Event $event The Stripe event.
			 */
			do_action( 'edds_stripe_event_' . $this->event->type, $this->event );

			$this->send_success();

		} catch ( EDD_Exception $e ) {
			$this->send_failure();
		}
	}

	/**
	 * Determines if the webhook should be captured.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function should_capture_webhook() {
		return isset( $_GET['edd-listener'] ) && 'stripe' === $_GET['edd-listener'];
	}

	/**
	 * Retrieves the event.
	 *
	 * @since 3.3.0
	 * @throws EDD_Exception If the event cannot be retrieved.
	 * @throws Utils\Exception If the event is invalid.
	 * @return void
	 */
	private function retrieve_event() {
		$body = @file_get_contents( 'php://input' );
		try {
			$event = json_decode( $body );

			if ( false === $event || ! isset( $event->id ) ) {
				throw new Utils\Exception( esc_html__( 'Invalid Event', 'easy-digital-downloads' ) );
			}

			$event = edds_api_request( 'Event', 'retrieve', $event->id );
		} catch ( EDD_Exception $exception ) {
			throw $exception;
		}

		$this->event = $event;
	}

	/**
	 * Gets the event class.
	 *
	 * Events are in the format of `object.action` or `object.subobject.action`. We will convert these
	 * to camel case and remove periods to get the class name.
	 *
	 * For example:
	 * `charge.succeeded` would become `ChargeSucceeded`.
	 * `radar.early_fraud_warning.created` would become `RadarEarlyFraudWarningCreated`.
	 *
	 * @since 3.3.0
	 * @param string $event_type The event type.
	 * @return string
	 */
	private function parse_event_class( $event_type ) {
		// Replace the . and _ characters with spaces.
		$event_type = str_replace( array( '.', '_' ), ' ', $event_type );

		// Uppercase the first letter of each word.
		$event_type = ucwords( $event_type );

		// Remove spaces.
		return str_replace( ' ', '', $event_type );
	}

	/**
	 * Sends a success response.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function send_success() {
		// Nothing failed, mark complete.
		status_header( 200 );
		die( esc_html( 'EDD Stripe: ' . $this->event->type ) );
	}

	/**
	 * Sends a failure response.
	 *
	 * This allows us to be able to have Stripe retry the webhook.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function send_failure() {
		http_response_code( 500 );
		die( '-2' );
		exit;
	}
}
