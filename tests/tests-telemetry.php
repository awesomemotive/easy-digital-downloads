<?php

namespace EDD\Tests;

use EDD\Telemetry\Data;
use EDD\Utils\ListHandler;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Telemetry Tests
 *
 * @covers EDD\Telemetry
 * @group edd_telemetry
 */
class Telemetry extends EDD_UnitTestCase {

	/**
	 * The data to send to the server.
	 *
	 * @var array
	 */
	private static $data;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		update_option( 'blogname', 'WordPress' );

		$telemetry_data = new Data();

		self::$data = $telemetry_data->get();
	}

	public function test_data_is_array() {
		$this->assertIsArray( self::$data );
	}

	public function test_data_is_not_empty() {
		$this->assertNotEmpty( self::$data );
	}

	public function test_data_contains_id() {
		$this->assertArrayHasKey( 'id', self::$data );
	}

	public function test_data_contains_environment() {
		$this->assertArrayHasKey( 'environment', self::$data );
	}

	public function test_data_contains_integrations() {
		$this->assertArrayHasKey( 'integrations', self::$data );
	}

	public function test_data_contains_licenses() {
		$this->assertArrayHasKey( 'licenses', self::$data );
	}

	public function test_data_contains_sales() {
		$this->assertArrayHasKey( 'sales', self::$data );
	}

	public function test_data_contains_refunds() {
		$this->assertArrayHasKey( 'refunds', self::$data );
	}

	public function test_data_contains_settings() {
		$this->assertArrayHasKey( 'settings', self::$data );
	}

	public function test_data_contains_stats() {
		$this->assertArrayHasKey( 'stats', self::$data );
	}

	public function test_data_contains_products() {
		$this->assertArrayHasKey( 'products', self::$data );
	}

	public function test_environment_contains_php_version() {
		$this->assertEquals( phpversion(), self::$data['environment']['php_version'] );
	}

	public function test_environment_contains_wp_version() {
		$version = get_bloginfo( 'version' );
		$version = explode( '-', $version );

		$this->assertEquals( reset( $version ), self::$data['environment']['wp_version'] );
	}

	public function test_environment_contains_edd_version() {
		$this->assertEquals( EDD_VERSION, self::$data['environment']['edd_version'] );
	}

	public function test_environment_contains_multiste() {
		$this->assertEquals( is_multisite(), self::$data['environment']['multisite'] );

		if ( is_multisite() ) {
			$this->assertEquals( 'subdirectory', self::$data['environment']['multisite_mode'] );
			$this->assertEquals( 0, self::$data['environment']['network_activated'] );
			$this->assertEquals( 1, self::$data['environment']['network_sites'] );
			$this->assertEquals( 0, self::$data['environment']['domain_mapping'] );
			$this->assertEquals( 1, self::$data['environment']['is_main_site'] );
		}
	}

	public function test_no_data_includes_admin_email() {
		$list_handler = new ListHandler( self::$data );
		$emails       = $list_handler->search( get_bloginfo( 'admin_email' ) );

		$this->assertFalse( $emails );
	}

	public function test_settings_currency_matches_store_currency() {
		$this->assertEquals( edd_get_currency(), self::$data['settings']['currency'] );
	}

	public function test_environment_theme_name_is_anonymized() {
		$this->assertEquals( 'WordPress', get_bloginfo( 'name' ) );
		$this->assertFalse( strpos( self::$data['environment']['active_theme'], 'WordPress' ) );
	}

	public function test_deprecated_class_instance_matches() {
		$tracking = new \EDD_Tracking();

		$this->assertTrue( $tracking instanceof \EDD\Telemetry\Tracking );
	}

	public function test_email_template_order_receipt_is_enabled() {
		$this->assertEquals( 1, self::$data['settings']['email_template_order_receipt'] );
	}
}
