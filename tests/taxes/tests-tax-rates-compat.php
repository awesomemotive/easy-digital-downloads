<?php
/**
 * Tests covering `update_option( 'edd_get_tax_rates' )`.
 * @group edd_tax
 */
namespace EDD\Tests\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

 class Test_Tax_Rates_Compat extends EDD_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		$tax_rates = array(
			array(
				'country' => 'US',
				'state'   => 'AL',
				'rate'    => 15,
			),
			array(
				'country' => 'US',
				'state'   => 'AZ',
				'rate'    => .15,
			),
			array(
				'country' => 'US',
				'state'   => 'TN',
				'rate'    => 9.25,
			),
			array(
				'country' => 'NO',
				'state'   => '',
				'rate'    => 25,
				'global'  => 1,
			),
		);

		update_option( 'edd_tax_rates', $tax_rates );
		edd_update_option( 'enable_taxes', true );
	}

	public static function wpTearDownAfterClass() {
		delete_option( 'edd_tax_rates' );
		edd_update_option( 'enable_taxes', false );
	}

	public function test_get_tax_rate_AZ_equals_0015() {
		$this->assertEquals( 0.0015, edd_get_tax_rate( 'US', 'AZ' ) );
	}

	public function test_get_tax_rate_TN_equals_0925() {
		$this->assertEquals( 0.0925, edd_get_tax_rate( 'US', 'TN' ) );
	}

	public function test_get_tax_rate_NO_equals_25() {
		$this->assertEquals( .25, edd_get_tax_rate( 'NO', 'Norge' ) );
	}
}
