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

		global $edd_options;

		$this->assertEquals( '20,000.20', edd_format_amount( '20000.20' ) );

		$edd_options['thousands_separator'] = '.';
		$edd_options['decimal_separator'] = ',';

		update_option( 'edd_settings', $edd_options );

		$this->assertEquals( '20.000,20', edd_format_amount( '20000.20' ) );

		$edd_options['thousands_separator'] = ' ';
		$edd_options['decimal_separator'] = '.';

		update_option( 'edd_settings', $edd_options );

		$this->assertEquals( '20 000.20', edd_format_amount( '20000.20' ) );
	}

	public function test_currency_filter() {
		$this->assertEquals( '&#36;20,000.20', edd_currency_filter( '20,000.20' ) );
	}

	public function test_separators() {

		global $edd_options;

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( ' ', $thousands_sep );
		$this->assertEquals( '.', $thousands_sep );

		$edd_options['thousands_separator'] = '.';
		$edd_options['decimal_separator'] = ',';

		update_option( 'edd_settings', $edd_options );

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( '.', $thousands_sep );
		$this->assertEquals( ',', $thousands_sep );

		$edd_options['thousands_separator'] = ',';
		$edd_options['decimal_separator'] = '.';

		update_option( 'edd_settings', $edd_options );

		$thousands_sep = edd_get_option( 'thousands_separator', ',' );
		$decimal_sep   = edd_get_option( 'decimal_separator', '.' );

		$this->assertEquals( ',', $thousands_sep );
		$this->assertEquals( '.', $thousands_sep );

	} 
}