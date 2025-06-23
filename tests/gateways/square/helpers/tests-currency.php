<?php
/**
 * Currency Helper Tests
 *
 * @package   EDD\Tests\Gateways\Square\Helpers
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.4.0
 */

namespace EDD\Tests\Gateways\Square\Helpers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Gateways\Square\Helpers\Currency as CurrencyHelper;

class Currency extends EDD_UnitTestCase {

	public function test_get_currency_default() {
		$currency = CurrencyHelper::get_currency();
		$this->assertEquals( 'USD', $currency );
	}

	public function test_get_currency_lowercase() {
		$currency = CurrencyHelper::get_currency( 'usd' );
		$this->assertEquals( 'USD', $currency );
	}

	public function test_get_currency_usd() {
		$currency = CurrencyHelper::get_currency( 'USD' );
		$this->assertEquals( 'USD', $currency );
	}

	public function test_get_currency_eur() {
		$currency = CurrencyHelper::get_currency( 'EUR' );
		$this->assertEquals( 'EUR', $currency );
	}

	public function test_get_currency_gbp() {
		$currency = CurrencyHelper::get_currency( 'GBP' );
		$this->assertEquals( 'GBP', $currency );
	}

	public function test_get_currency_jpy() {
		$currency = CurrencyHelper::get_currency( 'JPY' );
		$this->assertEquals( 'JPY', $currency );
	}

	public function test_get_currency_cad() {
		$currency = CurrencyHelper::get_currency( 'CAD' );
		$this->assertEquals( 'CAD', $currency );
	}

	public function test_get_currency_aud() {
		$currency = CurrencyHelper::get_currency( 'AUD' );
		$this->assertEquals( 'AUD', $currency );
	}

	public function test_is_zero_decimal_currency_default() {
		$is_zero_decimal = CurrencyHelper::is_zero_decimal_currency();
		$this->assertFalse( $is_zero_decimal );
	}

	public function test_is_zero_decimal_currency_usd() {
		$is_zero_decimal = CurrencyHelper::is_zero_decimal_currency( 'USD' );
		$this->assertFalse( $is_zero_decimal );
	}

	public function test_is_zero_decimal_currency_eur() {
		$is_zero_decimal = CurrencyHelper::is_zero_decimal_currency( 'JPY' );
		$this->assertTrue( $is_zero_decimal );
	}
}
