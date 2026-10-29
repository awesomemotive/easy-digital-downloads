<?php

namespace EDD\Tests\Users;

use EDD\Cache\NoCache;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Users\LoginLink\Verify\Handler;
use EDD\Users\LoginLink\Verify\Validate;
use EDD\Users\LoginLink\Token;
use EDD\Users\LoginLink\Utility;
use EDD\Utils\Messages;

class LoginLink extends EDD_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		edd_update_option( 'login_link', true );
		wp_set_current_user( 0 );
		Messages::clear( 'warn' );
	}

	public function tearDown(): void {
		edd_delete_option( 'login_link' );
		wp_set_current_user( 0 );
		Messages::clear( 'warn' );
		NoCache::reset();

		// Clean up validation failure rate limit transient.
		$ip = edd_get_ip();
		if ( ! empty( $ip ) ) {
			delete_transient( 'edd_login_link_fail_' . md5( $ip ) );
			delete_transient( 'edd_login_link_ip_' . md5( $ip ) );
		}

		unset( $_GET['token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_generate_url_uses_edd_action_query_params() {
		$user_id = $this->factory->user->create();
		$token   = bin2hex( random_bytes( 32 ) );

		$url = Token::generate_url( $user_id, $token );
		$this->assertIsString( $url );

		$parsed_url = wp_parse_url( $url );
		$this->assertIsArray( $parsed_url );
		$this->assertArrayHasKey( 'query', $parsed_url );

		wp_parse_str( $parsed_url['query'], $params );
		$this->assertSame( 'login_link_verify', $params['edd_action'] );
		$this->assertSame( $token, rawurldecode( $params['token'] ) );
		$this->assertArrayNotHasKey( 'user_id', $params );
	}

	/**
	 * @return void
	 */
	public function test_resolve_user_id_from_token_uses_transient_index() {
		$user_id    = $this->factory->user->create();
		$token_data = Token::issue( $user_id );

		$resolved_user_id = Token::resolve_user_id( $token_data['token'] );
		$this->assertSame( $user_id, $resolved_user_id );
	}

	/**
	 * @return void
	 */
	public function test_resolve_user_id_from_token_returns_zero_without_index() {
		$token = bin2hex( random_bytes( 32 ) );

		$resolved_user_id = Token::resolve_user_id( $token );
		$this->assertSame( 0, $resolved_user_id );
	}

	/**
	 * @return void
	 */
	public function test_process_login_missing_token_is_rejected() {
		$this->assert_login_link_rejected( '', 'edd_login_link_missing_token' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_disabled_feature_is_rejected() {
		list( , $token ) = $this->create_token_for_user();
		edd_update_option( 'login_link', false );

		$this->assert_login_link_rejected( $token, 'edd_login_link_login_link_disabled' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_invalid_user_is_rejected() {
		$token      = bin2hex( random_bytes( 32 ) );
		$invalid_id = 9999999;
		$this->set_token_index( $token, $invalid_id );

		$this->assert_login_link_rejected( $token, 'edd_login_link_invalid_user' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_used_token_is_rejected() {
		list( $user_id, $token ) = $this->create_token_for_user();
		set_transient( 'edd_login_link_used_' . $user_id, time() - 30, Token::TTL );

		$this->assert_login_link_rejected( $token, 'edd_login_link_token_used' );

		delete_transient( 'edd_login_link_used_' . $user_id );
	}

	/**
	 * @return void
	 */
	public function test_process_login_expired_token_is_rejected() {
		list( , $token ) = $this->create_token_for_user(
			array(
				'expires_at' => time() - 10,
			)
		);

		$this->assert_login_link_rejected( $token, 'edd_login_link_token_expired' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_tampered_token_is_rejected() {
		list( $user_id, $token ) = $this->create_token_for_user();
		$tampered_token          = $token . 'x';

		// Give the tampered token a transient entry so resolution succeeds and
		// the hash check is what rejects it, not a missing index.
		$this->set_token_index( $tampered_token, $user_id );

		$this->assert_login_link_rejected( $tampered_token, 'edd_login_link_token_invalid' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_missing_meta_is_rejected() {
		$user_id = $this->factory->user->create();
		$token   = bin2hex( random_bytes( 32 ) );
		$this->set_token_index( $token, $user_id );

		$this->assert_login_link_rejected( $token, 'edd_login_link_missing_token' );
	}

	/**
	 * @return void
	 */
	public function test_process_login_administrator_is_rejected() {
		$user_id    = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$token_data = Token::issue( $user_id );

		$this->assert_login_link_rejected( $token_data['token'], 'edd_login_link_invalid_user' );
	}

	/**
	 * @return void
	 */
	public function test_subscriber_is_allowed() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_userdata( $user_id );

		$this->assertTrue( Utility::user_allowed( $user ) );
	}

	/**
	 * @return void
	 */
	public function test_admin_is_not_allowed() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user    = get_userdata( $user_id );

		$this->assertFalse( Utility::user_allowed( $user ) );
	}

	/**
	 * @return void
	 */
	public function test_shop_manager_is_not_allowed() {
		$user_id = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$user    = get_userdata( $user_id );

		$this->assertTrue( user_can( $user, 'manage_shop_settings' ), 'Fixture: the shop manager must hold the cap this denial overrides.' );

		$this->assertFalse( Utility::user_allowed( $user ) );
	}

	/**
	 * @return void
	 */
	public function test_process_login_valid_token_logs_in_user() {
		$user_id    = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$token_data = Token::issue( $user_id );

		$this->assertNotEmpty( $token_data['token'] );

		$_GET['token'] = $token_data['token']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$process       = new Handler();
		$process->handle_verify();

		// Verify user is logged in.
		$this->assertSame( $user_id, get_current_user_id() );

		// Verify no errors were set.
		$this->assertEmpty( edd_get_errors() );

		// Verify token was invalidated (meta removed).
		$meta = get_user_meta( $user_id, Token::META_KEY, true );
		$this->assertEmpty( $meta );

		// Verify used-token transient was set.
		$this->assertNotFalse( get_transient( 'edd_login_link_used_' . $user_id ) );

		// Clean up.
		delete_transient( 'edd_login_link_used_' . $user_id );
	}

	/**
	 * @return void
	 */
	public function test_reissued_token_is_not_blocked_by_previous_used_transient() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Issue first token, use it, which sets the used-transient.
		$first = Token::issue( $user_id );
		Token::invalidate( $user_id, $first['token'] );
		$this->assertNotFalse( get_transient( 'edd_login_link_used_' . $user_id ), 'Used transient should exist after invalidation.' );

		// Issue a second token — should clear the stale used-transient.
		$second = Token::issue( $user_id );
		$this->assertFalse( get_transient( 'edd_login_link_used_' . $user_id ), 'Used transient should be cleared by re-issuance.' );

		// Verify the second token succeeds.
		$_GET['token'] = $second['token']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$process       = new Handler();
		$process->handle_verify();

		$this->assertSame( $user_id, get_current_user_id() );

		// Clean up.
		delete_transient( 'edd_login_link_used_' . $user_id );
	}

	/**
	 * @return void
	 */
	public function test_validation_rate_limit_blocks_after_threshold() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_fail_' . md5( $ip );

		set_transient( $key, Validate::MAX_FAILURES + 1, HOUR_IN_SECONDS );

		$this->assert_login_link_rejected( bin2hex( random_bytes( 32 ) ), 'edd_login_link_rate_limited' );

		delete_transient( $key );
	}

	/**
	 * @return void
	 */
	public function test_validation_failure_increments_counter() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_fail_' . md5( $ip );

		delete_transient( $key );

		$_GET['token'] = 'invalid'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$process       = new Handler();
		$process->handle_verify();
		edd_clear_errors();

		$failures = (int) get_transient( $key );
		$this->assertGreaterThan( 0, $failures );

		delete_transient( $key );
	}

	/**
	 * @return void
	 */
	public function test_validation_rate_limit_blocks_at_threshold() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_fail_' . md5( $ip );

		set_transient( $key, Validate::MAX_FAILURES, HOUR_IN_SECONDS );

		$this->assert_login_link_rejected( bin2hex( random_bytes( 32 ) ), 'edd_login_link_rate_limited' );

		delete_transient( $key );
	}

	/**
	 * @return void
	 */
	public function test_validation_rate_limit_allows_below_threshold() {
		$ip  = edd_get_ip();
		$key = 'edd_login_link_fail_' . md5( $ip );

		set_transient( $key, Validate::MAX_FAILURES - 1, HOUR_IN_SECONDS );

		// Request should pass rate limiting but fail on an unrecognised token.
		$this->assert_login_link_rejected( bin2hex( random_bytes( 32 ) ), 'edd_login_link_token_invalid' );

		delete_transient( $key );
	}

	/**
	 * @return void
	 */
	public function test_generate_url_returns_false_for_empty_token() {
		$user_id = $this->factory->user->create();
		$result  = Token::generate_url( $user_id, '' );
		$this->assertFalse( $result );
	}

	/**
	 * @return void
	 */
	public function test_generate_url_returns_false_for_zero_user_id() {
		$result = Token::generate_url( 0, 'sometoken' );
		$this->assertFalse( $result );
	}

	/**
	 * @return void
	 */
	public function test_cleanup_rate_limits_does_not_delete_send_rate_transient() {
		$ip      = edd_get_ip();
		$ip_hash = md5( $ip );

		$send_key    = 'edd_login_link_ip_' . $ip_hash;
		$failure_key = 'edd_login_link_fail_' . $ip_hash;

		// Seed both transients.
		set_transient( $send_key, array( time() ), HOUR_IN_SECONDS );
		set_transient( $failure_key, 3, HOUR_IN_SECONDS );

		Utility::cleanup_rate_limits();

		// Failure transient must be gone.
		$this->assertFalse( get_transient( $failure_key ), 'Failure transient should be deleted after cleanup.' );

		// Send-rate transient must survive.
		$this->assertNotFalse( get_transient( $send_key ), 'Send-rate transient should NOT be deleted after cleanup.' );

		delete_transient( $send_key );
	}

	/**
	 * @param string $token          Token to pass via handle_verify.
	 * @param string $expected_error Expected error key set by Messages.
	 * @return void
	 */
	private function assert_login_link_rejected( string $token, string $expected_error = '' ) {
		if ( '' === $token ) {
			unset( $_GET['token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} else {
			$_GET['token'] = $token; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$process = new Handler();
		$process->handle_verify();

		$errors = Messages::get_by_type( 'warn' );
		$this->assertNotEmpty( $errors, 'Expected login link rejection but no errors were set.' );

		if ( ! empty( $expected_error ) ) {
			$this->assertArrayHasKey( $expected_error, $errors, "Expected error key '{$expected_error}' not found. Got: " . implode( ', ', array_keys( $errors ) ) );
		}

		Messages::clear( 'warn' );
	}

	/**
	 * @param array $meta_overrides
	 * @return array
	 */
	private function create_token_for_user( array $meta_overrides = array() ) {
		$user_id    = $this->factory->user->create();
		$token_data = Token::issue( $user_id );

		if ( ! empty( $meta_overrides ) ) {
			$meta = get_user_meta( $user_id, Token::META_KEY, true );
			update_user_meta( $user_id, Token::META_KEY, array_merge( (array) $meta, $meta_overrides ) );
		}

		return array( $user_id, $token_data['token'] );
	}

	/**
	 * @param string $token
	 * @param int    $user_id
	 * @return void
	 */
	private function set_token_index( $token, $user_id ) {
		set_transient( 'edd_login_link_index_' . hash( 'sha256', (string) $token ), $user_id, MINUTE_IN_SECONDS );
	}
}
