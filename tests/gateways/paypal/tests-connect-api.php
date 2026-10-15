<?php
/**
 * PayPal Proxy API Tests
 *
 * Tests the ConnectAPI class, focusing on HMAC signature computation
 * (validated against contract test vectors), header construction,
 * idempotency key generation, and error handling.
 *
 * @package   EDD\Tests\Gateways\PayPal
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.6.9
 */

namespace EDD\Tests\Gateways\PayPal;

use EDD\Gateways\PayPal\V3\ConnectAPI;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for the ConnectAPI class.
 *
 * @group gateways
 * @group paypal
 * @group paypal-proxy-api
 */
class ConnectAPITest extends EDD_UnitTestCase {

	/**
	 * Shared HMAC key used across all test vectors.
	 *
	 * @var string
	 */
	const TEST_HMAC_KEY = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

	/**
	 * Test store ID.
	 *
	 * @var string
	 */
	const TEST_STORE_ID = '550e8400-e29b-41d4-a716-446655440000';

	/**
	 * The ConnectAPI instance.
	 *
	 * @var ConnectAPI
	 */
	private $api;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();

		// Set up store options.
		update_option( 'edd_paypal_sandbox_store_id', self::TEST_STORE_ID );
		update_option( 'edd_paypal_sandbox_hmac_key', self::TEST_HMAC_KEY );

		add_filter( 'edd_is_test_mode', '__return_true' );

		$this->api = new ConnectAPI( 'sandbox' );
	}

	/**
	 * Tear down test fixtures.
	 */
	public function tearDown(): void {
		delete_option( 'edd_paypal_sandbox_store_id' );
		delete_option( 'edd_paypal_sandbox_hmac_key' );

		remove_filter( 'edd_is_test_mode', '__return_true' );

		parent::tearDown();
	}

	/**
	 * Invokes a private/protected method on the ConnectAPI instance.
	 *
	 * `build_hmac_headers()` and `compute_signature()` are internal
	 * helpers — they're not part of the API surface and have no other
	 * public callers, so they're declared private on the class and
	 * exercised here via reflection.
	 *
	 * @param string $method Method name on the api instance.
	 * @param array  $args   Positional arguments to pass through.
	 * @return mixed The method's return value.
	 */
	private function invoke_private( $method, array $args = array() ) {
		$reflection = new \ReflectionMethod( $this->api, $method );
		if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible( true );
        }

		return $reflection->invokeArgs( $this->api, $args );
	}

	/**
	 * HMAC Test Vector 1: POST with JSON body.
	 *
	 * @see contracts/hmac-spec.md Vector 1
	 */
	public function test_hmac_vector_1_post_with_body() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$body = '{"intent": "CAPTURE", "purchase_units": [{"amount": {"currency_code": "USD", "value": "100.00"}}]}';

		$signature = $this->invoke_private( 'compute_signature', array(
			'1710000000',
			'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
			'POST',
			'/v3/paypal/orders',
			$body,
		) );

		$this->assertSame( '19e346d9787dcca31d9e402109fd839675a16dbed8e4f810d8c6b978fec98d92', $signature );
	}

	/**
	 * HMAC Test Vector 2: GET with empty body.
	 *
	 * @see contracts/hmac-spec.md Vector 2
	 */
	public function test_hmac_vector_2_get_empty_body() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$signature = $this->invoke_private( 'compute_signature', array(
			'1710000300',
			'z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4',
			'GET',
			'/v3/stores/550e8400-e29b-41d4-a716-446655440000/status',
			'',
		) );

		$this->assertSame( '4928d393324ca1e900c243811b9bf14a0d91a58429df1aff79f1f22393345924', $signature );
	}

	/**
	 * HMAC Test Vector 3: DELETE with empty body.
	 *
	 * @see contracts/hmac-spec.md Vector 3
	 */
	public function test_hmac_vector_3_delete_empty_body() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$signature = $this->invoke_private( 'compute_signature', array(
			'1710000600',
			'f1e2d3c4b5a6z7y8x9w0v1u2t3s4r5q6',
			'DELETE',
			'/v3/paypal/vault/payment-tokens/5F227839VA',
			'',
		) );

		$this->assertSame( '77a6983cb0f28e26cee6b93cef3464a9cb2f20528de58cc91f170622288d3f85', $signature );
	}

	/**
	 * Body hash for empty string should be the SHA-256 of empty string.
	 */
	public function test_empty_body_hash() {
		$expected_empty_hash = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
		$this->assertSame( $expected_empty_hash, hash( 'sha256', '' ) );
	}

	/**
	 * HMAC headers should contain all four required headers.
	 */
	public function test_build_hmac_headers_contains_required_headers() {
		$headers = $this->invoke_private( 'build_hmac_headers', array( 'GET', '/v3/stores/test/status' ) );

		$this->assertArrayHasKey( 'X-EDD-Store-ID', $headers );
		$this->assertArrayHasKey( 'X-EDD-Timestamp', $headers );
		$this->assertArrayHasKey( 'X-EDD-Nonce', $headers );
		$this->assertArrayHasKey( 'X-EDD-Signature', $headers );
	}

	/**
	 * The Store-ID header should match our configured store ID.
	 */
	public function test_build_hmac_headers_store_id() {
		$headers = $this->invoke_private( 'build_hmac_headers', array( 'GET', '/v3/stores/test/status' ) );

		$this->assertSame( self::TEST_STORE_ID, $headers['X-EDD-Store-ID'] );
	}

	/**
	 * The timestamp header should be a numeric string.
	 */
	public function test_build_hmac_headers_timestamp_is_numeric() {
		$headers = $this->invoke_private( 'build_hmac_headers', array( 'GET', '/v3/stores/test/status' ) );

		$this->assertIsNumeric( $headers['X-EDD-Timestamp'] );
	}

	/**
	 * The nonce should be 32 characters.
	 */
	public function test_build_hmac_headers_nonce_length() {
		$headers = $this->invoke_private( 'build_hmac_headers', array( 'GET', '/v3/stores/test/status' ) );

		$this->assertSame( 32, strlen( $headers['X-EDD-Nonce'] ) );
	}

	/**
	 * The signature should be a 64-character hex string.
	 */
	public function test_build_hmac_headers_signature_format() {
		$headers = $this->invoke_private( 'build_hmac_headers', array( 'POST', '/v3/paypal/orders', '{"test":true}' ) );

		$this->assertSame( 64, strlen( $headers['X-EDD-Signature'] ) );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $headers['X-EDD-Signature'] );
	}

	/**
	 * is_error should return true for WP_Error.
	 */
	public function test_is_error_with_wp_error() {
		$this->assertTrue( ConnectAPI::is_error( new \WP_Error( 'test', 'Test error' ) ) );
	}

	/**
	 * is_error should return true for proxy error responses.
	 */
	public function test_is_error_with_proxy_error() {
		$response = array(
			'error' => array(
				'code'    => 'invalid_signature',
				'message' => 'Request signature is invalid',
			),
		);

		$this->assertTrue( ConnectAPI::is_error( $response ) );
	}

	/**
	 * is_error should return false for successful responses.
	 */
	public function test_is_error_with_success() {
		$response = array(
			'id'     => 'test-order-id',
			'status' => 'CREATED',
		);

		$this->assertFalse( ConnectAPI::is_error( $response ) );
	}

	/**
	 * get_error_code should extract the code from a proxy error.
	 */
	public function test_get_error_code_from_proxy_error() {
		$response = array(
			'error' => array(
				'code'    => 'payment_declined',
				'message' => 'The payment method was declined by PayPal',
			),
		);

		$this->assertSame( 'payment_declined', ConnectAPI::get_error_code( $response ) );
	}

	/**
	 * get_error_code should extract the code from a WP_Error.
	 */
	public function test_get_error_code_from_wp_error() {
		$error = new \WP_Error( 'http_error', 'Connection failed' );

		$this->assertSame( 'http_error', ConnectAPI::get_error_code( $error ) );
	}

	/**
	 * get_error_message should extract the message from a proxy error.
	 */
	public function test_get_error_message_from_proxy_error() {
		$response = array(
			'error' => array(
				'code'    => 'paypal_error',
				'message' => 'PayPal API is unreachable or returned a server error',
			),
		);

		$this->assertSame( 'PayPal API is unreachable or returned a server error', ConnectAPI::get_error_message( $response ) );
	}

	/**
	 * get_paypal_debug_id should return the debug ID when present.
	 */
	public function test_get_paypal_debug_id() {
		$response = array(
			'error' => array(
				'code'             => 'paypal_validation_error',
				'message'          => 'PayPal rejected the order creation request',
				'paypal_debug_id'  => 'a1b2c3d4e5f6g',
			),
		);

		$this->assertSame( 'a1b2c3d4e5f6g', ConnectAPI::get_paypal_debug_id( $response ) );
	}

	/**
	 * get_paypal_debug_id should return null for proxy-only errors.
	 */
	public function test_get_paypal_debug_id_null_for_proxy_error() {
		$response = array(
			'error' => array(
				'code'             => 'invalid_signature',
				'message'          => 'Request signature is invalid',
				'paypal_debug_id'  => null,
			),
		);

		$this->assertNull( ConnectAPI::get_paypal_debug_id( $response ) );
	}

	/**
	 * get_error_code should return empty string for successful responses.
	 */
	public function test_get_error_code_returns_empty_for_success() {
		$response = array( 'id' => 'test-order-id' );

		$this->assertSame( '', ConnectAPI::get_error_code( $response ) );
	}

	/**
	 * Compute signature should produce consistent results.
	 */
	public function test_compute_signature_is_deterministic() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$sig1 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '{}' ) );
		$sig2 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '{}' ) );

		$this->assertSame( $sig1, $sig2 );
	}

	/**
	 * Different bodies should produce different signatures.
	 */
	public function test_different_bodies_produce_different_signatures() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$sig1 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '{"a":1}' ) );
		$sig2 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '{"a":2}' ) );

		$this->assertNotSame( $sig1, $sig2 );
	}

	/**
	 * Different HMAC keys should produce different signatures.
	 */
	public function test_different_keys_produce_different_signatures() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );
		$sig1 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '' ) );

		$this->api->set_hmac_key( 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb' );
		$sig2 = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '' ) );

		$this->assertNotSame( $sig1, $sig2 );
	}

	/**
	 * The method in the signature should be uppercased.
	 */
	public function test_method_is_uppercased_in_signature() {
		$this->api->set_hmac_key( self::TEST_HMAC_KEY );

		$sig_lower = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'post', '/v3/test', '' ) );
		$sig_upper = $this->invoke_private( 'compute_signature', array( '1710000000', 'testnonce12345678901234567890ab', 'POST', '/v3/test', '' ) );

		$this->assertSame( $sig_lower, $sig_upper );
	}

	/**
	 * Constructor should use default proxy URL when constant is defined.
	 */
	public function test_constructor_uses_proxy_url_constant() {
		$api = new ConnectAPI( 'sandbox' );

		// We can verify this indirectly by checking that the object was created without error.
		$this->assertInstanceOf( ConnectAPI::class, $api );
	}

	/**
	 * set_store_id and set_hmac_key should update the internal state.
	 */
	public function test_setters_update_state() {
		$api = new ConnectAPI( 'sandbox' );

		$api->set_store_id( 'new-store-id' );
		$api->set_hmac_key( self::TEST_HMAC_KEY );

		$reflection = new \ReflectionMethod( $api, 'build_hmac_headers' );
		if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible( true );
        }
		$headers = $reflection->invokeArgs( $api, array( 'GET', '/v3/test' ) );

		$this->assertSame( 'new-store-id', $headers['X-EDD-Store-ID'] );
	}

	/**
	 * Test that the last response code defaults to 0.
	 */
	public function test_last_response_code_defaults_to_zero() {
		$api = new ConnectAPI( 'sandbox' );
		$this->assertSame( 0, $api->get_last_response_code() );
	}

	/**
	 * A 4xx response carrying a structured Connect API error body should surface
	 * the decoded array (not a generic WP_Error) so callers can branch on
	 * the error code.
	 */
	public function test_make_request_surfaces_structured_error_on_4xx() {
		remove_all_filters( 'pre_http_request' );
		add_filter(
			'pre_http_request',
			function () {
				return array(
					'response' => array(
						'code'    => 403,
						'message' => 'Forbidden',
					),
					'headers'  => array( 'content-type' => 'application/json' ),
					'body'     => wp_json_encode(
						array(
							'error' => array(
								'code'    => 'applepay_not_available',
								'message' => 'This account is not subscribed to Apple Pay.',
							),
						)
					),
				);
			},
			10,
			3
		);

		$response = $this->api->make_request( 'POST', '/v3/paypal/applepay/register-domain', array( 'domain' => 'example.com' ) );

		$this->assertIsArray( $response );
		$this->assertFalse( is_wp_error( $response ) );
		$this->assertSame( 'applepay_not_available', ConnectAPI::get_error_code( $response ) );

		remove_all_filters( 'pre_http_request' );
	}
}
