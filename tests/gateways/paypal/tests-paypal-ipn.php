<?php
/**
 * PayPal IPN Fallback Tests
 *
 * Tests the fallback behavior when PayPal's IPN verification endpoint is unavailable.
 *
 * Note: Subscription renewal tests require EDD Recurring to be active.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.6.3
 */

namespace EDD\Tests\Gateways\PayPal;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for PayPal IPN fallback functionality.
 *
 * @group gateways
 * @group paypal
 * @group paypal-ipn
 */
class IPN extends EDD_UnitTestCase {

	const PAYPAL_SUB_ID   = 'I-1BA622H988BX';
	const PAYPAL_TRANS_ID = '58E16500406047637';
	const PAYPAL_ORDER_TRANS_ID = '27M47624FP291604U'; // 17 chars, valid format

	/**
	 * @var \EDD_Subscription
	 */
	protected $subscription;

	/**
	 * @var \EDD\Orders\Order
	 */
	protected $order;

	/**
	 * @var array
	 */
	protected $postbacks = array();

	/**
	 * @var array
	 */
	protected $postback_urls = array();

	public static function setUpBeforeClass(): void {
		// Set up PayPal credentials for both modes
		// Tests will control test_mode setting individually as needed
		edd_update_option( 'paypal_sandbox_client_id', 'test_client_id' );
		edd_update_option( 'paypal_sandbox_client_secret', 'test_client_secret' );
		edd_update_option( 'paypal_live_client_id', 'test_client_id' );
		edd_update_option( 'paypal_live_client_secret', 'test_client_secret' );
	}

	/**
	 * Sets up a payment for IPN testing
	 */
	public function setUp(): void {
		parent::setUp();

		// Remove the filter that blocks HTTP requests in tests.
		// We'll add our own filter to mock PayPal responses specifically.
		remove_all_filters( 'pre_http_request' );

		// Enable PayPal Commerce.
		edd_update_option( 'gateways', array( 'paypal_commerce' => 1 ) );

		// Create a basic order for testing.
		$this->create_test_order();
	}

	public function tearDown(): void {
		// Clean up globals
		$_GET  = array();
		$_POST = array();

		edd_delete_option( 'test_mode' );

		// Remove any filters we added
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'edd_is_gateway_active', array( $this, 'mock_gateway_active' ), 10 );

		parent::tearDown();
	}

	/**
	 * Creates a basic test order
	 */
	private function create_test_order() {
		// Create a simple order.
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array(
				'gateway' => 'paypal_commerce',
			)
		);

		$this->order = edd_get_order( $order_id );

		// Ensure the gateway is set (sometimes the helper doesn't persist it properly).
		edd_update_order(
			$this->order->id,
			array(
				'gateway' => 'paypal_commerce',
				'status'  => 'complete',
			)
		);

		// Refresh the order object.
		$this->order = edd_get_order( $this->order->id );

		// Create a transaction record (required for get_order_id_from_transaction_id to work).
		edd_add_order_transaction(
			array(
				'object_id'      => $this->order->id,
				'object_type'    => 'order',
				'transaction_id' => self::PAYPAL_ORDER_TRANS_ID,
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => $this->order->total,
			)
		);
	}

	/**
	 * Creates a test subscription (requires EDD Recurring)
	 */
	private function create_test_subscription() {
		if ( ! class_exists( 'EDD_Recurring' ) || ! class_exists( 'EDD_Subscription' ) ) {
			$this->markTestSkipped( 'EDD Recurring is required for this test.' );
		}

		// Create order if not already created.
		if ( ! $this->order ) {
			$this->create_test_order();
		}

		// Get the download from the order.
		$order_items = $this->order->get_items();
		$order_item  = reset( $order_items );
		$download    = edd_get_download( $order_item->product_id );

		// Create subscription.
		$this->subscription = new \EDD_Subscription();
		$expiration         = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + ( DAY_IN_SECONDS * 7 ) );
		$args               = array(
			'customer_id'       => $this->order->customer_id,
			'period'            => 'month',
			'initial_amount'    => 20.00,
			'recurring_amount'  => 20.00,
			'bill_times'        => 0,
			'parent_payment_id' => $this->order->id,
			'product_id'        => $download->ID,
			'expiration'        => $expiration,
			'profile_id'        => self::PAYPAL_SUB_ID,
			'status'            => 'active',
		);
		$this->subscription->create( $args );
	}

	/**
	 * Simulates an IPN request by setting up $_GET and $_POST globals
	 *
	 * @param array $ipn_data The IPN data to post
	 */
	private function simulate_ipn_request( $ipn_data ) {
		$_GET['edd-listener'] = 'eppe';

		foreach ( $ipn_data as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Returns mock IPN data for a renewal payment
	 *
	 * @param array $args Optional args to override defaults
	 * @return array
	 */
	private function get_renewal_ipn_data( $args = array() ) {
		$defaults = array(
			'txn_type'              => 'recurring_payment',
			'payment_status'        => 'Completed',
			'txn_id'                => self::PAYPAL_TRANS_ID,
			'recurring_payment_id'  => self::PAYPAL_SUB_ID,
			'mc_gross'              => '20.00',
			'mc_currency'           => 'USD',
			'payment_date'          => date( 'H:i:s M d, Y', strtotime( '+1 month' ) ), // Set to next month to ensure it's not the initial payment
			'receiver_email'        => 'test@example.com',
			'payer_email'           => 'customer@example.com',
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Mock filter to simulate PayPal verification returning VERIFIED
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return array Mocked response
	 */
	public function mock_paypal_verified( $response, $args, $url ) {
		if ( strpos( $url, 'paypal.com' ) !== false && strpos( $url, 'webscr' ) !== false ) {
			return array(
				'headers'  => array(),
				'body'     => 'VERIFIED',
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
			);
		}
		return $response;
	}

	/**
	 * Mock filter to simulate PayPal verification returning INVALID
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return array Mocked response
	 */
	public function mock_paypal_invalid( $response, $args, $url ) {
		if ( strpos( $url, 'paypal.com' ) !== false && strpos( $url, 'webscr' ) !== false ) {
			return array(
				'headers'  => array(),
				'body'     => 'INVALID',
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
			);
		}
		return $response;
	}

	/**
	 * Mock filter to simulate PayPal verification endpoint returning 503 error
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return array Mocked response
	 */
	public function mock_paypal_503_error( $response, $args, $url ) {
		if ( strpos( $url, 'paypal.com' ) !== false && strpos( $url, 'webscr' ) !== false ) {
			return array(
				'headers'  => array(),
				'body'     => 'Service Temporarily Unavailable',
				'response' => array(
					'code'    => 503,
					'message' => 'Service Unavailable',
				),
			);
		}
		return $response;
	}

	/**
	 * Mock filter to simulate PayPal verification endpoint connection error
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return \WP_Error Mocked error response
	 */
	public function mock_paypal_connection_error( $response, $args, $url ) {
		if ( strpos( $url, 'paypal.com' ) !== false && strpos( $url, 'webscr' ) !== false ) {
			return new \WP_Error( 'http_request_failed', 'Could not connect to PayPal' );
		}
		return $response;
	}

	/**
	 * Returns mock IPN data for a dispute
	 *
	 * @param array $args Optional args to override defaults
	 * @return array
	 */
	private function get_dispute_ipn_data( $args = array() ) {
		$defaults = array(
			'txn_type'     => 'new_case',
			'txn_id'       => self::PAYPAL_ORDER_TRANS_ID,
			'case_id'      => 'PP-R-123456',
			'reason_code'  => 'non_receipt',
			'case_type'    => 'chargeback',
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Returns mock IPN data for a refund
	 *
	 * @param array $args Optional args to override defaults
	 * @return array
	 */
	private function get_refund_ipn_data( $args = array() ) {
		$defaults = array(
			'txn_type'       => 'adjustment',
			'payment_status' => 'refunded',
			'parent_txn_id'  => self::PAYPAL_ORDER_TRANS_ID,
			'txn_id'         => '9XH123456A7890123', // Different valid PayPal transaction ID
			'mc_gross'       => '-20.00', // PayPal sends refunded amounts as negative values.
			'mc_currency'    => 'USD',
			'reason_code'    => 'refund',
			'ipn_track_id'   => 'test_ipn_track_123',
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Test: IPN logs dispute when new_case received
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::log_dispute
	 */
	public function test_ipn_logs_dispute() {
		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Mark order as complete (disputes can happen on completed orders)
		edd_update_order_status( $this->order->id, 'complete' );

		// Set up IPN request for dispute
		$this->simulate_ipn_request( $this->get_dispute_ipn_data() );

		// Order should not be on_hold yet
		$order = edd_get_order( $this->order->id );
		$this->assertEquals( 'complete', $order->status );

		$this->process_ipn();

		// Refresh order
		$order = edd_get_order( $this->order->id );

		// Order should now be on_hold
		$this->assertEquals( 'on_hold', $order->status );

		// Check for dispute note
		$notes = edd_get_notes( array(
			'object_type' => 'order',
			'object_id'   => $order->id,
		) );

		$note_found = false;
		foreach ( $notes as $note ) {
			if ( strpos( $note->content, 'PayPal transaction has been disputed' ) !== false ) {
				$note_found = true;
				break;
			}
		}

		$this->assertTrue( $note_found, 'Expected dispute note not found' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: the dispute note carries no markup from the notification body
	 *
	 * The case ID and reason code go into the note text as the request sent them.
	 *
	 * The payload is quoted and slashed because Query::validate_item() strips slashes before
	 * it calls the column's validate callback.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::log_dispute
	 */
	public function test_dispute_note_carries_no_markup() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'complete' );

		$this->simulate_ipn_request(
			$this->get_dispute_ipn_data(
				array(
					'case_id'     => wp_slash( 'PP-R-XSS<img src="x" onerror="alert(1)">' ),
					'reason_code' => wp_slash( 'non_receipt<svg onload="alert(2)">' ),
				)
			)
		);

		$this->process_ipn();

		// The order is put on hold either way, so assert that separately from the note text.
		$order = edd_get_order( $this->order->id );
		$this->assertEquals( 'on_hold', $order->status );

		$notes = edd_get_notes(
			array(
				'object_type' => 'order',
				'object_id'   => $order->id,
			)
		);

		$dispute_note = false;
		foreach ( $notes as $note ) {
			if ( false !== strpos( $note->content, 'PayPal transaction has been disputed' ) ) {
				$dispute_note = $note;
				break;
			}
		}

		$this->assertNotFalse( $dispute_note, 'Expected dispute note not found' );
		$this->assertStringNotContainsString( '<img', $dispute_note->content );
		$this->assertStringNotContainsString( 'onerror', $dispute_note->content );
		$this->assertStringNotContainsString( '<svg', $dispute_note->content );
		$this->assertStringNotContainsString( 'onload', $dispute_note->content );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN processes full refund
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_ipn_processes_full_refund() {
		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Mark order as complete
		edd_update_order_status( $this->order->id, 'complete' );

		// Verify the transaction record exists and can be looked up
		$lookup_order_id = edd_get_order_id_from_transaction_id( self::PAYPAL_ORDER_TRANS_ID );
		$this->assertEquals( $this->order->id, $lookup_order_id, 'Transaction lookup failed - order not found by transaction ID' );

		// Set up IPN request for refund (full amount)
		$ipn_data = $this->get_refund_ipn_data( array(
			'mc_gross' => -1 * $this->order->total,
		) );
		$this->simulate_ipn_request( $ipn_data );

		$this->process_ipn();

		// Refresh order
		$order = edd_get_order( $this->order->id );

		// Order should now be refunded
		$this->assertEquals( 'refunded', $order->status );

		// Check for refund note
		$notes = edd_get_notes( array(
			'object_type' => 'order',
			'object_id'   => $order->id,
		) );

		$note_found = false;
		foreach ( $notes as $note ) {
			if ( strpos( $note->content, 'Full refund processed in PayPal (IPN)' ) !== false ) {
				$note_found = true;
				break;
			}
		}

		$this->assertTrue( $note_found, 'Expected refund note not found' );

		$refunds = edd_get_orders(
			array(
				'type'   => 'refund',
				'parent' => $order->id,
			)
		);
		$this->assertCount( 1, $refunds, 'A full refund should create one refund order.' );

		$refund_transaction = edd_get_order_transaction_by( 'transaction_id', '9XH123456A7890123' );
		$this->assertEquals( $refunds[0]->id, $refund_transaction->object_id, 'The refund transaction should belong to the refund order.' );
		$this->assertEquals( edd_negate_amount( $order->total ), $refund_transaction->total, 'The refund transaction should be negative, matching refunds issued from EDD.' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN processes partial refund
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_ipn_processes_partial_refund() {
		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Mark order as complete
		edd_update_order_status( $this->order->id, 'complete' );

		// Verify transaction lookup works
		$lookup_order_id = edd_get_order_id_from_transaction_id( self::PAYPAL_ORDER_TRANS_ID );
		$this->assertEquals( $this->order->id, $lookup_order_id, 'Transaction lookup failed' );

		// Set up IPN request for partial refund (half the amount)
		$ipn_data = $this->get_refund_ipn_data( array(
			'mc_gross' => -1 * $this->order->total / 2,
		) );
		$this->simulate_ipn_request( $ipn_data );

		$this->process_ipn();

		// Refresh order
		$order = edd_get_order( $this->order->id );

		// Order should be partially_refunded
		$this->assertEquals( 'partially_refunded', $order->status );

		// Check for refund note
		$notes = edd_get_notes( array(
			'object_type' => 'order',
			'object_id'   => $order->id,
		) );

		$partial_refund_note = '';
		foreach ( $notes as $note ) {
			if ( strpos( $note->content, 'Partial refund processed in PayPal (IPN)' ) !== false ) {
				$partial_refund_note = $note->content;
				break;
			}
		}

		$this->assertNotEmpty( $partial_refund_note, 'Expected partial refund note not found' );

		// PayPal sends a negative mc_gross, but the note must show the amount the customer got back.
		$expected_amount = edd_currency_filter( edd_format_amount( $this->order->total / 2 ), 'USD' );
		$this->assertStringContainsString(
			'Amount: ' . html_entity_decode( $expected_amount ) . ';',
			html_entity_decode( $partial_refund_note )
		);

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A refund IPN for an order the webhook already refunded leaves the order refunded.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 */
	public function test_ipn_refund_leaves_already_refunded_order_refunded() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'refunded' );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => -1 * $this->order->total,
				)
			)
		);

		$this->process_ipn();

		$order = edd_get_order( $this->order->id );
		$this->assertEquals( 'refunded', $order->status );

		$ipn_refund_notes = edd_get_notes(
			array(
				'object_type'    => 'order',
				'object_id'      => $order->id,
				'search'         => 'refund processed in PayPal (IPN)',
				'search_columns' => array( 'content' ),
			)
		);
		$this->assertEmpty( $ipn_refund_notes, 'An already refunded order should not be refunded again.' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A full refund IPN on an order that cannot be refunded in EDD still marks it refunded.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_full_refund_ipn_on_revoked_order_sets_refunded_without_refund_transaction() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'revoked' );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => -1 * $this->order->total,
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'refunded', edd_get_order( $this->order->id )->status );
		$this->assertEmpty(
			edd_get_order_transaction_by( 'transaction_id', '9XH123456A7890123' ),
			'With no refund order created, there is nothing to attach the refund transaction to.'
		);

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A refund IPN in a different currency from the order is rejected.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 */
	public function test_refund_ipn_with_mismatched_currency_does_not_refund_order() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross'    => -1 * $this->order->total,
					'mc_currency' => 'USD' === $this->order->currency ? 'EUR' : 'USD',
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'complete', edd_get_order( $this->order->id )->status );
		$this->assertEmpty(
			edd_get_orders(
				array(
					'type'   => 'refund',
					'parent' => $this->order->id,
				)
			)
		);

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A PayPal refund matching EDD's unrefunded balance is still a partial refund.
	 *
	 * The amount alone cannot tell it apart from PayPal mirroring the refund EDD already recorded,
	 * and a wrong full refund writes a refund order for items the customer still paid for.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_refund_ipn_for_remaining_balance_after_edd_partial_refund_sets_partially_refunded() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		$refunded_item = $this->order->items[0];
		edd_refund_order(
			$this->order->id,
			array(
				array(
					'order_item_id' => $refunded_item->id,
					'subtotal'      => $refunded_item->subtotal,
					'tax'           => $refunded_item->tax,
					'total'         => $refunded_item->total,
				)
			),
			array()
		);
		$this->assertEquals( 'partially_refunded', edd_get_order( $this->order->id )->status );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => -1 * ( $this->order->total - $refunded_item->total ),
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'partially_refunded', edd_get_order( $this->order->id )->status );
		$this->assertCount(
			1,
			edd_get_orders(
				array(
					'type'   => 'refund',
					'parent' => $this->order->id,
				)
			),
			'Only the refund EDD recorded itself should exist.'
		);

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A refund IPN for a zero amount records nothing.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 */
	public function test_refund_ipn_with_zero_amount_leaves_order_complete() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => '-0.00',
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'complete', edd_get_order( $this->order->id )->status );

		$ipn_refund_notes = edd_get_notes(
			array(
				'object_type'    => 'order',
				'object_id'      => $this->order->id,
				'search'         => 'refund processed in PayPal (IPN)',
				'search_columns' => array( 'content' ),
			)
		);
		$this->assertEmpty( $ipn_refund_notes );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A full refund matches an order total stored with more precision than the currency has.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_full_refund_ipn_matching_rounded_total_sets_refunded() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Tax math can leave sub-cent precision; PayPal only ever charges the rounded amount.
		edd_update_order( $this->order->id, array( 'total' => 10.8249 ) );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => '-10.82',
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'refunded', edd_get_order( $this->order->id )->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: The refund amount comes from mc_gross even when the IPN also carries an amount field.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 * @covers \EDD\Gateways\PayPal\Traits\Refunds::process_refund
	 */
	public function test_partial_refund_ipn_with_amount_field_sizes_refund_from_mc_gross() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'mc_gross' => -1 * $this->order->total / 2,
					'amount'   => $this->order->total,
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'partially_refunded', edd_get_order( $this->order->id )->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN handles payment reversal
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 */
	public function test_ipn_handles_reversal() {
		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Mark order as complete
		edd_update_order_status( $this->order->id, 'complete' );

		// Verify transaction lookup works
		$lookup_order_id = edd_get_order_id_from_transaction_id( self::PAYPAL_ORDER_TRANS_ID );
		$this->assertEquals( $this->order->id, $lookup_order_id, 'Transaction lookup failed' );

		// Set up IPN request for reversal
		$ipn_data = $this->get_refund_ipn_data( array(
			'payment_status' => 'reversed',
			'reason_code'    => 'buyer_complaint',
		) );
		$this->simulate_ipn_request( $ipn_data );

		$this->process_ipn();

		// Refresh order
		$order = edd_get_order( $this->order->id );

		// Order should be on_hold due to reversal
		$this->assertEquals( 'on_hold', $order->status );

		// Check for reversal note
		$notes = edd_get_notes( array(
			'object_type' => 'order',
			'object_id'   => $order->id,
		) );

		$note_found = false;
		foreach ( $notes as $note ) {
			if ( strpos( $note->content, 'Reversal processed in PayPal (IPN)' ) !== false ) {
				$note_found = true;
				break;
			}
		}

		$this->assertTrue( $note_found, 'Expected reversal note not found' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: A reversal IPN on a refunded order leaves it refunded, since the money is already back with the customer.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_refunds
	 */
	public function test_reversal_ipn_on_refunded_order_adds_no_note() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'refunded' );

		$this->simulate_ipn_request(
			$this->get_refund_ipn_data(
				array(
					'payment_status' => 'reversed',
					'reason_code'    => 'buyer_complaint',
				)
			)
		);

		$this->process_ipn();

		$this->assertEquals( 'refunded', edd_get_order( $this->order->id )->status );

		$reversal_notes = edd_get_notes(
			array(
				'object_type'    => 'order',
				'object_id'      => $this->order->id,
				'search'         => 'Reversal processed in PayPal (IPN)',
				'search_columns' => array( 'content' ),
			)
		);
		$this->assertEmpty( $reversal_notes );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: Normal IPN processing when verification succeeds (subscription renewal)
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::is_verified
	 * @covers \EDD\Gateways\PayPal\IPN::process_recurring_payment
	 */
	public function test_ipn_renewal_processes_when_verification_succeeds() {
		// Create subscription for this test
		$this->create_test_subscription();
		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment at this point
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		// Process IPN (will die, so we catch it)
		ob_start();
		try {
			new \EDD\Gateways\PayPal\IPN( $_POST );
		} catch ( \Exception $e ) {
			// Expected - IPN processing calls die()
		}
		ob_end_clean();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now have 2 payments and be renewed
		$this->assertEquals( 2, $subscription->get_total_payments() );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN rejected when verification returns INVALID (subscription renewal)
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::is_verified
	 */
	public function test_ipn_rejected_when_verification_returns_invalid() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock INVALID response
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment at this point
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should still have only 1 payment (not processed)
		$this->assertEquals( 1, $subscription->get_total_payments() );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10 );
	}

	/**
	 * Test: IPN creates on-hold renewal when verification unavailable and fallback is create_on_hold
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::handle_verification_unavailable
	 * @covers \EDD\Gateways\PayPal\IPN::create_on_hold_renewal
	 */
	public function test_ipn_creates_on_hold_renewal_when_verification_unavailable() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Set fallback strategy to create_on_hold
		add_filter( 'edd_paypal_verification_fallback', array( $this, 'create_on_hold_fallback' ) );

		// Mock 503 error
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_503_error' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		// Process IPN - capture debug output
		ob_start();
		$ipn_output = '';
		try {
			new \EDD\Gateways\PayPal\IPN( $_POST );
		} catch ( \Exception $e ) {
			// Expected
		}
		ob_end_clean();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now have 2 payments
		$this->assertEquals( 2, $subscription->get_total_payments(), 'Expected renewal payment to be created' );

		// Get the renewal order - use direct query since get_renewal_orders() filters by status
		// and won't return on_hold orders
		$renewal_orders = edd_get_orders( array(
			'type'              => 'sale',
			'parent'            => $subscription->parent_payment_id,
			'status__not_in'    => array( 'refunded', 'abandoned', 'failed' ),
			'number'            => 100,
			'order'             => 'ASC',
		) );

		$this->assertNotEmpty( $renewal_orders, 'Expected at least one renewal order' );

		$renewal_order = end( $renewal_orders );
		$this->assertInstanceOf( '\\EDD\\Orders\\Order', $renewal_order, 'Expected renewal order to be an Order instance' );

		// Renewal should be on_hold status
		$this->assertEquals( 'on_hold', $renewal_order->status, 'Expected renewal order to have on_hold status' );

		// Check for note about verification failure
		$notes = edd_get_notes( array(
			'object_type' => 'order',
			'object_id'   => $renewal_order->id,
		) );

		$note_found = false;
		foreach ( $notes as $note ) {
			if ( strpos( $note->content, 'PayPal verification was unavailable' ) !== false ) {
				$note_found = true;
				break;
			}
		}

		$this->assertTrue( $note_found, 'Expected note about verification failure not found' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_503_error' ), 10 );
		remove_filter( 'edd_paypal_verification_fallback', array( $this, 'create_on_hold_fallback' ) );
	}

	/**
	 * Test: IPN processes with validation when fallback is process_with_validation
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::handle_verification_unavailable
	 * @covers \EDD\Gateways\PayPal\Traits\Validate::validate_ipn_data
	 */
	public function test_ipn_processes_with_validation_when_verification_unavailable() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Processing a notification PayPal did not confirm is opt-in; the default is to refuse.
		add_filter( 'edd_paypal_verification_fallback', array( $this, 'process_with_validation_fallback' ) );

		// Mock connection error
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_connection_error' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now have 2 payments and be renewed
		$this->assertEquals( 2, $subscription->get_total_payments() );

		// Get the renewal order
		$renewal_orders = $subscription->get_renewal_orders();
		$renewal_order  = end( $renewal_orders );

		$this->assertInstanceOf( '\\EDD\\Orders\\Order', $renewal_order );

		// Renewal should be edd_subscription status (normal processing)
		$this->assertEquals( 'edd_subscription', $renewal_order->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_connection_error' ), 10 );
		remove_filter( 'edd_paypal_verification_fallback', array( $this, 'process_with_validation_fallback' ) );
	}

	/**
	 * Test: IPN rejected when fallback is reject
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::handle_verification_unavailable
	 */
	public function test_ipn_rejected_when_fallback_is_reject() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Set fallback strategy to reject
		add_filter( 'edd_paypal_verification_fallback', array( $this, 'reject_fallback' ) );

		// Mock connection error
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_connection_error' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should still have only 1 payment (rejected)
		$this->assertEquals( 1, $subscription->get_total_payments() );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_connection_error' ), 10 );
		remove_filter( 'edd_paypal_verification_fallback', array( $this, 'reject_fallback' ) );
	}

	/**
	 * Test: Amount validation rejects mismatched amounts (subscription renewal)
	 *
	 * @covers \EDD\Gateways\PayPal\Traits\Validate::validate_subscription_amount
	 */
	public function test_ipn_rejected_when_amount_mismatch() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request with wrong amount
		$ipn_data = $this->get_renewal_ipn_data( array(
			'mc_gross' => '10.00', // Wrong amount (should be 20.00)
		) );
		$this->simulate_ipn_request( $ipn_data );

		// Subscription should have 1 payment
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		// Process IPN (should fail validation)
		ob_start();
		try {
			new \EDD\Gateways\PayPal\IPN( $_POST );
		} catch ( \Exception $e ) {
			// Expected - should die due to validation failure
		}
		ob_end_clean();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should still have only 1 payment (rejected due to amount mismatch)
		$this->assertEquals( 1, $subscription->get_total_payments() );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN cancels subscription on cancel transaction types
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_recurring
	 * @covers \EDD\Gateways\PayPal\IPN::cancel
	 */
	public function test_ipn_cancels_subscription() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request for cancellation
		$ipn_data = $this->get_renewal_ipn_data( array(
			'txn_type' => 'recurring_payment_profile_cancel',
		) );
		$this->simulate_ipn_request( $ipn_data );

		// Subscription should be active
		$this->assertEquals( 'active', $this->subscription->status );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now be cancelled--in tests, it's needs_attention due to not being able to confirm.
		$this->assertEquals( 'needs_attention', $subscription->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN marks subscription as failing on failed payment
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_recurring
	 */
	public function test_ipn_marks_subscription_failing() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request for failed payment
		$ipn_data = $this->get_renewal_ipn_data( array(
			'txn_type' => 'recurring_payment_failed',
		) );
		$this->simulate_ipn_request( $ipn_data );

		// Subscription should be active
		$this->assertEquals( 'active', $this->subscription->status );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now be failing
		$this->assertEquals( 'failing', $subscription->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN completes subscription on expiration
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::maybe_handle_recurring
	 * @covers \EDD\Gateways\PayPal\IPN::complete
	 */
	public function test_ipn_completes_subscription() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request for expiration
		$ipn_data = $this->get_renewal_ipn_data( array(
			'txn_type' => 'recurring_payment_expired',
		) );
		$this->simulate_ipn_request( $ipn_data );

		// Subscription should be active
		$this->assertEquals( 'active', $this->subscription->status );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now be completed
		$this->assertEquals( 'completed', $subscription->status );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN rejects duplicate transaction IDs
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::process_recurring_payment
	 */
	public function test_ipn_rejects_duplicate_transaction() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should now have 2 payments
		$this->assertEquals( 2, $subscription->get_total_payments() );

		// Try to process the SAME IPN again with same transaction ID
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should STILL have only 2 payments (duplicate was rejected)
		$this->assertEquals( 2, $subscription->get_total_payments(), 'Duplicate transaction should not create a third payment' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Test: IPN handles exception from duplicate prevention snippet gracefully
	 *
	 * Simulates a customer using a transient lock snippet to prevent duplicate renewals.
	 * Verifies that:
	 * 1. Exception from filter is caught and logged properly
	 * 2. No renewal order is created when lock exists
	 * 3. Subscription status remains unchanged
	 * 4. IPN returns proper response (doesn't break)
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::process_recurring_payment
	 */
	public function test_ipn_handles_duplicate_prevention_snippet() {
		// Create subscription for this test
		$this->create_test_subscription();

		// Mock successful verification
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10, 3 );

		// Add the customer's duplicate prevention snippet
		add_filter( 'edd_recurring_add_payment_pre_args', array( $this, 'duplicate_prevention_snippet' ), 10, 2 );

		// Set up IPN request
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );

		// Subscription should have 1 payment (the initial one)
		$this->assertEquals( 1, $this->subscription->get_total_payments() );

		$initial_status = $this->subscription->status;

		// Process IPN - this should trigger the exception from the filter
		$this->process_ipn();

		// Refresh subscription
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// Subscription should STILL have only 1 payment (renewal was prevented by exception)
		$this->assertEquals( 1, $subscription->get_total_payments(), 'Renewal should be prevented when snippet throws exception' );

		// Subscription status should be unchanged
		$this->assertEquals( $initial_status, $subscription->status, 'Subscription status should remain unchanged when renewal is prevented' );

		// Remove the filter to test normal processing works after lock expires
		remove_filter( 'edd_recurring_add_payment_pre_args', array( $this, 'duplicate_prevention_snippet' ), 10 );

		// Process the same IPN again (simulating lock expiration)
		$this->simulate_ipn_request( $this->get_renewal_ipn_data() );
		$this->process_ipn();

		// Refresh subscription again
		$subscription = new \EDD_Subscription( $this->subscription->id );

		// NOW the renewal should be created
		$this->assertEquals( 2, $subscription->get_total_payments(), 'Renewal should be created when snippet is not active' );

		// Check that the renewal has the correct meta
		$child_orders = edd_get_orders( array(
			'parent' => $this->order->id,
			'number' => 1,
		) );
		$this->assertNotEmpty( $child_orders, 'Renewal order should exist' );
		$renewal_order = reset( $child_orders );
		$this->assertEquals( 'paypal_commerce_ipn', edd_get_order_meta( $renewal_order->id, 'renewal_handler', true ), 'Renewal should be marked as processed by IPN' );

		remove_filter( 'pre_http_request', array( $this, 'mock_paypal_verified' ), 10 );
	}

	/**
	 * Simulates the customer's duplicate prevention snippet
	 *
	 * This filter callback simulates a transient lock being active,
	 * throwing an exception to prevent duplicate renewal processing.
	 *
	 * @param array             $args         Payment arguments
	 * @param \EDD_Subscription $subscription Subscription object
	 * @return array Modified arguments (never reached due to exception)
	 * @throws \Exception When lock is detected
	 */
	public function duplicate_prevention_snippet( $args, $subscription ) {
		if ( empty( $args['transaction_id'] ) ) {
			return $args;
		}

		$lock_key = 'edd_renewal_lock_' . md5( $args['transaction_id'] );

		// For testing purposes, always throw on first attempt
		// (simulating lock existing)
		if ( false === get_transient( $lock_key ) ) {
			// Set the lock
			set_transient( $lock_key, 1, 60 );
			// Throw to simulate lock already existing
			throw new \Exception( 'Duplicate renewal payment prevented by lock for transaction: ' . $args['transaction_id'] );
		}

		return $args;
	}

	public function create_on_hold_fallback() {
		return 'create_on_hold';
	}

	public function reject_fallback() {
		return 'reject';
	}

	public function process_with_validation_fallback() {
		return 'process_with_validation';
	}

	/**
	 * Test: test mode still verifies the notification with PayPal
	 *
	 * Test mode changes which endpoint verifies a notification, not whether one is verified.
	 * `edd_get_paypal_redirect()` already resolves to the sandbox, so the postback still has
	 * somewhere to go.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::is_verified
	 */
	public function test_ipn_verifies_the_notification_in_test_mode() {
		edd_update_option( 'test_mode', '1' );

		add_filter( 'pre_http_request', array( $this, 'record_postback' ), 9, 3 );
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'complete' );
		$this->simulate_ipn_request( $this->get_dispute_ipn_data() );

		$this->process_ipn();

		$this->assertCount( 1, $this->postbacks, 'Test mode must still verify the notification.' );
		$this->assertStringContainsString(
			'ipnpb.sandbox.paypal.com',
			$this->postback_urls[0],
			'The postback must go to the sandbox IPN host.'
		);
		$this->assertDisputeWasNotProcessed();
	}

	/**
	 * Test: the postback goes to PayPal's IPN host
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::is_verified
	 */
	public function test_the_postback_goes_to_the_ipn_host() {
		add_filter( 'pre_http_request', array( $this, 'record_postback' ), 9, 3 );
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10, 3 );

		$this->simulate_ipn_request( $this->get_dispute_ipn_data() );

		$this->process_ipn();

		$this->assertNotEmpty( $this->postback_urls, 'A postback must have been sent.' );
		$this->assertStringContainsString( 'ipnpb.paypal.com', $this->postback_urls[0] );
	}

	/**
	 * Test: an unavailable verification refuses the notification by default
	 *
	 * Asserts behavior rather than the fallback filter's value, so the test stays valid whether
	 * or not the decision keeps living behind a filter.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::handle_verification_unavailable
	 */
	public function test_ipn_refuses_the_notification_when_verification_is_unavailable() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_403_waf' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'complete' );
		$this->simulate_ipn_request( $this->get_dispute_ipn_data() );

		$this->process_ipn();

		$this->assertDisputeWasNotProcessed();
	}

	/**
	 * Test: a server error refuses the notification by default
	 *
	 * The 403 and 5xx branches share `handle_verification_unavailable()`, and fixing one without
	 * the other is a realistic mistake.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::handle_verification_unavailable
	 */
	public function test_ipn_refuses_the_notification_on_a_server_error() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_503_error' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'complete' );
		$this->simulate_ipn_request( $this->get_dispute_ipn_data() );

		$this->process_ipn();

		$this->assertDisputeWasNotProcessed();
	}

	/**
	 * Test: the verification postback replays the notification as received
	 *
	 * PayPal's `_notify-validate` requires every field it sent to be posted back; a subset makes
	 * PayPal answer INVALID for a legitimate notification, so the postback deliberately is not
	 * filtered down to the fields the handler reads. This asserts that, so the postback is not
	 * "hardened" later in a way that breaks verification for every store.
	 *
	 * @covers \EDD\Gateways\PayPal\Traits\Data::get_encoded_data_array
	 */
	public function test_the_verification_postback_replays_the_notification_as_received() {
		add_filter( 'pre_http_request', array( $this, 'record_postback' ), 9, 3 );
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10, 3 );

		$data = $this->get_dispute_ipn_data();
		$this->simulate_ipn_request( $data );

		$this->process_ipn();

		$this->assertNotEmpty( $this->postbacks, 'A postback must have been sent.' );

		$body = $this->postbacks[0];

		$this->assertIsArray( $body, 'The postback body is built as an array of fields.' );
		$this->assertArrayHasKey( 'cmd', $body );
		$this->assertSame( '_notify-validate', $body['cmd'], 'The postback must ask PayPal to validate.' );

		foreach ( $data as $key => $value ) {
			$this->assertArrayHasKey( $key, $body, sprintf( 'The postback must carry %s as received.', $key ) );
			$this->assertSame( $value, $body[ $key ], sprintf( 'The postback must not alter %s.', $key ) );
		}
	}

	/**
	 * Test: a well formed notification is still refused when PayPal denies it
	 *
	 * The control for the cases above, and the rule underneath them: passing every shape check
	 * is not the same as being authentic. Passes before and after this change.
	 *
	 * @covers \EDD\Gateways\PayPal\IPN::is_verified
	 */
	public function test_a_well_formed_notification_is_refused_when_paypal_denies_it() {
		add_filter( 'pre_http_request', array( $this, 'mock_paypal_invalid' ), 10, 3 );

		edd_update_order_status( $this->order->id, 'complete' );
		$this->simulate_ipn_request(
			$this->get_dispute_ipn_data(
				array(
					'payment_date' => gmdate( 'H:i:s M d, Y T' ),
					'mc_currency'  => 'USD',
				)
			)
		);

		$this->process_ipn();

		$this->assertDisputeWasNotProcessed();
	}

	/**
	 * Records each verification postback without changing the response.
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return false|array|\WP_Error The response, unchanged.
	 */
	public function record_postback( $response, $args, $url ) {
		if ( false !== strpos( $url, 'paypal.com' ) && false !== strpos( $url, 'webscr' ) ) {
			$this->postbacks[]     = isset( $args['body'] ) ? $args['body'] : array();
			$this->postback_urls[] = $url;
		}

		return $response;
	}

	/**
	 * Mock filter to simulate the verification endpoint returning 403.
	 *
	 * @param false|array|\WP_Error $response A preemptive return value of an HTTP request.
	 * @param array                 $args     HTTP request arguments.
	 * @param string                $url      The request URL.
	 * @return array Mocked response
	 */
	public function mock_paypal_403_waf( $response, $args, $url ) {
		if ( false !== strpos( $url, 'paypal.com' ) && false !== strpos( $url, 'webscr' ) ) {
			return array(
				'headers'  => array(),
				'body'     => 'Forbidden',
				'response' => array(
					'code'    => 403,
					'message' => 'Forbidden',
				),
			);
		}

		return $response;
	}

	/**
	 * Whether the order under test carries a dispute note.
	 *
	 * @return bool
	 */
	private function has_dispute_note() {
		$notes = edd_get_notes(
			array(
				'object_type' => 'order',
				'object_id'   => $this->order->id,
			)
		);

		foreach ( $notes as $note ) {
			if ( false !== strpos( $note->content, 'PayPal transaction has been disputed' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Asserts the dispute in the current request changed nothing about the order.
	 */
	private function assertDisputeWasNotProcessed() {
		$this->assertEquals(
			'complete',
			edd_get_order( $this->order->id )->status,
			'A notification which was not verified must not move the order.'
		);
		$this->assertFalse( $this->has_dispute_note(), 'A notification which was not verified must not annotate the order.' );
	}

	/**
	 * Process the IPN and catch any exceptions.
	 *
	 * @return void
	 */
	private function process_ipn() {
		ob_start();
		try {
			new \EDD\Gateways\PayPal\IPN( $_POST );
		} catch ( \Exception $e ) {
			// Expected
		}
		ob_end_clean();
	}
}
