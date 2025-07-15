<?php

namespace EDD\Tests\Settings\Sanitize\Tabs\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Settings\Sanitize\Tabs\Taxes\Main;

class VatSection extends EDD_UnitTestCase {

	/**
	 * Teardown after each test to ensure the tax rates are reset.
	 */
	public function tearDown(): void {
		$_POST = array();
		edd_update_option( 'enable_taxes', false );
	}

	public function test_country_rate_fr_does_not_exist() {
		$this->assertEmpty(
			edd_get_tax_rate_by_location(
				array(
					'country' => 'FR',
				)
			)
		);
	}

	public function test_enable_taxes_empty_disables_vat() {
		$_POST['vat_enable'] = true;

		$this->assertSame(
			array(
				'vat_enable' => false,
			),
			Main::sanitize( $_POST )
		);
	}

	public function test_enable_taxes_does_not_enable_vat_if_disabled() {
		$_POST['enable_taxes'] = true;
		$_POST['vat_enable']   = false;

		$this->assertSame(
			array(
				'enable_taxes' => true,
				'vat_enable'   => false,
			),
			Main::sanitize( $_POST )
		);
	}

	public function test_enable_taxes_vat_enable_both_true() {
		$_POST['enable_taxes'] = true;
		$_POST['vat_enable']   = true;

		$this->assertSame( $_POST, Main::sanitize( $_POST ) );
	}

	public function test_legacy_tax_rates_exist() {
		edd_update_option( 'enable_taxes', true );
		edd_update_option( 'vat_enable', true );

		$this->assertEquals(
			20,
			edd_get_tax_rate_by_location(
				array(
					'country' => 'FR',
				)
			)->amount
		);

		$this->assertEquals(
			21,
			edd_get_tax_rate_by_location(
				array(
					'country' => 'LT',
				)
			)->amount
		);
	}
}
