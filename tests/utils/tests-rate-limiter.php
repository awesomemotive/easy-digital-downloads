<?php
/**
 * Tests for EDD\Utils\Utility.
 *
 * Universal rate limiter tests: constructor, increment, check,
 * and get_remaining_lockout_seconds. Not feature-specific.
 *
 * @package   EDD\Tests\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Validators\RateLimiter as Utility;

/**
 * @coversDefaultClass \EDD\Utils\Validators\RateLimiter
 */
class RateLimiter extends EDD_UnitTestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		wp_cache_flush();
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		wp_cache_flush();
		parent::tearDown();
	}

	/**
	 * Test Utility can be instantiated with key, max attempts, and window.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::__construct
	 */
	public function test_initialization() {
		$rate_limiter = new Utility( 'test_key', 5, HOUR_IN_SECONDS );
		$this->assertInstanceOf( Utility::class, $rate_limiter );
	}

	/**
	 * Test increment allows first request.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::increment
	 */
	public function test_increment_allows_first_request() {
		$rate_limiter = new Utility( 'test_increment_' . time(), 5, HOUR_IN_SECONDS );
		$identifier   = 'identifier_' . time();

		$result = $rate_limiter->increment( $identifier );

		$this->assertTrue( $result );
	}

	/**
	 * Test increment allows requests within quota.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::increment
	 */
	public function test_increment_allows_requests_within_quota() {
		$rate_limiter = new Utility( 'test_quota_' . time(), 10, HOUR_IN_SECONDS );
		$identifier   = 'identifier_' . time();

		for ( $i = 0; $i < 5; $i++ ) {
			$result = $rate_limiter->increment( $identifier );
			$this->assertTrue( $result, "Request {$i} should be allowed" );
		}
	}

	/**
	 * Test increment returns false when quota exceeded.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::increment
	 */
	public function test_increment_returns_false_over_quota() {
		$rate_limiter = new Utility( 'test_over_' . time(), 3, HOUR_IN_SECONDS );
		$identifier   = 'identifier_' . time();

		$rate_limiter->increment( $identifier );
		$rate_limiter->increment( $identifier );
		$rate_limiter->increment( $identifier );
		$result = $rate_limiter->increment( $identifier );

		$this->assertFalse( $result );
	}

	/**
	 * Test check allows first request.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::check
	 */
	public function test_check_allows_first_request() {
		$rate_limiter = new Utility( 'test_check_' . time(), 5, HOUR_IN_SECONDS );
		$result       = $rate_limiter->check( '192.168.1.1' );

		$this->assertTrue( $result );
	}

	/**
	 * Test check allows requests within quota.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::check
	 */
	public function test_check_allows_requests_within_quota() {
		$rate_limiter = new Utility( 'test_check_quota_' . time(), 5, HOUR_IN_SECONDS );
		$identifier   = '192.168.1.' . time();

		for ( $i = 0; $i < 4; $i++ ) {
			$result = $rate_limiter->check( $identifier );
			$this->assertTrue( $result, "Request {$i} should be allowed" );
		}
	}

	/**
	 * Test check returns WP_Error when rate limit exceeded.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::check
	 */
	public function test_check_returns_error_over_quota() {
		$rate_limiter = new Utility( 'test_check_over_' . time(), 5, HOUR_IN_SECONDS );
		$identifier   = '192.168.1.' . time();

		for ( $i = 0; $i < 5; $i++ ) {
			$rate_limiter->check( $identifier );
		}

		$result = $rate_limiter->check( $identifier );

		$this->assertWPError( $result );
		$this->assertSame( 'rate_limit_exceeded', $result->get_error_code() );
	}

	/**
	 * Test get_remaining_lockout_seconds returns 0 when no active window.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::get_remaining_lockout_seconds
	 */
	public function test_get_remaining_lockout_seconds_returns_zero_when_no_window() {
		$rate_limiter = new Utility( 'test_remaining_' . time(), 5, HOUR_IN_SECONDS );
		$identifier   = 'identifier_' . time();

		$remaining = $rate_limiter->get_remaining_lockout_seconds( $identifier );

		$this->assertSame( 0, $remaining );
	}

	/**
	 * Test get_remaining_lockout_seconds returns positive value after limit exceeded.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::get_remaining_lockout_seconds
	 */
	public function test_get_remaining_lockout_seconds_returns_positive_after_limit_exceeded() {
		$rate_limiter = new Utility( 'test_remaining_pos_' . time(), 1, HOUR_IN_SECONDS );
		$identifier   = 'identifier_' . time();

		$rate_limiter->check( $identifier );
		$rate_limiter->check( $identifier ); // Exceed limit.
		$remaining = $rate_limiter->get_remaining_lockout_seconds( $identifier );

		$this->assertGreaterThan( 0, $remaining );
	}

	/**
	 * Test different key prefixes isolate counters.
	 *
	 * @covers \EDD\Utils\Validators\RateLimiter::increment
	 */
	public function test_different_keys_isolate_counters() {
		$identifier   = 'shared_identifier_' . time();
		$limiter_a     = new Utility( 'key_a_' . time(), 1, HOUR_IN_SECONDS );
		$limiter_b     = new Utility( 'key_b_' . time(), 1, HOUR_IN_SECONDS );

		$this->assertTrue( $limiter_a->increment( $identifier ) );
		$this->assertFalse( $limiter_a->increment( $identifier ) );
		// Same identifier, different key prefix: should get a fresh quota.
		$this->assertTrue( $limiter_b->increment( $identifier ) );
	}
}
