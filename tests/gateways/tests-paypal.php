<?php
/**
 * PayPal Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Tests\Gateways;

use EDD\PayPal\Webhooks\Events\Payment_Capture_Completed;
use EDD\PayPal\Webhooks\Events\Payment_Capture_Denied;
use EDD\PayPal\Webhooks\Events\Webhook_Event;
use EDD\PayPal\Webhooks\Webhook_Handler;
use EDD_UnitTestCase;
use EDD_Payment;

class Tests_PayPal extends EDD_UnitTestCase {

	const TRANSACTION_ID = '27M47624FP291604U';

	/**
	 * @var EDD_Payment
	 */
	protected $payment;

	public function setUp() {
		parent::setUp();

		$payment_id = \EDD_Helper_Payment::create_simple_payment();
		edd_set_payment_transaction_id( $payment_id, self::TRANSACTION_ID );

		wp_cache_flush();

		$this->payment = edd_get_payment( $payment_id );
	}

	/**
	 * Builds a valid REST Request object that can be passed to the webhook event handler.
	 *
	 * @param string $payload JSON payload.
	 *
	 * @return \WP_REST_Request
	 */
	private function build_rest_request( $payload ) {
		$request = new \WP_REST_Request( 'POST', Webhook_Handler::REST_NAMESPACE . '/' . Webhook_Handler::REST_ROUTE );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( $payload );

		return $request;
	}

	/**
	 * Builds a payload for the PAYMENT.CAPTURE.COMPLETED event.
	 *
	 * @param array $args {
	 *
	 * @type float  $amount
	 * @type string $currency_code
	 * @type string $transaction_id
	 * @type int    $custom_id
	 *                    }
	 *
	 * @return string
	 */
	private function get_payment_capture_completed_payload( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'amount'         => 120.00,
			'currency_code'  => 'USD',
			'transaction_id' => self::TRANSACTION_ID,
			'custom_id'      => $this->payment->ID
		) );

		$args['amount'] = (float) $args['amount'];

		return '{
  "id": "WH-58D329510W468432D-8HN650336L201105X",
  "create_time": "2019-02-14T21:50:07.940Z",
  "resource_type": "capture",
  "event_type": "PAYMENT.CAPTURE.COMPLETED",
  "summary": "Payment completed for $ 120 USD",
  "resource": {
    "amount": {
      "currency_code": "' . $args['currency_code'] . '",
      "value": "' . $args['amount'] . '"
    },
    "seller_protection": {
      "status": "ELIGIBLE",
      "dispute_categories": [
        "ITEM_NOT_RECEIVED",
        "UNAUTHORIZED_TRANSACTION"
      ]
    },
    "update_time": "2019-02-14T21:49:58Z",
    "create_time": "2019-02-14T21:49:58Z",
    "final_capture": true,
    "seller_receivable_breakdown": {
      "gross_amount": {
        "currency_code": "USD",
        "value": "' . $args['amount'] . '"
      },
      "paypal_fee": {
        "currency_code": "' . $args['currency_code'] . '",
        "value": "0.37"
      },
      "net_amount": {
        "currency_code": "' . $args['currency_code'] . '",
        "value": "119.63"
      }
    },
    "custom_id": "' . $args['custom_id'] . '",
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/captures/' . $args['transaction_id'] . '",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/captures/' . $args['transaction_id'] . '/refund",
        "rel": "refund",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/7W5147081L658180V",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "' . $args['transaction_id'] . '",
    "status": "COMPLETED"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-58D329510W468432D-8HN650336L201105X",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-58D329510W468432D-8HN650336L201105X/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}';
	}

	/**
	 * Builds a payload for the PAYMENT.CAPTURE.DENIED event.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	private function get_payment_capture_denied_payload( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'transaction_id' => self::TRANSACTION_ID
		) );

		return '{
  "id": "WH-4SW78779LY2325805-07E03580SX1414828",
  "create_time": "2019-02-14T22:20:08.370Z",
  "resource_type": "capture",
  "event_type": "PAYMENT.CAPTURE.DENIED",
  "summary": "A $ 120.00 USD capture payment was denied",
  "resource": {
    "amount": {
      "currency_code": "USD",
      "value": "120.00"
    },
    "seller_protection": {
      "status": "ELIGIBLE",
      "dispute_categories": [
        "ITEM_NOT_RECEIVED",
        "UNAUTHORIZED_TRANSACTION"
      ]
    },
    "update_time": "2019-02-14T22:20:01Z",
    "create_time": "2019-02-14T22:18:14Z",
    "final_capture": true,
    "seller_receivable_breakdown": {
      "gross_amount": {
        "currency_code": "USD",
        "value": "120.00"
      },
      "net_amount": {
        "currency_code": "USD",
        "value": "120.00"
      }
    },
    "links": [
      {
        "href": "https://api.paypal.com/v2/payments/captures/' . $args['transaction_id'] . '",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.paypal.com/v2/payments/captures/' . $args['transaction_id'] . '/refund",
        "rel": "refund",
        "method": "POST"
      },
      {
        "href": "https://api.paypal.com/v2/payments/authorizations/' . $args['transaction_id'] . '",
        "rel": "up",
        "method": "GET"
      }
    ],
    "id": "' . $args['transaction_id'] . '",
    "status": "DECLINED"
  },
  "links": [
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-4SW78779LY2325805-07E03580SX1414828",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-4SW78779LY2325805-07E03580SX1414828/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}';
	}

	/**
	 * @covers \EDD\PayPal\Webhooks\Events\Payment_Capture_Completed::process_event
	 */
	public function test_payment_capture_completed_marks_payment_complete() {
		// Status should be pending at first.
		$this->assertEquals( 'pending', $this->payment->status );

		$event = new Payment_Capture_Completed( $this->build_rest_request( $this->get_payment_capture_completed_payload( array(
			'amount'        => 120,
			'currency_code' => 'USD'
		) ) ) );
		$event->handle();

		// Refresh payment object.
		$payment = edd_get_payment( $this->payment->ID );
		$this->assertEquals( 'publish', $payment->status );
	}

	/**
	 * @expectedException \Exception
	 * @covers \EDD\PayPal\Webhooks\Events\Payment_Capture_Completed::handle
	 */
	public function test_payment_capture_completed_with_mismatching_amount_throws_exception() {
		$event = new Payment_Capture_Completed( $this->build_rest_request( $this->get_payment_capture_completed_payload( array(
			'amount' => 100.00
		) ) ) );

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( 'Exception' );
		}
		if ( method_exists( $this, 'expectExceptionMessage' ) ) {
			$this->expectExceptionMessage( 'doesn\'t match payment amount' );
		}

		$event->handle();
	}

	/**
	 * @expectedException \Exception
	 * @covers \EDD\PayPal\Webhooks\Events\Payment_Capture_Completed::handle
	 */
	public function test_payment_capture_completed_with_mismatching_currency_throws_exception() {
		$event = new Payment_Capture_Completed( $this->build_rest_request( $this->get_payment_capture_completed_payload( array(
			'currency_code' => 'GBP'
		) ) ) );

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( 'Exception' );
		}
		if ( method_exists( $this, 'expectExceptionMessage' ) ) {
			$this->expectExceptionMessage( 'Missing or invalid currency code' );
		}

		$event->handle();
	}

	/**
	 * @expectedException \Exception
	 * @covers \EDD\PayPal\Webhooks\Events\Payment_Capture_Completed::get_payment_from_capture
	 * @throws \Exception
	 */
	public function test_payment_capture_completed_with_correct_custom_id_but_wrong_transaction_id_throws_exception() {
		$event = new Payment_Capture_Completed( $this->build_rest_request( $this->get_payment_capture_completed_payload( array(
			'transaction_id' => 'wrong'
		) ) ) );

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( 'Exception' );
		}
		if ( method_exists( $this, 'expectExceptionMessage' ) ) {
			$this->expectExceptionMessage( 'get_payment_from_capture_object() - Transaction ID mismatch.' );
		}

		$event->handle();
	}

	/**
	 * @covers \EDD\PayPal\Webhooks\Events\Payment_Capture_Denied::handle
	 * @throws \Exception
	 */
	public function test_payment_capture_denied_marks_payment_failed() {
		$this->assertNotEquals( 'failed', $this->payment->status );

		$payload = $this->get_payment_capture_denied_payload();

		$event = new Payment_Capture_Denied( $this->build_rest_request( $payload ) );
		$event->handle();

		// Refresh payment object.
		$payment = edd_get_payment( $this->payment->ID );
		$this->assertEquals( 'failed', $payment->status );
	}

}
