<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_formatting
 */
class Tests_Formatting extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_sanitize_amount() {

		$this->assertEquals( '20000.20', edd_sanitize_amount( '20,000.20' ) );
		$this->assertEquals( '20.20', edd_sanitize_amount( '20.2' ) );
		$this->assertEquals( '25.42', edd_sanitize_amount( '25.42221112993' ) );

	}

	public function test_format_amount() {

		$this->assertEquals( '20,000.20', edd_format_amount( '20,000.20' ) );
	}

	public function test_currency_filter() {
		$this->assertEquals( '&#36;20,000.20', edd_currency_filter( '20,000.20' ) );
	}
}