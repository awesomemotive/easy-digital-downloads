<?php
/**
 * PayPal Capture Refunded Webhook Tests
 *
 * @package     EDD\Tests\Gateways\PayPal
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Gateways\PayPal;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Refunded;

/**
 * Tests for the PAYMENT.CAPTURE.REFUNDED webhook.
 *
 * @group gateways
 * @group paypal
 * @group paypal-webhook-capture-refunded
 */
class WebhookCaptureRefunded extends EDD_UnitTestCase {

	const CAPTURE_ID = '27M47624FP291604U';

	const REFUND_ID = '1JU08902781691411';

	const TOKEN_CACHE_KEY = 'edd_paypal_commerce_access_token_sandbox';

	/**
	 * The order the refunds are issued against.
	 *
	 * @var \EDD\Orders\Order
	 */
	private $order;

	/**
	 * PayPal credential settings as they stood before the test, null where absent.
	 *
	 * @var array
	 */
	private $original_credentials = array();

	/**
	 * Sets up an order paid through PayPal and sandbox credentials for the capture lookup.
	 */
	public function set_up() {
		parent::set_up();

		foreach ( array( 'paypal_sandbox_client_id', 'paypal_sandbox_client_secret' ) as $key ) {
			$this->original_credentials[ $key ] = edd_get_option( $key, null );
			edd_update_option( $key, 'test_' . $key );
		}

		add_filter( 'pre_http_request', array( $this, 'mock_paypal_api' ), 10, 3 );

		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array(
				'gateway' => 'paypal_commerce',
			)
		);
		edd_update_order(
			$order_id,
			array(
				'gateway' => 'paypal_commerce',
				'status'  => 'complete',
			)
		);
		// The webhook checks the capture against the order's primary transaction, which the helper has already created.
		$primary_transaction = edd_get_order_transactions(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'number'      => 1,
			)
		);
		edd_update_order_transaction(
			$primary_transaction[0]->id,
			array(
				'transaction_id' => self::CAPTURE_ID,
				'gateway'        => 'paypal_commerce',
			)
		);

		$this->order = edd_get_order( $order_id );
	}

	/**
	 * Restores the credentials and removes the API mock and cached token.
	 */
	public function tear_down() {
		foreach ( $this->original_credentials as $key => $value ) {
			if ( is_null( $value ) ) {
				edd_delete_option( $key );
			} else {
				edd_update_option( $key, $value );
			}
		}

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_api' ), 10 );
		delete_option( self::TOKEN_CACHE_KEY );

		parent::tear_down();
	}

	/**
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Refunded::process_event
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_full_refund_webhook_sets_refunded_with_refund_transaction() {
		$this->handle_refund_webhook( $this->order->total );

		$this->assertEquals( 'refunded', edd_get_order( $this->order->id )->status );

		$refunds = $this->get_refund_orders();
		$this->assertCount( 1, $refunds );
		$refund_transaction = edd_get_order_transaction_by( 'transaction_id', self::REFUND_ID );
		$this->assertEquals( $refunds[0]->id, $refund_transaction->object_id, 'The refund transaction should belong to the refund order.' );
		$this->assertEquals( edd_negate_amount( $this->order->total ), $refund_transaction->total, 'The refund transaction should be negative, matching refunds issued from EDD.' );
	}

	/**
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Refunded::process_event
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_partial_refund_webhook_sets_partially_refunded() {
		$this->handle_refund_webhook( $this->order->total / 2 );

		$this->assertEquals( 'partially_refunded', edd_get_order( $this->order->id )->status );
		$this->assertEmpty( $this->get_refund_orders() );
	}

	/**
	 * Test: PayPal mirroring a refund EDD already recorded does not refund the rest of the order.
	 *
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Refunded::process_event
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_refund_webhook_after_edd_partial_refund_of_same_amount_stays_partially_refunded() {
		$refunded_item = $this->order->items[0];
		edd_refund_order(
			$this->order->id,
			array(
				array(
					'order_item_id' => $refunded_item->id,
					'subtotal'      => $refunded_item->subtotal,
					'tax'           => $refunded_item->tax,
					'total'         => $refunded_item->total,
				),
			),
			array()
		);

		$this->handle_refund_webhook( $this->order->total - $refunded_item->total );

		$this->assertEquals( 'partially_refunded', edd_get_order( $this->order->id )->status );
		$this->assertCount( 1, $this->get_refund_orders(), 'Only the refund EDD recorded itself should exist.' );
	}

	/**
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Refunded::process_event
	 */
	public function test_refund_webhook_for_existing_refund_transaction_is_ignored() {
		edd_add_order_transaction(
			array(
				'object_id'      => $this->order->id,
				'object_type'    => 'order',
				'transaction_id' => self::REFUND_ID,
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => $this->order->total,
			)
		);

		$this->handle_refund_webhook( $this->order->total );

		$this->assertEquals( 'complete', edd_get_order( $this->order->id )->status );
		$this->assertEmpty( $this->get_refund_orders() );
	}

	/**
	 * Answers the token and capture lookups the webhook makes to resolve the order.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args    Request arguments.
	 * @param string      $url     Request URL.
	 * @return false|array
	 */
	public function mock_paypal_api( $preempt, $args, $url ) {
		if ( false !== strpos( $url, '/v1/oauth2/token' ) ) {
			return $this->mock_response(
				array(
					'access_token' => 'test_access_token',
					'expires_in'   => 3600,
				)
			);
		}

		if ( false !== strpos( $url, '/v2/payments/captures/' . self::CAPTURE_ID ) ) {
			return $this->mock_response( array( 'id' => self::CAPTURE_ID ) );
		}

		return $preempt;
	}

	/**
	 * Sends a PAYMENT.CAPTURE.REFUNDED event for the order's capture.
	 *
	 * @param float $refunded_amount The amount PayPal reports as refunded.
	 */
	private function handle_refund_webhook( $refunded_amount ) {
		$request = new \WP_REST_Request( 'POST' );
		$request->set_param( 'event_type', 'PAYMENT.CAPTURE.REFUNDED' );
		$request->set_param( 'resource_type', 'refund' );
		$request->set_param(
			'resource',
			array(
				'id'     => self::REFUND_ID,
				'amount' => array(
					'value'         => edd_format_amount( $refunded_amount, true, $this->order->currency, 'typed' ),
					'currency_code' => $this->order->currency,
				),
				'links'  => array(
					array(
						'href'   => 'https://api-m.sandbox.paypal.com/v2/payments/captures/' . self::CAPTURE_ID,
						'rel'    => 'up',
						'method' => 'GET',
					),
				),
			)
		);

		( new Payment_Capture_Refunded( $request ) )->handle();
	}

	/**
	 * Builds a successful JSON HTTP response.
	 *
	 * @param array $body The response body.
	 * @return array
	 */
	private function mock_response( $body ) {
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $body ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
	}

	/**
	 * Gets the refund orders recorded against the order.
	 *
	 * @return \EDD\Orders\Order[]
	 */
	private function get_refund_orders() {
		return edd_get_orders(
			array(
				'type'   => 'refund',
				'parent' => $this->order->id,
			)
		);
	}
}
