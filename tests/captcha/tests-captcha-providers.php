<?php
/**
 * Tests for Captcha Providers
 *
 * @group edd_captcha
 * @group edd_captcha_providers
 * @group edd_pro
 */

namespace EDD\Tests\Captcha;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Captcha\Providers\Provider;
use EDD\Captcha\Providers\Recaptcha;
use EDD\Captcha\Providers\Turnstile;

class Providers extends EDD_UnitTestCase {

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
		edd_delete_option( 'turnstile_site_key' );
		edd_delete_option( 'turnstile_secret_key' );
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
		edd_delete_option( 'turnstile_site_key' );
		edd_delete_option( 'turnstile_secret_key' );
	}

	/**
	 * Clear the provider cache.
	 */
	private function clear_provider_cache() {
		// Use reflection to reset the static cache.
		$reflection = new \ReflectionClass( Provider::class );
		$property = $reflection->getProperty( 'providers' );
		$property->setAccessible( true );
		$property->setValue( null, array() );
	}

	/**
	 * Test that get_available_providers returns all providers.
	 */
	public function test_get_available_providers_returns_all_providers() {
		$providers = Provider::get_available_providers();

		$this->assertIsArray( $providers );
		$this->assertArrayHasKey( 'recaptcha', $providers );
		$this->assertArrayHasKey( 'turnstile', $providers );
		$this->assertInstanceOf( Recaptcha::class, $providers['recaptcha'] );
		$this->assertInstanceOf( Turnstile::class, $providers['turnstile'] );
	}

	/**
	 * Test that get_available_providers is filterable.
	 */
	public function test_get_available_providers_is_filterable() {
		add_filter( 'edd_captcha_providers', function( $providers ) {
			$providers['custom'] = new Recaptcha(); // Just for testing.
			return $providers;
		} );

		$providers = Provider::get_available_providers();

		$this->assertArrayHasKey( 'custom', $providers );

		remove_all_filters( 'edd_captcha_providers' );
	}

	/**
	 * Test that get_provider_by_id returns correct provider.
	 */
	public function test_get_provider_by_id_returns_correct_provider() {
		$recaptcha = Provider::get_provider_by_id( 'recaptcha' );
		$turnstile = Provider::get_provider_by_id( 'turnstile' );

		$this->assertInstanceOf( Recaptcha::class, $recaptcha );
		$this->assertInstanceOf( Turnstile::class, $turnstile );
	}

	/**
	 * Test that get_provider_by_id returns null for invalid ID.
	 */
	public function test_get_provider_by_id_returns_null_for_invalid_id() {
		$provider = Provider::get_provider_by_id( 'invalid_provider' );

		$this->assertNull( $provider );
	}

	/**
	 * Test that get_active_provider returns null when no provider is configured.
	 */
	public function test_get_active_provider_returns_null_when_not_configured() {
		$provider = Provider::get_active_provider();

		$this->assertNull( $provider );
	}

	/**
	 * Test that get_active_provider returns provider when configured.
	 */
	public function test_get_active_provider_returns_configured_provider() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'test_site_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret_key' );

		$provider = Provider::get_active_provider();

		$this->assertInstanceOf( Recaptcha::class, $provider );
		$this->assertEquals( 'recaptcha', $provider->get_id() );
	}

	/**
	 * Test backwards compatibility - auto-detects reCAPTCHA when keys exist.
	 */
	public function test_backwards_compatibility_auto_detects_recaptcha() {
		// Don't set captcha_provider, just set the old reCAPTCHA keys.
		edd_update_option( 'recaptcha_site_key', 'test_site_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret_key' );

		$provider = Provider::get_active_provider();

		$this->assertInstanceOf( Recaptcha::class, $provider );
		$this->assertEquals( 'recaptcha', $provider->get_id() );
	}

	/**
	 * Test that get_active_provider returns null if provider not configured properly.
	 */
	public function test_get_active_provider_returns_null_if_keys_missing() {
		edd_update_option( 'captcha_provider', 'recaptcha' );
		// Don't set the keys.

		$provider = Provider::get_active_provider();

		$this->assertNull( $provider );
	}

	/**
	 * Test reCAPTCHA provider ID.
	 */
	public function test_recaptcha_provider_id() {
		$provider = new Recaptcha();

		$this->assertEquals( 'recaptcha', $provider->get_id() );
	}

	/**
	 * Test reCAPTCHA provider name.
	 */
	public function test_recaptcha_provider_name() {
		$provider = new Recaptcha();

		$this->assertEquals( 'reCAPTCHA v3', $provider->get_name() );
	}

	/**
	 * Test reCAPTCHA provider keys.
	 */
	public function test_recaptcha_provider_keys() {
		edd_update_option( 'recaptcha_site_key', 'test_site_key_123' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret_key_456' );

		$provider = new Recaptcha();

		$this->assertEquals( 'test_site_key_123', $provider->get_key() );
		$this->assertEquals( 'test_secret_key_456', $provider->get_secret_key() );
	}

	/**
	 * Test reCAPTCHA provider is_configured.
	 */
	public function test_recaptcha_is_configured_when_keys_set() {
		edd_update_option( 'recaptcha_site_key', 'test_site_key' );
		edd_update_option( 'recaptcha_secret_key', 'test_secret_key' );

		$provider = new Recaptcha();

		$this->assertTrue( $provider->is_configured() );
	}

	/**
	 * Test reCAPTCHA provider is not configured when keys missing.
	 */
	public function test_recaptcha_is_not_configured_when_keys_missing() {
		$provider = new Recaptcha();

		$this->assertFalse( $provider->is_configured() );
	}

	/**
	 * Test reCAPTCHA script URL.
	 */
	public function test_recaptcha_script_url() {
		edd_update_option( 'recaptcha_site_key', 'test_key_123' );

		$provider = new Recaptcha();
		$url = $provider->get_script_url();

		$this->assertStringContainsString( 'google.com/recaptcha/api.js', $url );
		$this->assertStringContainsString( 'render=test_key_123', $url );
	}

	/**
	 * Test reCAPTCHA script handle.
	 */
	public function test_recaptcha_script_handle() {
		$provider = new Recaptcha();

		$this->assertEquals( 'google-recaptcha', $provider->get_script_handle() );
	}

	/**
	 * Test reCAPTCHA script version.
	 */
	public function test_recaptcha_script_version() {
		$provider = new Recaptcha();

		// Should use the EDD version.
		$this->assertNotNull( $provider->get_script_version() );
	}

	/**
	 * Test reCAPTCHA provider settings.
	 */
	public function test_recaptcha_provider_settings() {
		$provider = new Recaptcha();
		$settings = $provider->get_settings();

		$this->assertIsArray( $settings );
		$this->assertArrayHasKey( 'recaptcha', $settings );
		$this->assertArrayHasKey( 'recaptcha_site_key', $settings );
		$this->assertArrayHasKey( 'recaptcha_secret_key', $settings );

		// Check that settings have the required keys.
		$this->assertArrayHasKey( 'id', $settings['recaptcha'] );
		$this->assertArrayHasKey( 'name', $settings['recaptcha'] );
		$this->assertArrayHasKey( 'type', $settings['recaptcha'] );
		$this->assertArrayHasKey( 'class', $settings['recaptcha'] );
	}

	/**
	 * Test Turnstile provider ID.
	 */
	public function test_turnstile_provider_id() {
		$provider = new Turnstile();

		$this->assertEquals( 'turnstile', $provider->get_id() );
	}

	/**
	 * Test Turnstile provider name.
	 */
	public function test_turnstile_provider_name() {
		$provider = new Turnstile();

		$this->assertEquals( 'Cloudflare Turnstile', $provider->get_name() );
	}

	/**
	 * Test Turnstile provider keys.
	 */
	public function test_turnstile_provider_keys() {
		edd_update_option( 'turnstile_site_key', 'turnstile_site_123' );
		edd_update_option( 'turnstile_secret_key', 'turnstile_secret_456' );

		$provider = new Turnstile();

		$this->assertEquals( 'turnstile_site_123', $provider->get_key() );
		$this->assertEquals( 'turnstile_secret_456', $provider->get_secret_key() );
	}

	/**
	 * Test Turnstile provider is_configured.
	 */
	public function test_turnstile_is_configured_when_keys_set() {
		edd_update_option( 'turnstile_site_key', 'test_site_key' );
		edd_update_option( 'turnstile_secret_key', 'test_secret_key' );

		$provider = new Turnstile();

		$this->assertTrue( $provider->is_configured() );
	}

	/**
	 * Test Turnstile provider is not configured when keys missing.
	 */
	public function test_turnstile_is_not_configured_when_keys_missing() {
		$provider = new Turnstile();

		$this->assertFalse( $provider->is_configured() );
	}

	/**
	 * Test Turnstile script URL.
	 */
	public function test_turnstile_script_url() {
		$provider = new Turnstile();
		$url = $provider->get_script_url();

		$this->assertEquals( 'https://challenges.cloudflare.com/turnstile/v0/api.js', $url );
	}

	/**
	 * Test Turnstile script handle.
	 */
	public function test_turnstile_script_handle() {
		$provider = new Turnstile();

		$this->assertEquals( 'cloudflare-turnstile', $provider->get_script_handle() );
	}

	/**
	 * Test Turnstile script version returns null.
	 */
	public function test_turnstile_script_version_returns_null() {
		$provider = new Turnstile();

		// Turnstile should return null to prevent version query parameter.
		$this->assertNull( $provider->get_script_version() );
	}

	/**
	 * Test Turnstile provider settings.
	 */
	public function test_turnstile_provider_settings() {
		$provider = new Turnstile();
		$settings = $provider->get_settings();

		$this->assertIsArray( $settings );
		$this->assertArrayHasKey( 'turnstile', $settings );
		$this->assertArrayHasKey( 'turnstile_site_key', $settings );
		$this->assertArrayHasKey( 'turnstile_secret_key', $settings );

		// Check that settings have the required keys.
		$this->assertArrayHasKey( 'id', $settings['turnstile'] );
		$this->assertArrayHasKey( 'name', $settings['turnstile'] );
		$this->assertArrayHasKey( 'type', $settings['turnstile'] );
		$this->assertArrayHasKey( 'class', $settings['turnstile'] );
	}

	/**
	 * Test that provider settings include conditional CSS classes.
	 */
	public function test_provider_settings_include_conditional_classes() {
		$provider = new Recaptcha();
		$settings = $provider->get_settings();

		// All settings should have the conditional class.
		foreach ( $settings as $setting ) {
			if ( isset( $setting['class'] ) ) {
				$this->assertStringContainsString( 'edd-requires', $setting['class'] );
				$this->assertStringContainsString( 'edd-requires__captcha_provider-recaptcha', $setting['class'] );
			}
		}
	}

	/**
	 * Test that switching providers works correctly.
	 */
	public function test_switching_providers() {
		// Set up reCAPTCHA.
		edd_update_option( 'captcha_provider', 'recaptcha' );
		edd_update_option( 'recaptcha_site_key', 'recaptcha_key' );
		edd_update_option( 'recaptcha_secret_key', 'recaptcha_secret' );

		$provider = Provider::get_active_provider();
		$this->assertInstanceOf( Recaptcha::class, $provider );

		// Clear cache and switch to Turnstile.
		$this->clear_provider_cache();
		edd_update_option( 'captcha_provider', 'turnstile' );
		edd_update_option( 'turnstile_site_key', 'turnstile_key' );
		edd_update_option( 'turnstile_secret_key', 'turnstile_secret' );

		$provider = Provider::get_active_provider();
		$this->assertInstanceOf( Turnstile::class, $provider );
	}

	/**
	 * Test that provider validation methods exist.
	 */
	public function test_providers_have_validate_method() {
		$recaptcha = new Recaptcha();
		$turnstile = new Turnstile();

		$this->assertTrue( method_exists( $recaptcha, 'validate' ) );
		$this->assertTrue( method_exists( $turnstile, 'validate' ) );
	}

	/**
	 * Test that provider cache persists between calls.
	 */
	public function test_provider_cache_persists() {
		$providers1 = Provider::get_available_providers();
		$providers2 = Provider::get_available_providers();

		// Should return the same instances (cached).
		$this->assertSame( $providers1['recaptcha'], $providers2['recaptcha'] );
		$this->assertSame( $providers1['turnstile'], $providers2['turnstile'] );
	}

	/**
	 * Test that all providers implement required abstract methods.
	 */
	public function test_providers_implement_required_methods() {
		$providers = Provider::get_available_providers();

		foreach ( $providers as $provider ) {
			$this->assertTrue( method_exists( $provider, 'get_id' ) );
			$this->assertTrue( method_exists( $provider, 'get_name' ) );
			$this->assertTrue( method_exists( $provider, 'get_key' ) );
			$this->assertTrue( method_exists( $provider, 'get_secret_key' ) );
			$this->assertTrue( method_exists( $provider, 'is_configured' ) );
			$this->assertTrue( method_exists( $provider, 'get_script_url' ) );
			$this->assertTrue( method_exists( $provider, 'get_script_handle' ) );
			$this->assertTrue( method_exists( $provider, 'validate' ) );
			$this->assertTrue( method_exists( $provider, 'get_settings' ) );
		}
	}
}

