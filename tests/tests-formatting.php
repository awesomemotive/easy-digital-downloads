<?php


/**
 * @group edd_formatting
 */
class Tests_Formatting extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_sanitize_amount() {

		$this->assertEquals( '20000.20', edd_sanitize_amount( '20,000.20' ) );
		$this->assertEquals( '22000.20', edd_sanitize_amount( '22 000.20' ) );
		$this->assertEquals( '20.20', edd_sanitize_amount( '20.2' ) );
		$this->assertEquals( '25.42', edd_sanitize_amount( '25.42221112993' ) );
		$this->assertEquals( '20.20', edd_sanitize_amount( '$20.2' ) );
		$this->assertEquals( '10.00', edd_sanitize_amount( '£10' ) );
		$this->assertEquals( '20.20', edd_sanitize_amount( '₱20.2' ) );
		$this->assertEquals( '2000.00', edd_sanitize_amount( '¥2000' ) );
		$this->assertEquals( '20.00', edd_sanitize_amount( 'Ð20' ) );

	}

	public function test_format_amount() {

		$this->assertEquals( '20,000.20', edd_format_amount( '20000.20' ) );

		edd_update_option( 'thousands_separator', '.' );
		edd_update_option( 'decimal_separator', ',' );

		$this->assertEquals( '20.000,20', edd_format_amount( '20000.20' ) );

		edd_update_option( 'thousands_separator', ' ' );
		edd_update_option( 'decimal_separator', '.' );

		$this->assertEquals( '20 000.20', edd_format_amount( '20000.20' ) );
	}

	public function test_currency_filter() {
		$this->assertEquals( '&#36;20,000.20', edd_currency_filter( '20,000.20' ) );
	}

	public function test_currency_symbol() {
		$this->assertEquals( '&#36;', edd_currency_symbol( 'USD' ) );
		$this->assertEquals( '&yen;', edd_currency_symbol( 'JPY' ) );
		$this->assertEquals( 'DKK', edd_currency_symbol( 'DKK' ) );
	}

	public function test_separators() {

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( ' ', $thousands_sep );
		$this->assertEquals( '.', $decimal_sep );

		edd_update_option( 'thousands_separator', '.' );
		edd_update_option( 'decimal_separator', ',' );

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( '.', $thousands_sep );
		$this->assertEquals( ',', $decimal_sep );

		edd_update_option( 'thousands_separator', ',' );
		edd_update_option( 'decimal_separator', '.' );

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( ',', $thousands_sep );
		$this->assertEquals( '.', $decimal_sep );

	}

	public function test_decimal_filter() {
		$initial_currency = edd_get_currency();

		$this->assertEquals( 2, edd_currency_decimal_filter() );

		edd_update_option( 'currency', 'RIAL' );
		$this->assertEquals( 0, edd_currency_decimal_filter() );

		edd_update_option( 'currency', 'JPY' );
		$this->assertEquals( 0, edd_currency_decimal_filter() );

		edd_update_option( 'currency', 'HUF' );
		$this->assertEquals( 0, edd_currency_decimal_filter() );

		edd_update_option( 'currency', 'TWD' );
		$this->assertEquals( 0, edd_currency_decimal_filter() );

		// Reset the option
		edd_update_option( 'currency', $initial_currency );
	}
}
