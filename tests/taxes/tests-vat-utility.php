<?php
namespace EDD\Tests\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Taxes\VAT\Utility;

class VATUtility extends EDD_UnitTestCase {

	/**
	 * Set up for each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip all tests in this class if EDD Pro is not available
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. VAT utility tests require EDD Pro.' );
		}
	}

	/**
	 * Test can_reverse_charge_vat method.
	 */
	public function test_can_reverse_charge_vat_with_eu_country() {
		// Test with a valid EU country
		$this->assertTrue( Utility::can_reverse_charge_vat( 'DE' ) );
	}

	public function test_can_reverse_charge_vat_with_non_eu_country() {
		// Test with a non-EU country
		$this->assertFalse( Utility::can_reverse_charge_vat( 'US' ) );
	}

	/**
	 * Test can_reverse_charge_vat when base country is EU and reverse charge is disabled in base country.
	 * This should hit the array_diff line and exclude the base country.
	 */
	public function test_can_reverse_charge_vat_excludes_base_country_when_reverse_charge_disabled() {
		// Set up Germany as shop country
		$original_shop_country = edd_get_shop_country();
		add_filter( 'edd_shop_country', function() {
			return 'DE'; // Germany as base country
		} );

		// Ensure reverse charge in base country is disabled (default behavior)
		edd_update_option( 'edd_vat_reverse_charge_base_country', false );

		// The base country (DE) should return false since it's excluded via array_diff
		$this->assertFalse( Utility::can_reverse_charge_vat( 'DE' ) );

		// Other EU countries should still return true
		$this->assertTrue( Utility::can_reverse_charge_vat( 'FR' ) );

		// Clean up
		remove_all_filters( 'edd_shop_country' );
		edd_delete_option( 'edd_vat_reverse_charge_base_country' );
	}

	/**
	 * Test can_reverse_charge_vat when base country is set via edd_vat_address_country option.
	 */
	public function test_can_reverse_charge_vat_with_custom_base_country_setting() {
		// Set up shop country
		add_filter( 'edd_shop_country', function() {
			return 'FR'; // France as shop country
		} );

		// Set the edd_vat_address_country option to return Italy
		edd_update_option( 'edd_vat_address_country', 'IT' );

		// Ensure reverse charge in base country is disabled
		edd_update_option( 'edd_vat_reverse_charge_base_country', false );

		// Italy (the API country) should be excluded, not France (the shop country)
		$this->assertFalse( Utility::can_reverse_charge_vat( 'IT' ) );
		$this->assertTrue( Utility::can_reverse_charge_vat( 'FR' ) );

		// Clean up
		remove_all_filters( 'edd_shop_country' );
		edd_delete_option( 'edd_vat_address_country' );
		edd_delete_option( 'edd_vat_reverse_charge_base_country' );
	}

	/**
	 * Test can_reverse_charge_vat when base country for API is set to 'EU' - should fall back to shop country.
	 */
	public function test_can_reverse_charge_vat_with_eu_as_base_country() {
		// Set up shop country
		add_filter( 'edd_shop_country', function() {
			return 'ES'; // Spain as shop country
		} );

		// Set the edd_vat_address_country option to return 'EU'
		edd_update_option( 'edd_vat_address_country', 'EU' );

		// Ensure reverse charge in base country is disabled
		edd_update_option( 'edd_vat_reverse_charge_base_country', false );

		// Should fall back to shop country (ES) and exclude it
		$this->assertFalse( Utility::can_reverse_charge_vat( 'ES' ) );
		$this->assertTrue( Utility::can_reverse_charge_vat( 'DE' ) );

		// Clean up
		remove_all_filters( 'edd_shop_country' );
		edd_delete_option( 'edd_vat_address_country' );
		edd_delete_option( 'edd_vat_reverse_charge_base_country' );
	}

	/**
	 * Test can_reverse_charge_vat when reverse charge is enabled in base country.
	 * In this case, array_diff should NOT be called and base country should return true.
	 */
	public function test_can_reverse_charge_vat_includes_base_country_when_reverse_charge_enabled() {
		// Set up an EU country as the base/shop country
		add_filter( 'edd_shop_country', function() {
			return 'DE'; // Germany as base country
		} );

		// Enable reverse charge in base country
		edd_update_option( 'edd_vat_reverse_charge_base_country', true );

		// The base country (DE) should return true since array_diff is not called
		$this->assertTrue( Utility::can_reverse_charge_vat( 'DE' ) );

		// Other EU countries should also return true
		$this->assertTrue( Utility::can_reverse_charge_vat( 'FR' ) );

		// Clean up
		remove_all_filters( 'edd_shop_country' );
		edd_delete_option( 'edd_vat_reverse_charge_base_country' );
	}

	/**
	 * Test format_edd_address method.
	 */
	public function test_format_edd_address_with_complete_address() {
		$address = array(
			'line1'   => '123 Test Street',
			'line2'   => 'Suite 456',
			'city'    => 'Test City',
			'state'   => 'CA',
			'zip'     => '12345',
			'country' => 'US',
		);

		$expected = "123 Test Street\nSuite 456\nTest City 12345 California\nUnited States";
		$this->assertEquals( $expected, Utility::format_edd_address( $address ) );
	}

	public function test_format_edd_address_with_partial_address() {
		$address = array(
			'line1'   => '123 Test Street',
			'city'    => 'Test City',
			'country' => 'US',
		);

		$expected = "123 Test Street\nTest City\nUnited States";
		$this->assertEquals( $expected, Utility::format_edd_address( $address ) );
	}

	/**
	 * Test array_insert_after method.
	 */
	public function test_array_insert_after_with_valid_key() {
		$original_array = array(
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		);

		$expected = array(
			'key1' => 'value1',
			'key2' => 'value2',
			'new_key' => 'new_value',
			'key3' => 'value3',
		);

		$result = Utility::array_insert_after( 'key2', $original_array, 'new_key', 'new_value' );
		$this->assertEquals( $expected, $result );
	}

	public function test_array_insert_after_with_invalid_key() {
		$original_array = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);

		$result = Utility::array_insert_after( 'invalid_key', $original_array, 'new_key', 'new_value' );
		$this->assertEquals( $original_array, $result );
	}

	/**
	 * Test get_eu_countries_list method.
	 */
	public function test_get_eu_countries_list() {
		$countries = Utility::get_eu_countries_list();
		$this->assertIsArray( $countries );
		$this->assertArrayHasKey( 'DE', $countries ); // Germany should be in EU list
		$this->assertArrayHasKey( 'FR', $countries ); // France should be in EU list
	}

	/**
	 * Test get_country_list method.
	 */
	public function test_get_country_list_with_moss() {
		$countries = Utility::get_country_list( true );
		$this->assertIsArray( $countries );
		$this->assertArrayHasKey( 'EU', $countries );
		$this->assertArrayHasKey( 'XI', $countries );
	}

	public function test_get_country_list_without_moss() {
		$countries = Utility::get_country_list( false );
		$this->assertIsArray( $countries );
		$this->assertArrayHasKey( 'XI', $countries );
		$this->assertArrayNotHasKey( 'EU', $countries );
	}

	/**
	 * Test get_country_for_api method.
	 */
	public function test_get_country_for_api() {
		edd_update_option( 'base_country', 'GB' );
		$country = Utility::get_country_for_api();
		$this->assertEquals( edd_get_option( 'edd_vat_address_country', edd_get_shop_country() ), $country );
		edd_delete_option( 'base_country' );
	}

	public function test_get_country_for_api_without_default() {
		$country = Utility::get_country_for_api();
		$this->assertEquals( edd_get_option( 'edd_vat_address_country', 'US' ), $country );
	}
}
