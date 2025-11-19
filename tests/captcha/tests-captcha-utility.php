<?php
/**
 * Tests for Captcha Utility
 *
 * @group edd_captcha
 * @group edd_captcha_utility
 * @group edd_pro
 */

namespace EDD\Tests\Captcha;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Captcha\Utility;
use EDD\Captcha\Providers\Provider;

class UtilityTests extends EDD_UnitTestCase {

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear provider cache.
		$this->clear_provider_cache();

		// Clear all CAPTCHA settings.
		edd_delete_option( 'captcha_provider' );
		edd_delete_option( 'recaptcha_site_key' );
		edd_delete_option( 'recaptcha_secret_key' );
		edd_delete_option( 'recaptcha_checkout' );
		edd_delete_option( 'turnstile_site_key' );
		edd_delete_option( 'turnstile_secret_key' );

		// Log out user.
		wp_set_current_user( 0 );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();

		$this->clear_provider_cache();

		// Clear all CAPTCHA settings.
		edd_delete_option( 'captcha_provider' );
		edd_delete_option( 'recaptcha_site_key' );
		edd_delete_option( 'recaptcha_secret_key' );
		edd_delete_option( 'recaptcha_checkout' );
		edd_delete_option( 'turnstile_site_key' );
		edd_delete_option( 'turnstile_secret_key' );

		// Remove all filters.
		remove_all_filters( 'edd_can_captcha_checkout' );
		remove_all_filters( 'edd_can_recaptcha_checkout' );
	}

	/**
	 * Clear the provider cache.
	 */
	private function clear_provider_cache() {
		$reflection = new \ReflectionClass( Provider::class );
		$property = $reflection->getProperty( 'providers' );
		$property->setAccessible( true );
		$property->setValue( null, array() );
	}

	/**
	 * Test can_do_captcha returns false when no provider configured.
	 */
	public function test_can_do_captcha_returns_false_when_no_provider() {
		edd_update_option( 'recaptcha_checkout', 'always' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertFalse( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha returns false when checkout setting is empty.
	 */
	public function test_can_do_captcha_returns_false_when_checkout_setting_empty() {
		// Configure provider.
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );

		// But don't enable on checkout.
		edd_update_option( 'recaptcha_checkout', '' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertFalse( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha returns true when configured with "always".
	 */
	public function test_can_do_captcha_returns_true_with_always_setting() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertTrue( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha returns true for guests when setting is "guests".
	 */
	public function test_can_do_captcha_returns_true_for_guests() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'guests' );

		// Ensure user is logged out.
		wp_set_current_user( 0 );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertTrue( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha returns false for logged-in users when setting is "guests".
	 */
	public function test_can_do_captcha_returns_false_for_logged_in_users_with_guests_setting() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'guests' );

		// Log in a user.
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertFalse( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha works with Turnstile provider.
	 */
	public function test_can_do_captcha_works_with_turnstile() {
		edd_update_option( 'captcha_provider', 'turnstile' );
		edd_update_option( 'turnstile_site_key', 'test_key' );
		edd_update_option( 'turnstile_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertTrue( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha is filterable with new filter.
	 */
	public function test_can_do_captcha_is_filterable() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		// Force disable via filter.
		add_filter( 'edd_can_captcha_checkout', '__return_false' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertFalse( $can_do_captcha );

		remove_filter( 'edd_can_captcha_checkout', '__return_false' );
	}

	/**
	 * Test backwards compatibility with old filter name.
	 */
	public function test_backwards_compatibility_with_old_filter() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		// Use old filter name.
		add_filter( 'edd_can_recaptcha_checkout', '__return_false' );

		// Expect the deprecation notice.
		$this->setExpectedDeprecated( 'edd_can_recaptcha_checkout' );

		$can_do_captcha = Utility::can_do_captcha();

		// Old filter should still work (backwards compatibility).
		$this->assertFalse( $can_do_captcha );

		remove_filter( 'edd_can_recaptcha_checkout', '__return_false' );
	}

	/**
	 * Test can_do_captcha passes provider to filter.
	 */
	public function test_can_do_captcha_passes_provider_to_filter() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		$filter_called = false;
		$provider_arg = null;

		add_filter( 'edd_can_captcha_checkout', function( $can_do, $setting, $provider ) use ( &$filter_called, &$provider_arg ) {
			$filter_called = true;
			$provider_arg = $provider;
			return $can_do;
		}, 10, 3 );

		Utility::can_do_captcha();

		$this->assertTrue( $filter_called );
		$this->assertInstanceOf( \EDD\Captcha\Providers\Provider::class, $provider_arg );

		remove_all_filters( 'edd_can_captcha_checkout' );
	}

	/**
	 * Test can_do_captcha with backwards compatible reCAPTCHA config.
	 */
	public function test_can_do_captcha_with_backwards_compatible_config() {
		// Old setup: just keys, no provider selection.
		edd_update_option( 'recaptcha_site_key', 'test_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		$can_do_captcha = Utility::can_do_captcha();

		// Should auto-detect reCAPTCHA and work.
		$this->assertTrue( $can_do_captcha );
	}

	/**
	 * Test can_do_captcha returns false when provider is set but not configured.
	 */
	public function test_can_do_captcha_returns_false_when_provider_not_configured() {
		// Set provider but forget to set keys.
		edd_update_option( 'captcha_provider', 'turnstile' );
		edd_update_option( 'recaptcha_checkout', 'always' );

		$can_do_captcha = Utility::can_do_captcha();

		$this->assertFalse( $can_do_captcha );
	}
}

