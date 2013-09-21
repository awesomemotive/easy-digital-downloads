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
		$this->assertEquals( '20,000.20', edd_sanitize_amount( '20,000.20' ) );
	}

}
