<?php
/**
 * Tests for Captcha Validation
 *
 * @group edd_captcha
 * @group edd_pro
 */

namespace EDD\Tests\Captcha;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Captcha\Validate;

class Validation extends EDD_UnitTestCase {

	/**
	 * Validate instance.
	 *
	 * @var Validate
	 */
	protected $validate;

	/**
	 * Test helper class to access protected methods.
	 *
	 * @var TestableValidate
	 */
	protected $testable_validate;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->validate = new Validate();
		$this->testable_validate = new TestableValidate();

		// Clear any existing session data.
		EDD()->session->set( 'captcha_validated_tokens', array() );

		// Set up reCAPTCHA keys for testing.
		edd_update_option( 'recaptcha_site_key', 'test_site_key_123' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret_key_456' );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clear session data.
		EDD()->session->set( 'captcha_validated_tokens', array() );

		// Remove filters.
		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test that get_validated_tokens returns empty array when no tokens exist.
	 */
	public function test_get_validated_tokens_empty() {
		$tokens = $this->testable_validate->get_validated_tokens();

		$this->assertIsArray( $tokens );
		$this->assertEmpty( $tokens );
	}

	/**
	 * Test that get_validated_tokens returns valid tokens.
	 */
	public function test_get_validated_tokens_returns_valid_tokens() {
		$token_hash = md5( 'test_token_123' );
		$token_data = array(
			$token_hash => array(
				'result'    => true,
				'timestamp' => time(),
			),
		);

		EDD()->session->set( 'captcha_validated_tokens', $token_data );

		$tokens = $this->testable_validate->get_validated_tokens();

		$this->assertArrayHasKey( $token_hash, $tokens );
		$this->assertTrue( $tokens[ $token_hash ]['result'] );
	}

	/**
	 * Test that get_validated_tokens removes expired tokens.
	 */
	public function test_get_validated_tokens_removes_expired_tokens() {
		$fresh_token_hash   = md5( 'fresh_token' );
		$expired_token_hash = md5( 'expired_token' );

		$token_data = array(
			$fresh_token_hash   => array(
				'result'    => true,
				'timestamp' => time(), // Fresh token.
			),
			$expired_token_hash => array(
				'result'    => true,
				'timestamp' => time() - 121, // 121 seconds ago (expired).
			),
		);

		EDD()->session->set( 'captcha_validated_tokens', $token_data );

		$tokens = $this->testable_validate->get_validated_tokens();

		// Fresh token should still be there.
		$this->assertArrayHasKey( $fresh_token_hash, $tokens );

		// Expired token should be removed.
		$this->assertArrayNotHasKey( $expired_token_hash, $tokens );
	}

	/**
	 * Test that get_validated_tokens keeps tokens within 2-minute window.
	 */
	public function test_get_validated_tokens_respects_two_minute_window() {
		$token_hash = md5( 'recent_token' );

		$token_data = array(
			$token_hash => array(
				'result'    => true,
				'timestamp' => time() - 119, // 119 seconds ago (still valid).
			),
		);

		EDD()->session->set( 'captcha_validated_tokens', $token_data );

		$tokens = $this->testable_validate->get_validated_tokens();

		// Token should still be present (within 120 second window).
		$this->assertArrayHasKey( $token_hash, $tokens );
	}

	/**
	 * Test that validation cleans up multiple expired tokens at once.
	 */
	public function test_cleanup_removes_multiple_expired_tokens() {
		$valid_token_1   = md5( 'valid_1' );
		$valid_token_2   = md5( 'valid_2' );
		$expired_token_1 = md5( 'expired_1' );
		$expired_token_2 = md5( 'expired_2' );
		$expired_token_3 = md5( 'expired_3' );

		$token_data = array(
			$valid_token_1   => array(
				'result'    => true,
				'timestamp' => time(),
			),
			$expired_token_1 => array(
				'result'    => true,
				'timestamp' => time() - 150,
			),
			$valid_token_2   => array(
				'result'    => true,
				'timestamp' => time() - 60,
			),
			$expired_token_2 => array(
				'result'    => true,
				'timestamp' => time() - 200,
			),
			$expired_token_3 => array(
				'result'    => true,
				'timestamp' => time() - 300,
			),
		);

		EDD()->session->set( 'captcha_validated_tokens', $token_data );

		$tokens = $this->testable_validate->get_validated_tokens();

		// Should only have 2 valid tokens.
		$this->assertCount( 2, $tokens );
		$this->assertArrayHasKey( $valid_token_1, $tokens );
		$this->assertArrayHasKey( $valid_token_2, $tokens );

		// All expired tokens should be gone.
		$this->assertArrayNotHasKey( $expired_token_1, $tokens );
		$this->assertArrayNotHasKey( $expired_token_2, $tokens );
		$this->assertArrayNotHasKey( $expired_token_3, $tokens );
	}
}

/**
 * Helper class to access protected methods for testing.
 */
class TestableValidate extends Validate {

	/**
	 * Expose get_validated_tokens as public for testing.
	 *
	 * @return array
	 */
	public function get_validated_tokens(): array {
		return parent::get_validated_tokens();
	}
}

