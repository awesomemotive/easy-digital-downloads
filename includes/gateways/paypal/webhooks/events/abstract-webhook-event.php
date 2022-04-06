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

namespace EDD\Gateways\PayPal\Webhooks\Events;

use EDD\Gateways\PayPal\API;
use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;
use EDD\Orders\Order;

abstract class Webhook_Event {

	/**
	 * API request
	 *
	 * @var \WP_REST_Request
	 * @since 2.11
	 */
	protected $request;

	/**
	 * Data from the request.
	 *
	 * @var object
	 * @since 2.11
	 */
	protected $event;

	/**
	 * Webhook_Event constructor.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @since 2.11
	 */
	public function __construct( $request ) {
		$this->request = $request;

		// `get_params()` returns an array, but we want an object.
		$this->event = json_decode( json_encode( $this->request->get_params() ) );
	}

	/**
	 * Handles the webhook event.
	 *
	 * @throws \Exception
	 */
	public function handle() {
		$this->process_event();
	}

	/**
	 * Processes the event.
	 *
	 * @since 2.11
	 * @return void
	 */
	abstract protected function process_event();

	/**
	 * Retrieves an Order record from a capture event.
	 *
	 * @since 3.0
	 *
	 * @return Order
	 * @throws \Exception
	 */
	protected function get_order_from_capture() {
		if ( 'capture' !== $this->request->get_param( 'resource_type' ) ) {
			throw new \Exception( sprintf( 'get_payment_from_capture() - Invalid resource type: %s', $this->request->get_param( 'resource_type' ) ) );
		}

		if ( empty( $this->event->resource ) ) {
			throw new \Exception( sprintf( 'get_payment_from_capture() - Missing event resource.' ) );
		}

		return $this->get_order_from_capture_object( $this->event->resource );
	}

	/**
	 * Retrieves an Order record from a capture object.
	 *
	 * @param object $resource
	 *
	 * @since 3.0
	 *
	 * @return Order
	 * @throws \Exception
	 */
	protected function get_order_from_capture_object( $resource ) {
		$order = false;

		if ( ! empty( $resource->custom_id ) && is_numeric( $resource->custom_id ) ) {
			$order = edd_get_order( $resource->custom_id );
		}

		if ( empty( $order ) && ! empty( $resource->id ) ) {
			$order_id = edd_get_order_id_from_transaction_id( $resource->id );
			$order    = $order_id ? edd_get_order( $order_id ) : false;
		}

		if ( ! $order instanceof Order ) {
			throw new \Exception( 'get_order_from_capture_object() - Failed to locate order.', 200 );
		}

		/*
		 * Verify the transaction ID. This covers us in case we fetched the order via `custom_id`, but
		 * it wasn't actually an EDD-initiated payment.
		 */
		$order_transaction_id = $order->get_transaction_id();
		if ( $order_transaction_id !== $resource->id ) {
			throw new \Exception( sprintf(
				'get_order_from_capture_object() - Transaction ID mismatch. Expected: %s; Actual: %s',
				$order_transaction_id,
				$resource->id
			), 200 );
		}

		return $order;
	}

	/**
	 * Retrieves an Order record from a refund event.
	 *
	 * @since      3.0
	 *
	 * @return Order
	 * @throws API_Exception
	 * @throws Authentication_Exception
	 * @throws \Exception
	 */
	protected function get_order_from_refund() {
		edd_debug_log( sprintf(
			'PayPal Commerce Webhook - get_payment_from_capture_object() - Resource type: %s; Resource ID: %s',
			$this->request->get_param( 'resource_type' ),
			$this->event->resource->id
		) );

		if ( empty( $this->event->resource->links ) || ! is_array( $this->event->resource->links ) ) {
			throw new \Exception( 'Missing resources.', 200 );
		}

		$order_link = current( array_filter( $this->event->resource->links, function ( $link ) {
			return ! empty( $link->rel ) && 'up' === strtolower( $link->rel );
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

		return $this->get_order_from_capture_object( $response );
	}

	/**
	 * Retrieves an EDD_Payment record from a capture event.
	 *
	 * @since      2.11
	 * @deprecated 3.0 In favour of `get_order_from_capture()`
	 * @see        Webhook_Event::get_order_from_capture()
	 *
	 * @return \EDD_Payment
	 * @throws \Exception
	 */
	protected function get_payment_from_capture() {
		if ( 'capture' !== $this->request->get_param( 'resource_type' ) ) {
			throw new \Exception( sprintf( 'get_payment_from_capture() - Invalid resource type: %s', $this->request->get_param( 'resource_type' ) ) );
		}

		if ( empty( $this->event->resource ) ) {
			throw new \Exception( sprintf( 'get_payment_from_capture() - Missing event resource.' ) );
		}

		return $this->get_payment_from_capture_object( $this->event->resource );
	}

	/**
	 * Retrieves an EDD_Payment record from a capture object.
	 *
	 * @param object $resource
	 *
	 * @since      2.11
	 * @deprecated 3.0 In favour of `get_order_from_capture_object()`
	 * @see        Webhook_Event::get_order_from_capture_object
	 *
	 * @return \EDD_Payment
	 * @throws \Exception
	 */
	protected function get_payment_from_capture_object( $resource ) {
		$payment = false;

		if ( ! empty( $resource->custom_id ) && is_numeric( $resource->custom_id ) ) {
			$payment = edd_get_payment( $resource->custom_id );
		}

		if ( empty( $payment ) && ! empty( $resource->id ) ) {
			$payment_id = edd_get_purchase_id_by_transaction_id( $resource->id );
			$payment    = $payment_id ? edd_get_payment( $payment_id ) : false;
		}

		if ( ! $payment instanceof \EDD_Payment ) {
			throw new \Exception( 'get_payment_from_capture_object() - Failed to locate payment.', 200 );
		}

		/*
		 * Verify the transaction ID. This covers us in case we fetched the payment via `custom_id`, but
		 * it wasn't actually an EDD-initiated payment.
		 */
		if ( $payment->transaction_id !== $resource->id ) {
			throw new \Exception( sprintf( 'get_payment_from_capture_object() - Transaction ID mismatch. Expected: %s; Actual: %s', $payment->transaction_id, $resource->id ), 200 );
		}

		return $payment;
	}

	/**
	 * Retrieves an EDD_Payment record from a refund event.
	 *
	 * @since      2.11
	 * @deprecated 3.0 In favour of `get_order_from_refund`
	 * @see        Webhook_Event::get_order_from_refund
	 *
	 * @return \EDD_Payment
	 * @throws API_Exception
	 * @throws Authentication_Exception
	 * @throws \Exception
	 */
	protected function get_payment_from_refund() {
		edd_debug_log( sprintf( 'PayPal Commerce Webhook - get_payment_from_refund() - Resource type: %s; Resource ID: %s', $this->request->get_param( 'resource_type' ), $this->event->resource->id ) );

		if ( empty( $this->event->resource->links ) || ! is_array( $this->event->resource->links ) ) {
			throw new \Exception( 'Missing resources.', 200 );
		}

		$order_link = current( array_filter( $this->event->resource->links, function ( $link ) {
			return ! empty( $link->rel ) && 'up' === strtolower( $link->rel );
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

		return $this->get_payment_from_capture_object( $response );
	}

}
