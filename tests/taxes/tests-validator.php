<?php
/**
 * Tests for Tax Validator
 *
 * @group edd_taxes
 * @group edd_validator
 */
namespace EDD\Tests\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Taxes\VAT\Result;
use EDD\Pro\Taxes\VAT\Validator as VATValidator;

class Validator extends EDD_UnitTestCase {

	/**
	 * Test fixtures.
	 */
	protected $valid_eu_country = 'DE';
	protected $invalid_country = 'US';
	protected $valid_vat_number = 'DE123456789';

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. VAT validator tests require EDD Pro.' );
		}

		parent::setUp();

		// Enable debug mode
		add_filter( 'edd_is_debug_mode', '__return_true' );

		// Set current user to admin
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		remove_filter( 'edd_is_debug_mode', '__return_true' );
		remove_all_filters( 'edd_vat_custom_request_result' );
		remove_all_filters( 'edd_vat_number_check' );
		parent::tearDown();
	}

	/**
	 * Test check_vat returns Result instance.
	 */
	public function test_check_vat_returns_vat_result() {
		$result = VATValidator::check_vat( $this->valid_vat_number, $this->valid_eu_country );

		$this->assertInstanceOf( Result::class, $result );
	}

	/**
	 * Test check_vat with empty VAT number.
	 */
	public function test_check_vat_empty_vat_number() {
		$result = VATValidator::check_vat( '', $this->valid_eu_country );

		$this->assertFalse( $result->is_valid() );
		$this->assertEquals( Result::NO_VAT_NUMBER, $result->error );
	}

	/**
	 * Test check_vat with empty country code.
	 */
	public function test_check_vat_empty_country_code() {
		$result = VATValidator::check_vat( $this->valid_vat_number, '' );

		$this->assertFalse( $result->is_valid() );
		$this->assertEquals( Result::NO_COUNTRY_CODE, $result->error );
	}

	/**
	 * Test check_vat with non-EU country.
	 */
	public function test_check_vat_non_eu_country() {
		$result = VATValidator::check_vat( 'US123456789', $this->invalid_country );

		$this->assertFalse( $result->is_valid() );
		$this->assertEquals( Result::INVALID_COUNTRY_CODE, $result->error );
	}

	public function test_check_vat_country_mismatch() {
		$result = VATValidator::check_vat( 'FR123456789', $this->valid_eu_country );
		$this->assertFalse( $result->is_valid() );
		$this->assertEquals( Result::VAT_NUMBER_INVALID_FOR_COUNTRY, $result->error );
	}

	/**
	 * Test check_vat preserves original VAT number.
	 */
	public function test_check_vat_preserves_original_vat_number() {
		// Use a messy VAT number with spaces
		$messy_vat_number = ' D E 1 2 3 4 5 6 7 8 9 ';
		$result = VATValidator::check_vat( $messy_vat_number, $this->valid_eu_country );

		// Should preserve the original VAT number in the result
		$this->assertEquals( $messy_vat_number, $result->vat_number );
	}



	/**
	 * Test check_vat with Custom service - filter not present.
	 */
	public function test_check_vat_custom_service_no_filter() {
		// Use a standard VAT number format
		$result = VATValidator::check_vat( 'DE12345', $this->valid_eu_country );

		// Should fall through to other services since Custom service requirements not met
		$this->assertFalse( $result->is_valid() );
		// Should have some error set by the fallback services
		$this->assertNotEmpty( $result->error );
	}

	/**
	 * Test check_vat with Custom service - filter present and returns valid.
	 */
	public function test_check_vat_custom_service_filter_valid() {
		// Add custom filter that returns valid result
		add_filter( 'edd_vat_custom_request_result', function( $result, $vat_number, $country_code ) {
			$result->valid = true;
			$result->name = 'Custom Test Company';
			$result->address = 'Custom Test Address';
			$result->consultation_number = '9876543210';
			return $result;
		}, 10, 3 );

		// Use a standard VAT number format
		$result = VATValidator::check_vat( 'DE12345', $this->valid_eu_country );

		$this->assertTrue( $result->is_valid() );
		$this->assertEquals( 'Custom Test Company', $result->name );
		$this->assertEquals( 'Custom Test Address', $result->address );
		$this->assertEquals( '9876543210', $result->consultation_number );
	}

	/**
	 * Test check_vat with Custom service - filter present and returns invalid.
	 */
	public function test_check_vat_custom_service_filter_invalid() {
		// Add custom filter that returns invalid result
		add_filter( 'edd_vat_custom_request_result', function( $result, $vat_number, $country_code ) {
			$result->valid = false;
			$result->error = Result::INVALID_VAT_NUMBER;
			return $result;
		}, 10, 3 );

		// Use a standard VAT number format
		$result = VATValidator::check_vat( 'DE12345', $this->valid_eu_country );

		$this->assertFalse( $result->is_valid() );
		$this->assertEquals( Result::INVALID_VAT_NUMBER, $result->error );
	}

	/**
	 * Test check_vat applies final filter.
	 */
	public function test_check_vat_applies_final_filter() {
		$filter_applied = false;

		// Add filter to verify it gets called
		add_filter( 'edd_vat_number_check', function( $result, $vat_number, $country_code ) use ( &$filter_applied ) {
			$filter_applied = true;
			$this->assertInstanceOf( Result::class, $result );
			$this->assertEquals( 'DE123456789', $vat_number );
			$this->assertEquals( 'DE', $country_code );
			return $result;
		}, 10, 3 );

		VATValidator::check_vat( 'DE123456789', $this->valid_eu_country );

		$this->assertTrue( $filter_applied );
	}

	/**
	 * Test check_vat final filter can modify result.
	 */
	public function test_check_vat_final_filter_modifies_result() {
		// Add filter that modifies the result
		add_filter( 'edd_vat_number_check', function( $result, $vat_number, $country_code ) {
			$result->name = 'Modified by Filter';
			$result->address = 'Modified Address';
			return $result;
		}, 10, 3 );

		$result = VATValidator::check_vat( 'DE123456789', $this->valid_eu_country );

		$this->assertEquals( 'Modified by Filter', $result->name );
		$this->assertEquals( 'Modified Address', $result->address );
	}



	/**
	 * Test check_vat sets catchall error for invalid VAT with no specific error.
	 */
	public function test_check_vat_sets_catchall_error() {
		// Mock a scenario where no service sets an error but VAT is invalid
		// This is harder to test directly, but we can test with a non-matching VAT number

		// Turn off debug mode
		remove_filter( 'edd_is_debug_mode', '__return_true' );

		$result = VATValidator::check_vat( 'INVALID123', $this->valid_eu_country );

		$this->assertFalse( $result->is_valid() );
		// Should have some error set (likely by VIES or other services)
		$this->assertNotEmpty( $result->error );
	}

	/**
	 * Test check_vat with various EU countries.
	 */
	public function test_check_vat_various_eu_countries() {
		$eu_countries = array( 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'SE', 'DK', 'FI' );

		foreach ( $eu_countries as $country ) {
			$result = VATValidator::check_vat( 'INVALID123', $country );

			// Should preserve the country code in result even if validation fails
			$this->assertEquals( $country, $result->country_code );
		}
	}

	/**
	 * Test check_vat preserves original VAT number format.
	 */
	public function test_check_vat_preserves_original_format() {
		$test_cases = array(
			' D E 1 2 3 ',     // spaces
			'D.E.1.2.3',       // dots
			'D-E-1-2-3',       // dashes
			' D.E-1 2 3 ',     // mixed
		);

		foreach ( $test_cases as $input ) {
			$result = VATValidator::check_vat( $input, $this->valid_eu_country );

			// Should preserve the original format in the result
			$this->assertEquals( $input, $result->vat_number, "Failed for input: '$input'" );
		}
	}

	/**
	 * Test check_vat preserves original VAT number and country code in result.
	 */
	public function test_check_vat_preserves_original_data() {
		$original_vat = 'DE123456789';
		$original_country = 'DE';

		$result = VATValidator::check_vat( $original_vat, $original_country );

		$this->assertEquals( $original_vat, $result->vat_number );
		$this->assertEquals( $original_country, $result->country_code );
	}

	/**
	 * Test check_vat with Greek country code (special case).
	 */
	public function test_check_vat_greek_country_code() {
		// Greece uses 'GR' as country code but 'EL' as VAT prefix
		$result = VATValidator::check_vat( 'EL123456789', 'GR' );

		$this->assertEquals( 'GR', $result->country_code );
		$this->assertEquals( 'EL123456789', $result->vat_number );
	}

	/**
	 * Test check_vat debug logging.
	 */
	public function test_check_vat_debug_logging() {
		// This is harder to test directly since edd_debug_log() is a WordPress function
		// But we can at least verify the method doesn't break when logging is called
		$result = VATValidator::check_vat( 'DE123456789', $this->valid_eu_country );

		$this->assertInstanceOf( Result::class, $result );
	}
}
