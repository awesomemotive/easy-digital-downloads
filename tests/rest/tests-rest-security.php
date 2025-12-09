<?php
namespace EDD\Tests\REST;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\REST\Security as REST_Security;
use EDD\Utils\Tokenizer;

/**
 * REST Security Tests
 *
 * @group edd_rest
 * @group edd_rest_security
 */
class Security extends EDD_UnitTestCase {

	/**
	 * Security instance.
	 *
	 * @var Security
	 */
	protected $security;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->security = new REST_Security();
	}

	/**
	 * Test that generate_token returns a valid string.
	 *
	 * @covers \EDD\REST\Security::generate_token
	 */
	public function test_generate_token_returns_string() {
		$token = REST_Security::generate_token();
		$this->assertIsString( $token );
		$this->assertNotEmpty( $token );
	}

	/**
	 * Test that generate_token with timestamp returns valid token.
	 *
	 * @covers \EDD\REST\Security::generate_token
	 */
	public function test_generate_token_with_timestamp() {
		$timestamp = time();
		$token = REST_Security::generate_token( $timestamp );

		$this->assertIsString( $token );
		$this->assertNotEmpty( $token );

		// Verify token is valid for the timestamp
		$this->assertTrue( Tokenizer::is_token_valid( $token, $timestamp ) );
	}

	/**
	 * Test that refresh_token returns a new valid token.
	 *
	 * @covers \EDD\REST\Security::refresh_token
	 */
	public function test_refresh_token() {
		$token = $this->security->refresh_token();
		$this->assertIsString( $token );
		$this->assertNotEmpty( $token );
	}

	/**
	 * Test that get_current_timestamp returns an integer.
	 *
	 * @covers \EDD\REST\Security::get_current_timestamp
	 */
	public function test_get_current_timestamp() {
		$timestamp = REST_Security::get_current_timestamp();
		$this->assertIsInt( $timestamp );
		$this->assertEqualsWithDelta( time(), $timestamp, 1000, 'Timestamp is not within 1 second of the current time' );
	}

	/**
	 * Test validate_token with valid token succeeds.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_valid_token() {
		$timestamp = time();
		$token = REST_Security::generate_token( $timestamp );
		$nonce = wp_create_nonce( 'wp_rest' );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $timestamp );

		$this->assertTrue( $this->security->validate_token( $request ) );
	}

	/**
	 * Test validate_token without token returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_without_token() {
		$nonce = wp_create_nonce( 'wp_rest' );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'missing_token', $result->get_error_code() );
		$this->assertEquals( 401, $result->get_error_data()['status'] );
	}

	/**
	 * Test validate_token with empty token returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_empty_token() {
		$nonce = wp_create_nonce( 'wp_rest' );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', '' );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'missing_token', $result->get_error_code() );
	}

	/**
	 * Test validate_token with invalid token returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_invalid_token() {
		$nonce = wp_create_nonce( 'wp_rest' );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', 'invalid_token_string' );
		$request->set_header( 'X-EDD-Cart-Timestamp', time() );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_token', $result->get_error_code() );
		$this->assertEquals( 403, $result->get_error_data()['status'] );
	}

	/**
	 * Test validate_token with expired token returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_expired_token() {
		$nonce = wp_create_nonce( 'wp_rest' );

		// Create a token for an old timestamp (2 hours ago)
		$old_timestamp = time() - ( 2 * HOUR_IN_SECONDS );
		$token = REST_Security::generate_token( $old_timestamp );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $old_timestamp );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_timestamp', $result->get_error_code() );
	}

	/**
	 * Test validate_token with mismatched token and timestamp returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_mismatched_timestamp() {
		$nonce = wp_create_nonce( 'wp_rest' );
		$timestamp1 = time();
		$timestamp2 = time() - 60; // 1 minute ago

		$token = REST_Security::generate_token( $timestamp1 );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $timestamp2 );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_token', $result->get_error_code() );
	}

	/**
	 * Test that tokens are unique for different timestamps.
	 *
	 * @covers \EDD\REST\Security::generate_token
	 */
	public function test_tokens_unique_for_different_timestamps() {
		$timestamp1 = time();
		$timestamp2 = time() - 60;

		$token1 = REST_Security::generate_token( $timestamp1 );
		$token2 = REST_Security::generate_token( $timestamp2 );

		$this->assertNotEquals( $token1, $token2 );
	}

	/**
	 * Test that same timestamp generates same token.
	 *
	 * @covers \EDD\REST\Security::generate_token
	 */
	public function test_same_timestamp_generates_same_token() {
		$timestamp = time();

		$token1 = REST_Security::generate_token( $timestamp );
		$token2 = REST_Security::generate_token( $timestamp );

		$this->assertEquals( $token1, $token2 );
	}

	/**
	 * Test validate_token with logged-in user and both valid nonce and cart token succeeds.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_logged_in_user_and_valid_nonce() {
		// Create and log in a user
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		// Create a valid nonce and cart token
		$nonce = wp_create_nonce( 'wp_rest' );
		$timestamp = time();
		$token = REST_Security::generate_token( $timestamp );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', $nonce );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $timestamp );

		$result = $this->security->validate_token( $request );
		$this->assertTrue( $result );
	}

	/**
	 * Test validate_token with invalid nonce returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_with_invalid_nonce_returns_error() {
		// Create and log in a user
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		// Provide an invalid nonce (even with valid cart token, should fail)
		$timestamp = time();
		$token = REST_Security::generate_token( $timestamp );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-WP-Nonce', 'invalid_nonce' );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $timestamp );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_nonce', $result->get_error_code() );
		$this->assertEquals( 403, $result->get_error_data()['status'] );
	}

	/**
	 * Test validate_token without nonce returns error.
	 *
	 * @covers \EDD\REST\Security::validate_token
	 */
	public function test_validate_token_without_nonce_returns_error() {
		// Create and log in a user
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		// Provide valid cart token but no nonce (should fail - nonce is required)
		$timestamp = time();
		$token = REST_Security::generate_token( $timestamp );

		$request = new \WP_REST_Request( 'GET', '/edd/v1/cart/contents' );
		$request->set_header( 'X-EDD-Cart-Token', $token );
		$request->set_header( 'X-EDD-Cart-Timestamp', $timestamp );

		$result = $this->security->validate_token( $request );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_nonce', $result->get_error_code() );
		$this->assertEquals( 403, $result->get_error_data()['status'] );
	}
}
