<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Taxes\Rates;

class RatesSection extends EDD_UnitTestCase {

	/**
	 * Teardown after each test to ensure the tax rates are reset.
	 */
	public function tearDown(): void {
		parent::_delete_all_edd_data();
	}

	public function test_tax_rates_not_set() {
		/**
		 * Tax rates are expected to be sent in the $_POST array.
		 *
		 * If this is not defined, we'll just return the input array as is.
		 */
		$this->assertSame(
			array(
				'invalid_key' => array(),
			),
			Rates::sanitize(
				array(
					'invalid_key' => array(),
				)
			)
		);
	}

	public function test_empty_tax_rates() {
		// Set the tax rates in $_POST.
		$_POST['tax_rates'] = array();

		// Ensure the input array comes out the same.
		$this->assertSame(
			array(
				'different_key' => array(),
			),
			Rates::sanitize(
				array(
					'different_key' => array(),
				)
			)
		);

		$this->assertSame( 0, count( edd_get_adjustments( array( 'type' => 'tax_rate' ) ) ) );
	}

	public function test_parsing_tax_rates() {
		// Set the tax rates in $_POST.
		$_POST['tax_rates'] = array(
			array(
				'global'  => '1',
				'country' => 'US',
				'rate'    => '7.52',
			),
			array(
				'country' => 'GB',
				'state'   => 'GB-WRX',
				'rate'    => '20',
			)
		);

		// Ensure the input array comes out the same.
		$this->assertSame(
			array( 'different_key' => array() ),
			Rates::sanitize( array( 'different_key' => array() ) )
		);

		// Verify that the tax rates were saved.
		$tax_rates = edd_get_adjustments(
			array(
				'type'    => 'tax_rate',
				'orderby' => 'id',
				'order'   => 'ASC'
			)
		);
		$this->assertSame( 2, count( $tax_rates ) );
		$this->assertSame( 'US', $tax_rates[0]->name );
		$this->assertSame('country', $tax_rates[0]->scope );
		$this->assertSame( floatval( 7.52 ), floatval( $tax_rates[0]->amount ) );
		$this->assertSame( 'GB', $tax_rates[1]->name );
		$this->assertSame( 'region', $tax_rates[1]->scope );
		$this->assertSame( floatval( 20 ), floatval( $tax_rates[1]->amount ) );
		$this->assertSame( 'GB-WRX', $tax_rates[1]->description );
	}

}
