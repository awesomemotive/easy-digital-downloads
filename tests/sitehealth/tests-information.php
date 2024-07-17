<?php

namespace EDD\Tests\SiteHealth;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Information extends EDD_UnitTestCase {

	private static $test;

	public function test_edd_general_key_exists() {
		$this->assertArrayHasKey( 'edd_general', $this->get_test() );
	}

	public function test_edd_tables_key_exists() {
		$this->assertArrayHasKey( 'edd_tables', $this->get_test() );
	}

	public function test_edd_pages_key_exists() {
		$this->assertArrayHasKey( 'edd_pages', $this->get_test() );
	}

	public function test_edd_templates_key_exists() {
		$this->assertArrayHasKey( 'edd_templates', $this->get_test() );
	}

	public function test_edd_gateways_key_exists() {
		$this->assertArrayHasKey( 'edd_gateways', $this->get_test() );
	}

	public function test_edd_taxes_key_exists() {
		$this->assertArrayHasKey( 'edd_taxes', $this->get_test() );
	}

	public function test_edd_sessions_key_exists() {
		$this->assertArrayHasKey( 'edd_sessions', $this->get_test() );
	}

	public function test_edd_cron_key_exists() {
		$this->assertArrayHasKey( 'edd_cron', $this->get_test() );
	}

	private function get_test() {
		if ( is_null( self::$test ) ) {
			$information = new \EDD\Admin\SiteHealth\Information();
			self::$test = $information->get_data( array() );
		}

		return self::$test;
	}
}
