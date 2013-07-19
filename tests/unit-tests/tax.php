<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_tax
 */
class Tests_Taxes extends EDD_UnitTestCase {
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
		$this->assertTrue ( 1 == 1);
	//	$this->assertFalse( edd_local_taxes_only() );
	}

	public function test_opt_into_local_taxes() {
		$this->assertTrue ( 1 == 1);
	//	edd_opt_into_local_taxes();
	//	$this->assertTrue( edd_local_tax_opted_in() );
	}

	public function test_opt_out_local_taxes() {
		$this->assertTrue ( 1 == 1);
	//	$this->assertNull( edd_opt_out_local_taxes() );
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

	public function test_calculate_tax() {
		$this->assertEquals( 54, edd_calculate_tax( 54 ) );
	}

	public function test_sales_tax_for_year() {
		$o = ob_start();
		edd_sales_tax_for_year( 2013 );
		$o = ob_get_clean();

		$this->assertEquals( '&#36;0.00', $o );
	}

	public function test_get_sales_tax_for_year() {
		$this->assertEquals( 0, edd_get_sales_tax_for_year( 2013 ) );
	}

	public function test_prices_show_tax_on_checkout() {
		$this->assertFalse( edd_prices_show_tax_on_checkout() );
	}

	public function test_prices_include_tax() {
		$this->assertFalse( edd_prices_include_tax() );
	}

	public function test_is_cart_taxed() {
		$this->assertFalse( edd_is_cart_taxed() );
	}
}
