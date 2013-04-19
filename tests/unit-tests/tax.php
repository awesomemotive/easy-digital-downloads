<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_tax
 */
class Tests_Taxes extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_use_taxes() {
		$this->assertFalse( edd_use_taxes() );
	}

	public function test_local_taxes_only() {
		$this->assertFalse( edd_local_taxes_only() );
	}

	public function test_opt_into_local_taxes() {
		edd_opt_into_local_taxes();
		$this->assertTrue( edd_local_tax_opted_in() );
	}

	public function test_opt_out_local_taxes() {
		$this->assertNull( edd_opt_out_local_taxes() );
	}

	public function test_taxes_one_prices() {
		$this->assertFalse( edd_taxes_on_prices() );
	}

	public function test_taxes_after_discounts() {
		$this->assertFalse( edd_taxes_after_discounts() );
	}

	public function test_get_tax_rate() {
		$this->assertInternalType( 'integer', edd_get_tax_rate() );
		$this->assertEquals( 0, edd_get_tax_rate() );
	}
}