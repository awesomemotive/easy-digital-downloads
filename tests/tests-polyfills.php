<?php
/**
 * Polyfills for WordPress and PHP functions which may not be available in all servers/versions.
 *
 * @package     EDD
 * @subpackage  Tests
 * @since       3.2.0
 */
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class Polyfills extends EDD_UnitTestCase {

	public function test_wp_readonly_exists() {
		$this->assertTrue( function_exists( 'wp_readonly' ) );
	}

	public function test_wp_readonly_true_returns_readonly_string() {
		$this->assertStringContainsString( 'readonly', wp_readonly( true, true, false ) );
	}

	public function test_cal_days_in_month_exists() {
		$this->assertTrue( function_exists( 'cal_days_in_month' ) );
	}

	public function test_cal_days_in_month_january_2021() {
		$this->assertEquals( 31, cal_days_in_month( CAL_GREGORIAN, 1, 2021 ) );
	}

	public function test_getallheaders_exists() {
		$this->assertTrue( function_exists( 'getallheaders' ) );
	}
}
