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

use EDD\Gateways\PayPal\MerchantAccount;
use EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Completed;
use EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Denied;
use EDD\Gateways\PayPal\Webhooks\Events\Webhook_Event;
use EDD\Gateways\PayPal\Webhooks\Webhook_Handler;
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
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Completed::process_event
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
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Completed::handle
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
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Completed::handle
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
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Completed::get_payment_from_capture
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
	 * @covers \EDD\Gateways\PayPal\Webhooks\Events\Payment_Capture_Denied::handle
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

	/**
	 * @covers \EDD\Gateways\PayPal\MerchantAccount::__construct()
	 * @expectedException \EDD\Gateways\PayPal\Exceptions\MissingMerchantDetails
	 */
	public function test_merchant_account_missing_merchant_id_throws_exception() {
		$merchant = new MerchantAccount( array(
			'random_field'
		) );

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( '\EDD\Gateways\PayPal\Exceptions\MissingMerchantDetails' );
		}

		$merchant->validate();
	}

	/**
	 * @covers \EDD\Gateways\PayPal\MerchantAccount::__construct()
	 * @expectedException \EDD\Gateways\PayPal\Exceptions\InvalidMerchantDetails
	 */
	public function test_merchant_account_missing_required_fields_throws_exception() {
		$merchant = new MerchantAccount( array(
			'merchant_id' => 'merchant-123'
		) );

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( '\EDD\Gateways\PayPal\Exceptions\InvalidMerchantDetails' );
		}

		$merchant->validate();
	}

	/**
	 * @covers \EDD\Gateways\PayPal\MerchantAccount::is_account_ready
	 */
	public function test_merchant_account_not_ready_email_not_confirmed() {
		$merchant = new MerchantAccount( array(
			'merchant_id'             => 123,
			'payments_receivable'     => true,
			'primary_email_confirmed' => false,
			'products'                => array()
		) );

		$this->assertFalse( $merchant->is_account_ready() );

		$this->assertTrue( in_array( 'primary_email_confirmed', $merchant->get_errors()->get_error_codes() ) );
	}

	/**
	 * @covers \EDD\Gateways\PayPal\MerchantAccount::is_account_ready
	 */
	public function test_merchant_account_not_ready_payments_not_receivable() {
		$merchant = new MerchantAccount( array(
			'merchant_id'             => 123,
			'payments_receivable'     => false,
			'primary_email_confirmed' => true,
			'products'                => array()
		) );

		$this->assertFalse( $merchant->is_account_ready() );

		$this->assertTrue( in_array( 'payments_receivable', $merchant->get_errors()->get_error_codes() ) );
	}

	/**
	 * @covers \EDD\Gateways\PayPal\MerchantAccount::is_account_ready
	 */
	public function test_merchant_account_is_ready() {
		$merchant = new MerchantAccount( array(
			'merchant_id'             => 123,
			'payments_receivable'     => true,
			'primary_email_confirmed' => true,
			'products'                => array()
		) );

		$this->assertTrue( $merchant->is_account_ready() );
	}

	/**
	 * @covers ::\EDD\Gateways\PayPal\_is_item_total_mismatch()
	 */
	public function test_item_total_mismatch_detected() {
		$api_response = '{"name":"UNPROCESSABLE_ENTITY","details":[{"field":"\/purchase_units\/@reference_id==\'f4b51d81164ab4338fb9aa0911c94701\'\/amount\/breakdown\/item_total\/value","value":"0","issue":"ITEM_TOTAL_MISMATCH","description":"Should equal sum of (unit_amount * quantity) across all items for a given purchase_unit"}],"message":"The requested action could not be performed, semantically incorrect, or failed business validation.","debug_id":"123456789","links":[{"href":"https:\/\/developer.paypal.com\/docs\/api\/orders\/v2\/#error-ITEM_TOTAL_MISMATCH","rel":"information_link","method":"GET"}]}';

		$this->assertTrue( \EDD\Gateways\PayPal\_is_item_total_mismatch( json_decode( $api_response ) ) );
	}

	/**
	 * @covers ::\EDD\Gateways\PayPal\_is_item_total_mismatch()
	 */
	public function test_item_total_mismatch_not_detected_on_success() {
		$api_response = '{"id":"5GY4996685035442T","status":"CREATED","links":[{"href":"https:\/\/api.sandbox.paypal.com\/v2\/checkout\/orders\/5GY4996685035442T","rel":"self","method":"GET"},{"href":"https:\/\/www.sandbox.paypal.com\/checkoutnow?token=5GY4996685035442T","rel":"approve","method":"GET"},{"href":"https:\/\/api.sandbox.paypal.com\/v2\/checkout\/orders\/5GY4996685035442T","rel":"update","method":"PATCH"},{"href":"https:\/\/api.sandbox.paypal.com\/v2\/checkout\/orders\/5GY4996685035442T\/capture","rel":"capture","method":"POST"}]}';

		$this->assertFalse( \EDD\Gateways\PayPal\_is_item_total_mismatch( json_decode( $api_response ) ) );
	}

	/**
	 * Product costs $10.
	 * Two added to cart (quantity: 2)
	 * 50% discount applied.
	 *
	 * @covers ::\EDD\Gateways\PayPal\get_order_purchase_units()
	 */
	public function test_purchase_units_with_quantities_and_discount() {
		$purchase_data = array(
			'subtotal'     => 20.00,
			'discount'     => 10.00,
			'tax'          => 0.00,
			'price'        => 10.00,
			'cart_details' => array(
				array(
					'id'         => 1,
					'item_price' => 10.00,
					'quantity'   => 2, // Quantity 2
					'discount'   => 10.00, // With 50% discount
					'subtotal'   => 20.00,
					'tax'        => 0.00,
					'price'      => 10.00
				)
			)
		);

		$payment_args = array(
			'purchase_key' => '123'
		);

		$expected = array(
			'reference_id' => '123',
			'amount'       => array(
				'currency_code' => 'USD',
				'value'         => '10',
				'breakdown'     => array(
					'item_total' => array(
						'currency_code' => 'USD',
						'value'         => '10',
					)
				),
			),
			'custom_id'    => 1,
			'items'        => array(
				array(
					'name'        => '1',
					'quantity'    => 2,
					'unit_amount' => array(
						'currency_code' => 'USD',
						'value'         => '5.00'
					)
				)
			)
		);

		$actual = \EDD\Gateways\PayPal\get_order_purchase_units( 1, $purchase_data, $payment_args );

		$this->assertEqualSetsWithIndex( $expected, $actual[0] );
	}

	/**
	 * Product costs $10.
	 * $5 "handling fee" applied to the product.
	 *
	 * @covers ::\EDD\Gateways\PayPal\get_order_purchase_units()
	 */
	public function test_purchase_units_with_positive_fee() {
		$purchase_data = array(
			'subtotal'     => 15.00,
			'discount'     => 0,
			'tax'          => 0.00,
			'price'        => 15.00,
			'cart_details' => array(
				array(
					'id'         => 1,
					'item_price' => 10.00,
					'quantity'   => 1,
					'discount'   => 0.00,
					'subtotal'   => 15.00,
					'tax'        => 0.00,
					'fees'       => array(
						'handling' => array(
							'amount'      => 5.00,
							'label'       => 'Handling Fee',
							'no_tax'      => 0,
							'type'        => 'fee',
							'download_id' => 1,
						),
					),
					'price'      => 15.00,
				),
			),
		);

		$payment_args = array(
			'purchase_key' => '123',
		);

		$expected = array(
			'reference_id' => '123',
			'amount'       => array(
				'currency_code' => 'USD',
				'value'         => '15',
				'breakdown'     => array(
					'item_total' => array(
						'currency_code' => 'USD',
						'value'         => '15',
					),
				),
			),
			'custom_id'    => 1,
			'items'        => array(
				array(
					'name'        => '1',
					'quantity'    => 1,
					'unit_amount' => array(
						'currency_code' => 'USD',
						'value'         => '15.00',
					),
				),
			),
		);

		$actual = \EDD\Gateways\PayPal\get_order_purchase_units( 1, $purchase_data, $payment_args );

		$this->assertEqualSetsWithIndex( $expected, $actual[0] );
	}

}
