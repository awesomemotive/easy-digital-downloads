<?php

namespace EDD\Tests\Settings;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Registration extends EDD_UnitTestCase {

	private static $settings;

	public function test_settings_is_array() {
		$this->assertIsArray( $this->get_settings() );
	}

	public function test_settings_has_general_key() {
		$this->assertArrayHasKey( 'general', $this->get_settings() );
	}

	public function test_settings_has_gateways_key() {
		$this->assertArrayHasKey( 'gateways', $this->get_settings() );
	}

	public function test_settings_has_emails_key() {
		$this->assertArrayHasKey( 'emails', $this->get_settings() );
	}

	public function test_settings_has_marketing_key() {
		$this->assertArrayHasKey( 'marketing', $this->get_settings() );
	}

	public function test_settings_has_taxes_key() {
		$this->assertArrayHasKey( 'taxes', $this->get_settings() );
	}

	public function test_settings_has_extensions_key() {
		$this->assertArrayHasKey( 'extensions', $this->get_settings() );
	}

	public function test_settings_has_licenses_key() {
		$this->assertArrayHasKey( 'licenses', $this->get_settings() );
	}

	public function test_settings_has_misc_key() {
		$this->assertArrayHasKey( 'misc', $this->get_settings() );
	}

	public function test_settings_has_privacy_key() {
		$this->assertArrayHasKey( 'privacy', $this->get_settings() );
	}

	public function test_settings_does_not_have_styles_key() {
		$this->assertArrayNotHasKey( 'styles', $this->get_settings() );
	}

	public function test_settings_misc_main_has_session_handling_key() {
		$this->assertArrayHasKey( 'session_handling', $this->get_settings()['misc']['main'] );
		$this->assertEquals( 'Session Handling', $this->get_settings()['misc']['main']['session_handling']['name'] );
		$this->assertEquals( 'select', $this->get_settings()['misc']['main']['session_handling']['type'] );
	}

	public function test_settings_misc_main_array_keys() {
		$keys = array_keys( $this->get_settings()['misc']['main'] );

		$this->assertTrue( in_array( 'debug_mode', $keys ) );
		$this->assertTrue( in_array( 'session_handling', $keys ) );
		$this->assertTrue( in_array( 'disable_styles', $keys ) );
		$this->assertTrue( in_array( 'item_quantities', $keys ) );
		$this->assertTrue( in_array( 'uninstall_on_delete', $keys ) );
	}

	private function get_settings() {
		if ( is_null( self::$settings ) ) {
			self::$settings = edd_get_registered_settings();
		}

		return self::$settings;
	}
}
