<?php

namespace EDD\Tests\Settings;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Setting extends EDD_UnitTestCase {
	public function test_get_settings() {
		$this->assertIsArray( edd_get_settings() );
	}

	public function test_get_setting_return_default() {
		$this->assertSame( 'default', edd_get_option( 'non_existent_setting', 'default' ) );
	}

	public function test_get_setting_existing_numeric_non_zero() {
		edd_update_option( 'existing_setting', 1 );
		$this->assertSame( 1, edd_get_option( 'existing_setting', 0 ) );
	}

	public function test_get_setting_existing_numeric_zero() {
		edd_update_option( 'existing_setting', 0 );
		$this->assertSame( 0, edd_get_option( 'existing_setting', 1 ) );
	}

	public function test_get_setting_existing_string() {
		edd_update_option( 'existing_setting', 'string' );
		$this->assertSame( 'string', edd_get_option( 'existing_setting', 'default' ) );
	}

	public function test_update_option_no_setting() {
		$this->assertFalse( edd_update_option( '', 'value' ) );
	}

	public function test_update_option_empty_after_sanitization() {
		add_filter( 'edd_update_option_fake_option', function( $value ) { return ''; } );
		$this->assertFalse( edd_update_option( 'fake_option', 'value' ) );
		remove_filter( 'edd_update_option_fake_option', function( $value ) { return ''; } );
	}

	public function test_delete_option_no_setting() {
		$this->assertFalse( edd_delete_option( '' ) );
	}
}
