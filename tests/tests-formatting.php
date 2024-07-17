<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_formatting
 */
class Tests_Formatting extends EDD_UnitTestCase {

	public function test_sanitize_amount_empty_string() {
		$this->assertEquals( 0.00, edd_sanitize_amount( '' ) );
	}

	public function test_sanitize_amount_comma_thousands() {
		$this->assertEquals( '20000.20', edd_sanitize_amount( '20,000.20' ) );
	}

	public function test_sanitize_amount_space_thousands() {
		$this->assertEquals( '22000.20', edd_sanitize_amount( '22 000.20' ) );
	}

	public function test_sanitize_amount_incomplete_amount() {
		$this->assertEquals( '20.20', edd_sanitize_amount( '20.2' ) );
	}

	public function test_sanitize_amount_amount_too_long() {
		$this->assertEquals( '25.42', edd_sanitize_amount( '25.42221112993' ) );
	}

	public function test_sanitize_amount_includes_currency_usd() {
		$this->assertEquals( '20.20', edd_sanitize_amount( '$20.2' ) );
	}

	public function test_sanitize_amount_includes_currency_gpb() {
		$this->assertEquals( '10.00', edd_sanitize_amount( '£10' ) );
	}

	public function test_sanitize_amount_includes_currency_philippine() {
		$this->assertEquals( '20.20', edd_sanitize_amount( '₱20.2' ) );
	}

	public function test_sanitize_amount_includes_currency_yen() {
		$this->assertEquals( '2000.00', edd_sanitize_amount( '¥2000' ) );
	}

	public function test_sanitize_amount_includes_currency_doge() {
		$this->assertEquals( '20.00', edd_sanitize_amount( 'Ð20' ) );
	}

	public function test_sanitize_amount_negative_amount() {
		$this->assertEquals( -20.20, edd_sanitize_amount( '-20.2' ) );
	}

	public function test_sanitize_amount_negative_amount_with_currency() {
		$this->assertEquals( -20.20, edd_sanitize_amount( '-$20.2' ) );
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

	public function test_format_amount_typed() {
		$this->assertEquals( 20000.20, edd_format_amount( '20000.20', true, '', 'typed' ) );

		edd_update_option( 'thousands_separator', '.' );
		edd_update_option( 'decimal_separator', ',' );

		$this->assertEquals( 20000.20, edd_format_amount( '20000.20', true, '', 'typed' ) );

		edd_update_option( 'thousands_separator', ' ' );
		edd_update_option( 'decimal_separator', '.' );

		$this->assertEquals( 20000.20, edd_format_amount( '20000.20', true, '', 'typed' ) );
	}

	public function test_currency_filter() {
		$this->assertEquals( '&#36;20,000.20', edd_currency_filter( '20,000.20' ) );
	}

	public function test_currency_filter_invalid_currency() {
		// Prepare test data
		$price    = '19.99';
		$currency = '<img src="image.jpg" onerror="alert(\'0\')">';

		// Call the function with the test data
		$result = edd_currency_filter( $price, $currency );

		// Assert: ensure that the image tag is escaped in the result
		$this->assertStringNotContainsString( '<IMG="', $result );
		$this->assertStringNotContainsString( 'ONERROR="', $result );
		$this->assertStringContainsString( strtoupper( '&lt;img src=&quot;image.jpg&quot; onerror=&quot;alert(&#039;0&#039;)&quot;&gt;' ), $result );
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

	public function test_decimal_filter_with_currency_passed_should_return_0() {
		$this->assertSame( 0, edd_currency_decimal_filter( 2, 'RIAL' ) );

		$this->assertSame( 0, edd_currency_decimal_filter( 2, 'HUF' ) );

		$this->assertSame( 0, edd_currency_decimal_filter( 2, 'JPY' ) );
	}

	public function test_address_type_label_billing() {
		$this->assertSame( 'Billing', edd_get_address_type_label( 'billing' ) );
	}

	public function test_address_type_label_default() {
		$this->assertSame( 'Billing', edd_get_address_type_label() );
	}

	public function test_address_type_label_unregistered() {
		$this->assertSame( 'shipping', edd_get_address_type_label( 'shipping' ) );
	}

	public function test_address_type_label_unregistered_two_words() {
		$this->assertSame( 'test type', edd_get_address_type_label( 'test type' ) );
	}
}
