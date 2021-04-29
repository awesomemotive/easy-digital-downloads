<?php
/**
 * Webhook Event Handler
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal\Webhooks\Events;

use EDD\PayPal\API;
use EDD\PayPal\Exceptions\API_Exception;
use EDD\PayPal\Exceptions\Authentication_Exception;

abstract class Webhook_Event {

	/**
	 * Webhook event object
	 *
	 * @var object
	 * @since 2.11
	 */
	protected $event;

	/**
	 * Webhook_Event constructor.
	 *
	 * @param object $event
	 *
	 * @since 2.11
	 */
	public function __construct( $event ) {
		$this->event = $event;
	}

	/**
	 * Handles the webhook event.
	 */
	public function handle() {
		try {
			$this->process_event();

			wp_send_json_success( 'Success', 200 );
		} catch ( Authentication_Exception $e ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook due to authentication exception.', true );
			wp_send_json_error( 'Authentication exception.', 403 );
		} catch ( API_Exception $e ) {
			edd_debug_log( sprintf( 'PayPal Commerce - Exiting webhook due to an API exception. Message: %s', $e->getMessage() ), true );
			wp_send_json_error( 'API exception.', 500 );
		} catch ( \Exception $e ) {
			$response_code = $e->getCode() > 0 ? $e->getCode() : 500;

			edd_debug_log( sprintf( 'PayPal Commerce - Exiting webhook due to a general exception. Message: %s', $e->getMessage() ), true );
			wp_send_json_error( sprintf( 'Error: %s', $e->getMessage() ), $response_code );
		}
	}

	/**
	 * Processes the event.
	 *
	 * @since 2.11
	 * @return void
	 */
	abstract protected function process_event();

	/**
	 * Retrieves an EDD_Payment record from the event's resource link.
	 *
	 * @param string $link_rel Link relation.
	 *
	 * @since 2.11
	 *
	 * @return \EDD_Payment
	 * @throws API_Exception
	 * @throws \EDD\PayPal\Exceptions\Authentication_Exception
	 * @throws \Exception
	 */
	protected function get_payment_from_resource_link( $link_rel = 'up' ) {
		if ( empty( $this->event->resource->links ) || ! is_array( $this->event->resource->links ) ) {
			throw new \Exception( 'Missing resources.', 200 );
		}

		$order_link = current( array_filter( $this->event->resource->links, function ( $link ) use ( $link_rel ) {
			return ! empty( $link->rel ) && $link_rel === strtolower( $link->rel );
		} ) );

		if ( empty( $order_link->href ) ) {
			throw new \Exception( 'Missing order link.', 200 );
		}

		// Based on the payment link, determine which mode we should act in.
		if ( false === strpos( $order_link->href, 'sandbox.paypal.com' ) ) {
			$mode = API::MODE_LIVE;
		} else {
			$mode = API::MODE_SANDBOX;
		}

		// Look up the full order record in PayPal.
		$api      = new API( $mode );
		$response = $api->make_request( $order_link->href, array(), array(), $order_link->method );

		if ( 200 !== $api->last_response_code ) {
			throw new API_Exception( sprintf( 'Invalid response code when retrieving order record: %d', $api->last_response_code ) );
		}

		if ( empty( $response->id ) ) {
			throw new API_Exception( 'Missing order ID from API response.' );
		}

		// First, try to find a payment record using `custom_id`, because that's better for performance.
		$payment = false;
		if ( ! empty( $response->custom_id ) ) {
			$payment = edd_get_payment( $response->custom_id );
		}

		if ( empty( $payment ) ) {
			// Otherwise, we'll retrieve it by transaction ID. This is SLOW though!
			$payment = edd_get_purchase_id_by_transaction_id( $response->id );
		}

		if ( empty( $payment ) ) {
			throw new \Exception( 'Failed to locate payment record.', 200 );
		}

		if ( $response->id !== $payment->transaction_id ) {
			throw new \Exception( sprintf( 'Transaction ID mismatch. PayPal: %s; EDD: %s', $response->id, $payment->transaction_id ) );
		}

		edd_debug_log( sprintf( 'PayPal Commerce - Associated resource %s ID %s with EDD payment ID %d', $this->event->resource_type, $this->event->resource->id, $payment->ID ) );

		return $payment;
	}

}
