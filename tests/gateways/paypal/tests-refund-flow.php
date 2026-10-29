<?php
/**
 * PayPal V3 Refund Flow Tests
 *
 * Tests refund payload construction, insufficient balance detection,
 * and error handling through the proxy.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.6.9
 */

namespace EDD\Tests\Gateways\PayPal;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for PayPal V3 refund flow.
 *
 * @group gateways
 * @group paypal
 * @group paypal-refund-flow
 */
class RefundFlowTest extends EDD_UnitTestCase {

	/**
	 * Separator settings as they stood before the test, null where absent.
	 *
	 * @var array
	 */
	private $original_separators = array();

	/**
	 * Request keys as they stood before the test, null where absent.
	 *
	 * @var array
	 */
	private $original_post = array();

	/**
	 * Clean up before and after each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->setup_v3_options();

		$settings = get_option( 'edd_settings', array() );
		foreach ( array( 'decimal_separator', 'thousands_separator' ) as $key ) {
			$this->original_separators[ $key ] = $settings[ $key ] ?? null;
		}

		foreach ( array( 'order_id', 'data' ) as $key ) {
			$this->original_post[ $key ] = $_POST[ $key ] ?? null;
		}
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		$this->clean_up_options();

		foreach ( $this->original_separators as $key => $value ) {
			if ( is_null( $value ) ) {
				edd_delete_option( $key );
			} else {
				edd_update_option( $key, $value );
			}
		}

		foreach ( $this->original_post as $key => $value ) {
			if ( is_null( $value ) ) {
				unset( $_POST[ $key ] );
			} else {
				$_POST[ $key ] = $value;
			}
		}

		remove_all_filters( 'pre_http_request' );
		remove_filter( 'edd_is_test_mode', '__return_true' );
		parent::tearDown();
	}

	/**
	 * Set up V3 connection options.
	 */
	private function setup_v3_options() {
		add_filter( 'edd_is_test_mode', '__return_true' );
		update_option( 'edd_paypal_sandbox_commerce_version', 'v3' );
		update_option( 'edd_paypal_sandbox_store_id', 'test-store-id' );
		update_option( 'edd_paypal_sandbox_hmac_key', str_repeat( 'a', 64 ) );
		update_option( 'edd_paypal_sandbox_merchant_id', 'MERCHANT_ID' );
	}

	/**
	 * Remove all PayPal options.
	 */
	private function clean_up_options() {
		foreach ( array( 'sandbox', 'live' ) as $mode ) {
			delete_option( "edd_paypal_{$mode}_commerce_version" );
			delete_option( "edd_paypal_{$mode}_store_id" );
			delete_option( "edd_paypal_{$mode}_hmac_key" );
			delete_option( "edd_paypal_{$mode}_merchant_id" );
		}
	}

	/**
	 * V3 refund sends request to the proxy refund endpoint.
	 */
	public function test_v3_refund_uses_proxy_endpoint() {
		$request_url = '';

		remove_all_filters( 'pre_http_request' );
		add_filter( 'pre_http_request', function( $status, $args, $url ) use ( &$request_url ) {
			$request_url = $url;

			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'id'     => 'REFUND_123',
					'status' => 'COMPLETED',
					'amount' => array( 'value' => '10.00', 'currency_code' => 'USD' ),
				) ),
			);
		}, 10, 3 );

		$proxy = new \EDD\Gateways\PayPal\V3\ConnectAPI( 'sandbox' );
		$proxy->post( '/v3/paypal/orders/ORDER_123/refund', array(
			'capture_id' => 'CAP_123',
		) );

		$this->assertStringContainsString( '/v3/paypal/orders/ORDER_123/refund', $request_url );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Partial refund includes amount in the request body.
	 */
	public function test_partial_refund_includes_amount() {
		$request_body = '';

		remove_all_filters( 'pre_http_request' );
		add_filter( 'pre_http_request', function( $status, $args, $url ) use ( &$request_body ) {
			$request_body = $args['body'];

			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'id'     => 'REFUND_123',
					'status' => 'COMPLETED',
					'amount' => array( 'value' => '25.00', 'currency_code' => 'USD' ),
				) ),
			);
		}, 10, 3 );

		$proxy = new \EDD\Gateways\PayPal\V3\ConnectAPI( 'sandbox' );
		$proxy->post( '/v3/paypal/orders/ORDER_123/refund', array(
			'capture_id' => 'CAP_123',
			'amount'     => array(
				'value'         => '25.00',
				'currency_code' => 'USD',
			),
		) );

		$decoded = json_decode( $request_body, true );
		$this->assertArrayHasKey( 'amount', $decoded );
		$this->assertSame( '25.00', $decoded['amount']['value'] );
		$this->assertSame( 'USD', $decoded['amount']['currency_code'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * INSUFFICIENT_FUNDS error is detected from proxy response.
	 */
	public function test_insufficient_funds_error_detected() {
		$error_response = array(
			'error' => array(
				'code'    => 'paypal_validation_error',
				'message' => 'PayPal rejected the request',
				'details' => array(
					'details' => array(
						array(
							'issue'       => 'INSUFFICIENT_FUNDS',
							'description' => 'The seller does not have enough funds to complete this transaction.',
						),
					),
				),
			),
		);

		$this->assertTrue( \EDD\Gateways\PayPal\V3\ConnectAPI::is_error( $error_response ) );

		// Verify the error details contain the INSUFFICIENT_FUNDS issue.
		$details = $error_response['error']['details']['details'];
		$found   = false;
		foreach ( $details as $detail ) {
			if ( 'INSUFFICIENT_FUNDS' === $detail['issue'] ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found );
	}

	/**
	 * TRANSACTION_REFUSED error is detected from proxy response.
	 */
	public function test_transaction_refused_error_detected() {
		$error_response = array(
			'error' => array(
				'code'    => 'paypal_validation_error',
				'message' => 'PayPal rejected the request',
				'details' => array(
					'details' => array(
						array(
							'issue'       => 'TRANSACTION_REFUSED',
							'description' => 'The transaction was refused.',
						),
					),
				),
			),
		);

		$this->assertTrue( \EDD\Gateways\PayPal\V3\ConnectAPI::is_error( $error_response ) );
	}

	/**
	 * Generic proxy error is detected.
	 */
	public function test_generic_proxy_error() {
		$error_response = array(
			'error' => array(
				'code'    => 'paypal_error',
				'message' => 'PayPal is temporarily unavailable.',
			),
		);

		$this->assertTrue( \EDD\Gateways\PayPal\V3\ConnectAPI::is_error( $error_response ) );
		$this->assertSame( 'paypal_error', \EDD\Gateways\PayPal\V3\ConnectAPI::get_error_code( $error_response ) );
		$this->assertSame( 'PayPal is temporarily unavailable.', \EDD\Gateways\PayPal\V3\ConnectAPI::get_error_message( $error_response ) );
	}

	/**
	 * WP_Error is detected as a proxy error.
	 */
	public function test_wp_error_is_proxy_error() {
		$error = new \WP_Error( 'http_request_failed', 'Connection timed out' );

		$this->assertTrue( \EDD\Gateways\PayPal\V3\ConnectAPI::is_error( $error ) );
	}

	// -------------------------------------------------------------------------
	// Legacy v2 → v3 refund routing tests.
	// -------------------------------------------------------------------------

	/**
	 * A v2 order (no paypal_order_id meta) on a v3 store routes to the
	 * captures refund endpoint, not the orders endpoint.
	 */
	public function test_v2_order_on_v3_store_routes_to_captures_endpoint() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array( 'gateway' => 'paypal_commerce' )
		);
		edd_update_order( $order_id, array( 'gateway' => 'paypal_commerce' ) );
		// Intentionally no paypal_order_id meta — this is a v2 order.

		$order        = edd_get_order( $order_id );
		$captured_url = '';

		remove_all_filters( 'pre_http_request' );
		add_filter( 'pre_http_request', function( $status, $args, $url ) use ( &$captured_url ) {
			$captured_url = $url;
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'id'     => 'REFUND_V2_001',
					'status' => 'COMPLETED',
					'amount' => array( 'value' => '20.00', 'currency_code' => 'USD' ),
				) ),
			);
		}, 10, 3 );

		\EDD\Gateways\PayPal\refund_transaction( $order );

		$this->assertStringContainsString( '/v3/paypal/captures/', $captured_url );
		$this->assertStringContainsString( '/refund', $captured_url );
		$this->assertStringNotContainsString( '/v3/paypal/orders/', $captured_url );
	}

	/**
	 * A v3 order, which also carries paypal_order_id meta, routes to the captures
	 * endpoint like every other refund on a Connect store.
	 *
	 * Regression guard: paypal_order_id is written by both checkout versions, so it must
	 * never be what decides the endpoint.
	 */
	public function test_v3_order_on_v3_store_routes_to_captures_endpoint() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array( 'gateway' => 'paypal_commerce' )
		);
		edd_update_order( $order_id, array( 'gateway' => 'paypal_commerce' ) );
		edd_update_order_meta( $order_id, 'paypal_order_id', 'PPORDER_V3_001' );

		$order        = edd_get_order( $order_id );
		$captured_url = '';

		remove_all_filters( 'pre_http_request' );
		add_filter( 'pre_http_request', function( $status, $args, $url ) use ( &$captured_url ) {
			$captured_url = $url;
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'id'     => 'REFUND_V3_001',
					'status' => 'COMPLETED',
					'amount' => array( 'value' => '20.00', 'currency_code' => 'USD' ),
				) ),
			);
		}, 10, 3 );

		\EDD\Gateways\PayPal\refund_transaction( $order );

		$this->assertStringContainsString(
			'/v3/paypal/captures/' . $order->get_transaction_id() . '/refund',
			$captured_url
		);
		$this->assertStringNotContainsString( '/v3/paypal/orders/', $captured_url );
		$this->assertStringNotContainsString( 'PPORDER_V3_001', $captured_url, 'The paypal_order_id meta must not reach the URL.' );
	}

	/**
	 * A v2 order on a v2 store (not v3-onboarded) takes the legacy API path,
	 * which throws Authentication_Exception when no v2 credentials are configured.
	 *
	 * The order carries paypal_order_id meta because the v2 checkout records it too.
	 * Without that meta the store-version check can regress to reading the meta and
	 * this test would still pass.
	 */
	public function test_v2_order_on_v2_store_throws_without_credentials() {
		// Clear v3 options to simulate a pure v2 store.
		$this->clean_up_options();

		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array( 'gateway' => 'paypal_commerce' )
		);
		edd_update_order( $order_id, array( 'gateway' => 'paypal_commerce' ) );
		edd_add_order_transaction( array(
			'object_id'      => $order_id,
			'object_type'    => 'order',
			'transaction_id' => 'TXN_V2_V2STORE',
			'gateway'        => 'paypal_commerce',
			'status'         => 'complete',
			'total'          => 20.00,
		) );
		edd_update_order_meta( $order_id, 'paypal_order_id', 'PPORDER_V2_ON_V2' );

		$order = edd_get_order( $order_id );

		$this->expectException( \EDD\Gateways\PayPal\Exceptions\Authentication_Exception::class );

		\EDD\Gateways\PayPal\refund_transaction( $order );
	}

	/**
	 * The pre-flight leaves a v2 store alone, even though its orders carry
	 * paypal_order_id meta.
	 *
	 * The reported failure: the pre-flight called the proxy with no store credentials,
	 * which was rejected for missing authentication headers, so the refund reached
	 * neither PayPal nor EDD. The nonce is valid here so that the store-version check
	 * is the only thing that can stop the request.
	 */
	public function test_preflight_skips_a_v2_store_whose_order_has_paypal_order_id() {
		$this->clean_up_options();

		// Leave the commerce version reporting v3 so the missing Connect credentials are
		// the only thing that can stop the request.
		update_option( 'edd_paypal_sandbox_commerce_version', 'v3' );

		$order_id = $this->build_refundable_order();
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, wp_create_nonce( 'edd_process_refund' ) );

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$this->assertSame( 0, $calls->count, 'A store with no Connect credentials must not call the proxy.' );
		$this->assertFalse(
			get_transient( \EDD\Gateways\PayPal\preflight_cache_key( edd_get_order( $order_id )->get_transaction_id() ) ),
			'Nothing may be cached when the pre-flight is skipped.'
		);
	}

	/**
	 * A cached pre-flight response is consumed by refund_transaction() for a v2
	 * order on a v3 store, avoiding a second round-trip to the proxy.
	 */
	public function test_preflighted_response_is_reused_for_v2_order() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array( 'gateway' => 'paypal_commerce' )
		);
		edd_update_order( $order_id, array( 'gateway' => 'paypal_commerce' ) );
		// No paypal_order_id meta — v2 order. Use the actual transaction ID the
		// helper stored so the transient key matches what refund_transaction() computes.
		$order          = edd_get_order( $order_id );
		$transaction_id = $order->get_transaction_id();

		$cached_response = array(
			'id'     => 'REFUND_PREFLIGHTED',
			'status' => 'COMPLETED',
			'amount' => array( 'value' => '20.00', 'currency_code' => 'USD' ),
		);
		set_transient(
			\EDD\Gateways\PayPal\preflight_cache_key( $transaction_id ),
			$cached_response,
			MINUTE_IN_SECONDS
		);

		$http_request_made = false;
		remove_all_filters( 'pre_http_request' );
		add_filter( 'pre_http_request', function( $status, $args, $url ) use ( &$http_request_made ) {
			$http_request_made = true;
			// Return a mock to prevent actual network call if the cache is missed.
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array( 'id' => 'REFUND_FALLBACK', 'status' => 'COMPLETED' ) ),
			);
		}, 10, 3 );

		\EDD\Gateways\PayPal\refund_transaction( $order );

		$this->assertFalse( $http_request_made, 'refund_transaction() should reuse the pre-flighted response, not make a new HTTP request.' );
		$this->assertFalse( get_transient( \EDD\Gateways\PayPal\preflight_cache_key( $transaction_id ) ), 'Pre-flight transient should be deleted after use.' );
	}

	/**
	 * The pre-flight must not reach the gateway without a valid refund nonce.
	 *
	 * The pre-flight runs ahead of the callback that owns authorization for this action,
	 * so it has to establish intent itself rather than inheriting it.
	 */
	public function test_preflight_requires_a_valid_refund_nonce() {
		$order_id = $this->build_refundable_order();
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, 'not-a-real-nonce' );

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$this->assertSame( 0, $calls->count, 'No refund may be sent to the gateway without a valid nonce.' );
		$this->assertFalse(
			get_transient( \EDD\Gateways\PayPal\preflight_cache_key( edd_get_order( $order_id )->get_transaction_id() ) ),
			'No pre-flight result may be cached.'
		);
	}

	/**
	 * A nonce minted for some other action must not authorize a refund.
	 *
	 * Pins the action name, not merely the presence of a token.
	 */
	public function test_preflight_rejects_a_nonce_for_another_action() {
		$order_id = $this->build_refundable_order();
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, wp_create_nonce( 'edd_process_refund_something_else' ) );

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$this->assertSame( 0, $calls->count, 'A nonce for a different action must not authorize a refund.' );
	}

	/**
	 * The control: a properly nonced refund still pre-flights.
	 *
	 * Without this, refusing every request would satisfy the cases above.
	 */
	public function test_preflight_dispatches_with_a_valid_nonce() {
		$order_id = $this->build_refundable_order();
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, wp_create_nonce( 'edd_process_refund' ) );

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$this->assertSame( 1, $calls->count, 'A properly nonced refund must still reach the gateway.' );
		$this->assertNotFalse(
			get_transient( \EDD\Gateways\PayPal\preflight_cache_key( edd_get_order( $order_id )->get_transaction_id() ) ),
			'The pre-flight result must be cached for the post-flight callback.'
		);
	}

	/**
	 * A nonce for the right action, minted by somebody else, must not authorize a refund.
	 *
	 * wp_create_nonce() binds the user ID into the hash, so this is closer to the audit's
	 * scenario than a malformed token: the value is well formed and correct for the action,
	 * it just was not created by the person making the request.
	 */
	public function test_preflight_rejects_a_nonce_minted_by_another_user() {
		$order_id = $this->build_refundable_order();

		// Mint the nonce as somebody else, before switching to the acting user.
		$other_user = new \WP_User( $this->factory->user->create( array( 'role' => 'shop_worker' ) ) );
		$other_user->add_cap( 'edit_shop_payments' );
		wp_set_current_user( $other_user->ID );
		$their_nonce = wp_create_nonce( 'edd_process_refund' );

		// The acting user still holds the capability, so the nonce is the only thing that can
		// refuse this request.
		$acting_user = $this->sign_in_refunder();
		$this->assertNotSame( $other_user->ID, $acting_user, 'The two users must be different.' );
		$this->assertTrue( current_user_can( 'edit_shop_payments' ), 'The capability gate must not be what refuses.' );

		$this->seed_refund_request( $order_id, $their_nonce );

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$this->assertSame( 0, $calls->count, "A nonce minted by another user must not authorize a refund." );
	}

	/**
	 * A partial refund's amount must reach PayPal as a plain decimal string, never one
	 * carrying the store's own decimal or thousands separator.
	 */
	public function test_preflight_partial_refund_sends_an_unformatted_amount() {
		edd_update_option( 'decimal_separator', ',' );
		edd_update_option( 'thousands_separator', '.' );

		$order_id = $this->build_high_value_refundable_order( 100.00 );
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, wp_create_nonce( 'edd_process_refund' ), '12,50' );

		$request = $this->capture_refund_request_body();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$decoded = json_decode( $request->body, true );

		$this->assertArrayHasKey( 'amount', $decoded, 'A partial refund must send an amount.' );
		$this->assertSame( '12.50', $decoded['amount']['value'], 'A refund of 12,50 must reach PayPal as 12.50.' );
	}

	/**
	 * A partial refund over 1000 must not let the store's thousands separator leak
	 * into the amount PayPal receives, even under the store's default separators.
	 */
	public function test_preflight_partial_refund_over_one_thousand_sends_no_thousands_separator() {
		$order_id = $this->build_high_value_refundable_order( 2000.00 );
		$this->sign_in_refunder();
		$this->seed_refund_request( $order_id, wp_create_nonce( 'edd_process_refund' ), '1234.56' );

		$request = $this->capture_refund_request_body();

		\EDD\Gateways\PayPal\preflight_refund_submission();

		$decoded = json_decode( $request->body, true );

		$this->assertSame( '1234.56', $decoded['amount']['value'], 'A refund of 1234.56 must not carry a thousands separator.' );
	}

	/**
	 * A cached pre-flight response with no amount still records the refund as a negative
	 * transaction on the refund order.
	 */
	public function test_preflighted_minimal_response_records_negative_transaction_on_refund_order() {
		$order_id  = $this->build_high_value_refundable_order( 20.00 );
		$refund_id = $this->create_refund_without_gateway_call( $order_id, 'all' );

		set_transient(
			\EDD\Gateways\PayPal\preflight_cache_key( 'CAPTURE_HIGH_VALUE' ),
			array(
				'id'     => 'REFUND_MIN',
				'status' => 'COMPLETED',
			),
			MINUTE_IN_SECONDS
		);

		$calls = $this->count_refund_requests();

		\EDD\Gateways\PayPal\refund_transaction( edd_get_order( $order_id ), edd_get_order( $refund_id ) );

		$this->assertSame( 0, $calls->count, 'The cached pre-flight response must be used.' );

		$transactions = edd_get_order_transactions( array( 'transaction_id' => 'REFUND_MIN' ) );

		$this->assertCount( 1, $transactions );
		$this->assertSame( $refund_id, (int) $transactions[0]->object_id, 'The transaction must belong to the refund order.' );
		$this->assertSame( 'order', $transactions[0]->object_type );
		$this->assertEquals( -20.00, (float) $transactions[0]->total );
	}

	/**
	 * A full refund sends no amount, so PayPal refunds whatever is left on the capture. A minimal
	 * response does not say how much that was, so the notes must not claim one, while the
	 * transaction still matches the refund order.
	 */
	public function test_full_refund_minimal_response_notes_state_no_amount() {
		$order_id  = $this->build_high_value_refundable_order( 20.00 );
		$refund_id = $this->create_refund_without_gateway_call( $order_id, 'all' );

		$this->respond_with_minimal_refund( 'REFUND_FULL_MIN' );

		\EDD\Gateways\PayPal\refund_transaction( edd_get_order( $order_id ), edd_get_order( $refund_id ) );

		$transactions = edd_get_order_transactions( array( 'transaction_id' => 'REFUND_FULL_MIN' ) );

		$this->assertCount( 1, $transactions );
		$this->assertEquals( -20.00, (float) $transactions[0]->total );

		foreach ( array( $order_id, $refund_id ) as $note_order_id ) {
			$notes = $this->get_order_note_contents( $note_order_id );

			$this->assertContains( 'Successfully refunded in PayPal. Refund transaction ID: REFUND_FULL_MIN', $notes, "Order {$note_order_id} must get the amount-less note." );
			foreach ( $notes as $note ) {
				$this->assertStringNotContainsString( '$20.00', $note );
			}
		}
	}

	/**
	 * A partial refund answered with no amount records the refund order's amount as the transaction.
	 */
	public function test_partial_refund_minimal_response_records_negative_transaction() {
		$order_id  = $this->build_high_value_refundable_order( 100.00 );
		$refund_id = $this->create_partial_refund_without_gateway_call( $order_id, 12.50 );

		$this->respond_with_minimal_refund( 'REFUND_PARTIAL_TX' );

		\EDD\Gateways\PayPal\refund_transaction( edd_get_order( $order_id ), edd_get_order( $refund_id ) );

		$transactions = edd_get_order_transactions( array( 'transaction_id' => 'REFUND_PARTIAL_TX' ) );

		$this->assertCount( 1, $transactions );
		$this->assertSame( $refund_id, (int) $transactions[0]->object_id, 'The transaction must belong to the refund order.' );
		$this->assertEquals( -12.50, (float) $transactions[0]->total );
	}

	/**
	 * A partial refund sends its amount, so a COMPLETED response confirms it even when the body
	 * leaves it out, and both orders get a note naming it.
	 */
	public function test_partial_refund_minimal_response_notes_state_sent_amount() {
		$order_id  = $this->build_high_value_refundable_order( 100.00 );
		$order     = edd_get_order( $order_id );
		$refund_id = $this->create_partial_refund_without_gateway_call( $order_id, 12.50 );

		$this->respond_with_minimal_refund( 'REFUND_PARTIAL_NOTE' );

		\EDD\Gateways\PayPal\refund_transaction( $order, edd_get_order( $refund_id ) );

		// Notes are stored sanitized, which re-encodes the currency symbol's entity.
		$expected_note = edd_sanitize_note_content(
			sprintf(
				'%1$s refunded in PayPal. Refund transaction ID: %2$s',
				edd_currency_filter( edd_format_amount( 12.50 ), $order->currency ),
				'REFUND_PARTIAL_NOTE'
			)
		);

		foreach ( array( $order_id, $refund_id ) as $note_order_id ) {
			$notes = $this->get_order_note_contents( $note_order_id );

			$this->assertContains( $expected_note, $notes, "Order {$note_order_id} must get a note naming the refunded amount." );
			foreach ( $notes as $note ) {
				$this->assertStringNotContainsString( 'Successfully refunded in PayPal', $note );
			}
		}
	}

	/**
	 * Creates a paypal_commerce order that the pre-flight will act on.
	 *
	 * @return int
	 */
	private function build_refundable_order() {
		$order_id = Helpers\EDD_Helper_Payment::create_simple_payment(
			array( 'gateway' => 'paypal_commerce' )
		);
		edd_update_order( $order_id, array( 'gateway' => 'paypal_commerce' ) );
		edd_update_order_meta( $order_id, 'paypal_order_id', 'ORDER_P1B' );

		return $order_id;
	}

	/**
	 * Creates a paypal_commerce order with a single item large enough to exercise a
	 * partial refund's amount formatting, rather than the two fixed-price items
	 * build_refundable_order() provides.
	 *
	 * @param float $total Order (and single item) total.
	 * @return int
	 */
	private function build_high_value_refundable_order( $total ) {
		$download = Helpers\EDD_Helper_Download::create_simple_download();

		$order_id = parent::edd()->order->create(
			array(
				'gateway'  => 'paypal_commerce',
				'mode'     => 'test',
				'subtotal' => $total,
				'tax'      => 0,
				'discount' => 0,
				'total'    => $total,
			)
		);

		// The order factory always adds one fixed-price item, so resize it rather than add a second.
		parent::edd()->order_item->update_object(
			current( edd_get_order( $order_id )->get_items() )->id,
			array(
				'product_id'   => $download->ID,
				'product_name' => $download->post_title,
				'amount'       => $total,
				'subtotal'     => $total,
				'discount'     => 0,
				'tax'          => 0,
				'total'        => $total,
			)
		);

		parent::edd()->order_transaction->create(
			array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => 'CAPTURE_HIGH_VALUE',
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => $total,
			)
		);

		return $order_id;
	}

	/**
	 * Creates a refund order without letting the edd_refund_order callback call PayPal.
	 *
	 * @param int          $order_id    The order being refunded.
	 * @param string|array $order_items Items to refund, as edd_refund_order() takes them.
	 * @return int
	 */
	private function create_refund_without_gateway_call( $order_id, $order_items ) {
		// The callback only calls PayPal when the refund form data is posted.
		unset( $_POST['data'] );

		$refund_id = edd_refund_order( $order_id, $order_items );
		$this->assertIsInt( $refund_id, 'The refund order must be created.' );

		return $refund_id;
	}

	/**
	 * Creates a partial refund order for a single-item order, without calling PayPal.
	 *
	 * @param int   $order_id The order being refunded.
	 * @param float $amount   The amount to refund.
	 * @return int
	 */
	private function create_partial_refund_without_gateway_call( $order_id, $amount ) {
		$item = current( edd_get_order( $order_id )->get_items() );

		return $this->create_refund_without_gateway_call(
			$order_id,
			array(
				array(
					'order_item_id' => $item->id,
					'quantity'      => 1,
					'subtotal'      => $amount,
					'tax'           => 0,
					'total'         => $amount,
				),
			)
		);
	}

	/**
	 * Makes the direct PayPal call answer COMPLETED with an id and no amount.
	 *
	 * @param string $refund_transaction_id The refund's ID in PayPal.
	 */
	private function respond_with_minimal_refund( $refund_transaction_id ) {
		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function () use ( $refund_transaction_id ) {
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode(
						array(
							'id'     => $refund_transaction_id,
							'status' => 'COMPLETED',
						)
					),
				);
			}
		);
	}

	/**
	 * Returns the content of every note on an order.
	 *
	 * @param int $order_id The order.
	 * @return string[]
	 */
	private function get_order_note_contents( $order_id ) {
		return wp_list_pluck(
			edd_get_notes(
				array(
					'object_id'   => $order_id,
					'object_type' => 'order',
				)
			),
			'content'
		);
	}

	/**
	 * Signs in a user who may refund orders, and returns their ID.
	 *
	 * The capability is granted explicitly because the pre-flight's own gates are what these
	 * tests cover, not how EDD composes its roles. Nonces are bound to the user who creates
	 * them, so which user is signed in decides whether a nonce validates.
	 *
	 * @return int
	 */
	private function sign_in_refunder() {
		$user = new \WP_User( $this->factory->user->create( array( 'role' => 'shop_worker' ) ) );
		$user->add_cap( 'edit_shop_payments' );
		wp_set_current_user( $user->ID );

		return $user->ID;
	}

	/**
	 * Seeds the request the admin refund modal submits, with the nonce under test.
	 *
	 * @param int         $order_id The order being refunded.
	 * @param string      $nonce    The value to place in the form's nonce field.
	 * @param string|null $subtotal Optional subtotal to post for each item exactly as typed, in place of the item's own.
	 */
	private function seed_refund_request( $order_id, $nonce, $subtotal = null ) {
		$order = edd_get_order( $order_id );
		$items = array();

		foreach ( $order->get_items() as $item ) {
			$items[] = sprintf(
				'refund_order_item[%1$d][quantity]=%2$d&refund_order_item[%1$d][subtotal]=%3$s&refund_order_item[%1$d][tax]=%4$s&refund_order_item[%1$d][id]=%1$d',
				$item->id,
				$item->quantity,
				$subtotal ?? edd_format_amount( $item->subtotal ),
				edd_format_amount( $item->tax )
			);
		}

		$_POST['order_id'] = $order_id;
		$_POST['data']     = 'edd-paypal-commerce-refund=1&edd_process_refund=' . rawurlencode( $nonce ) . '&' . implode( '&', $items );
	}

	/**
	 * Intercepts outbound HTTP and counts refund calls, returning a live counter.
	 *
	 * @return object
	 */
	private function count_refund_requests() {
		$counter = new \stdClass();
		$counter->count = 0;

		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function ( $status, $args, $url ) use ( $counter ) {
				if ( false !== strpos( (string) $url, '/refund' ) ) {
					$counter->count++;
				}

				return array(
					'response' => array( 'code' => 201 ),
					'body'     => wp_json_encode( array( 'id' => 'REFUND_TEST', 'status' => 'COMPLETED' ) ),
				);
			},
			10,
			3
		);

		return $counter;
	}

	/**
	 * Intercepts outbound HTTP and records the refund request body, returning a live holder.
	 *
	 * @return object
	 */
	private function capture_refund_request_body() {
		$request       = new \stdClass();
		$request->body = '';

		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function ( $status, $args, $url ) use ( $request ) {
				$request->body = $args['body'];

				return array(
					'response' => array( 'code' => 200 ),
					'body'     => wp_json_encode( array( 'id' => 'REFUND_TEST', 'status' => 'COMPLETED' ) ),
				);
			},
			10,
			3
		);

		return $request;
	}

}
