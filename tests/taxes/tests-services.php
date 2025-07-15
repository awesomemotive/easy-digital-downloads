<?php
/**
 * Tests for Tax Services
 *
 * @group edd_taxes
 * @group edd_services
 */
namespace EDD\Tests\Taxes;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Taxes\VAT\Result;
use EDD\Pro\Taxes\Services\Custom;
use EDD\Pro\Taxes\Services\EDD;

class Services extends EDD_UnitTestCase {

	/**
	 * Test fixtures.
	 */
	protected $valid_vat_number = 'GB123456789';
	protected $invalid_vat_number = 'INVALID123';
	protected $test_country_code = 'GB';

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. VAT services tests require EDD Pro.' );
		}

		parent::setUp();

		// Enable debug mode for test service
		add_filter( 'edd_is_debug_mode', '__return_true' );

		// Set current user to admin for test service
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		remove_filter( 'edd_is_debug_mode', '__return_true' );
		parent::tearDown();
	}

	/**
	 * Test Custom service initialization.
	 */
	public function test_custom_service_initialization() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new Custom( $result );

		$this->assertInstanceOf( Custom::class, $service );
	}

	/**
	 * Test Custom service requirements - needs filter.
	 */
	public function test_custom_service_requirements_without_filter() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new Custom( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'requirements_met' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $service ) );
	}

	/**
	 * Test Custom service requirements - with filter.
	 */
	public function test_custom_service_requirements_with_filter() {
		add_filter( 'edd_vat_custom_request_result', '__return_true' );

		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new Custom( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'requirements_met' );
		$method->setAccessible( true );

		$this->assertTrue( $method->invoke( $service ) );

		// Clean up
		remove_filter( 'edd_vat_custom_request_result', '__return_true' );
	}

	/**
	 * Test Custom service endpoint.
	 */
	public function test_custom_service_endpoint() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new Custom( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_endpoint' );
		$method->setAccessible( true );

		$this->assertEquals( '', $method->invoke( $service ) );
	}

	/**
	 * Test Custom service validation applies filter.
	 */
	public function test_custom_service_validation_applies_filter() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new Custom( $result );

		// Mock the filter to return a modified result
		add_filter( 'edd_vat_custom_request_result', function( $result, $vat_number, $country_code ) {
			$result->valid = true;
			$result->name = 'Custom Test Company';
			return $result;
		}, 10, 3 );

		$final_result = $service->get_result();

		$this->assertTrue( $final_result->valid );
		$this->assertEquals( 'Custom Test Company', $final_result->name );

		// Clean up
		remove_all_filters( 'edd_vat_custom_request_result' );
	}

	/**
	 * Test EDD service initialization.
	 */
	public function test_edd_service_initialization() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new EDD( $result );

		$this->assertInstanceOf( EDD::class, $service );
	}

	/**
	 * Test EDD service requirements - without valid license.
	 */
	public function test_edd_service_requirements_without_valid_license() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new EDD( $result );

		// By default, there won't be a valid license in tests
		$this->assertFalse( $service->requirements_met() );
	}

	/**
	 * Test EDD service endpoint.
	 */
	public function test_edd_service_endpoint() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_endpoint' );
		$method->setAccessible( true );

		$this->assertEquals( 'https://services.easydigitaldownloads.com/validate-vat-number', $method->invoke( $service ) );
	}

	/**
	 * Test EDD service get_query_args.
	 */
	public function test_edd_service_get_query_args() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_query_args' );
		$method->setAccessible( true );

		$args = $method->invoke( $service );

		$this->assertArrayHasKey( 'vat_number', $args );
		$this->assertEquals( $this->valid_vat_number, $args['vat_number'] );
	}

	/**
	 * Test EDD service get_query_args with requester VAT number.
	 */
	public function test_edd_service_get_query_args_with_requester_vat() {
		// Set up a UK VAT number
		edd_update_option( 'edd_uk_vat_number', 'GB987654321' );

		$result = new Result( $this->valid_vat_number, 'GB' );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_query_args' );
		$method->setAccessible( true );

		$args = $method->invoke( $service );

		$this->assertArrayHasKey( 'requester_vat_number', $args );
		$this->assertEquals( 'GB987654321', $args['requester_vat_number'] );

		// Clean up
		edd_delete_option( 'edd_uk_vat_number' );
	}

	/**
	 * Test EDD service get_requester_number for GB country.
	 */
	public function test_edd_service_get_requester_number_gb() {
		edd_update_option( 'edd_uk_vat_number', 'GB987654321' );

		$result = new Result( $this->valid_vat_number, 'GB' );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_requester_number' );
		$method->setAccessible( true );

		$requester_number = $method->invoke( $service );
		$this->assertEquals( 'GB987654321', $requester_number );

		// Clean up
		edd_delete_option( 'edd_uk_vat_number' );
	}

	/**
	 * Test EDD service get_requester_number for non-GB country.
	 */
	public function test_edd_service_get_requester_number_non_gb() {
		edd_update_option( 'edd_vat_number', 'DE123456789' );

		$result = new Result( $this->valid_vat_number, 'DE' );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_requester_number' );
		$method->setAccessible( true );

		$requester_number = $method->invoke( $service );
		$this->assertEquals( 'DE123456789', $requester_number );

		// Clean up
		edd_delete_option( 'edd_vat_number' );
	}

	/**
	 * Test EDD service get_requester_number returns empty for invalid UK VAT.
	 */
	public function test_edd_service_get_requester_number_invalid_uk_vat() {
		edd_update_option( 'edd_uk_vat_number', '987654321' ); // Missing GB prefix

		$result = new Result( $this->valid_vat_number, 'GB' );
		$service = new EDD( $result );

		$reflection = new \ReflectionClass( $service );
		$method = $reflection->getMethod( 'get_requester_number' );
		$method->setAccessible( true );

		$requester_number = $method->invoke( $service );
		$this->assertEquals( '', $requester_number );

		// Clean up
		edd_delete_option( 'edd_uk_vat_number' );
	}

	/**
	 * Test all services implement abstract methods.
	 */
	public function test_all_services_implement_abstract_methods() {
		$services = array( Custom::class, EDD::class );

		foreach ( $services as $service_class ) {
			$result = new Result( $this->valid_vat_number, $this->test_country_code );
			$service = new $service_class( $result );

			$reflection = new \ReflectionClass( $service );

			// Check that get_endpoint method exists
			$this->assertTrue( $reflection->hasMethod( 'get_endpoint' ) );

			// Check that requirements_met method exists
			$this->assertTrue( $reflection->hasMethod( 'requirements_met' ) );

			// Check that validate method exists
			$this->assertTrue( $reflection->hasMethod( 'validate' ) );
		}
	}

	/**
	 * Test service get_result method returns Result.
	 */
	public function test_service_get_result_returns_vat_result() {
		$result = new Result( $this->valid_vat_number, $this->test_country_code );
		$service = new EDD( $result );

		$final_result = $service->get_result();

		$this->assertInstanceOf( Result::class, $final_result );
	}

	/**
	 * Test all services inherit VAT number sanitization from base Service class.
	 */
	public function test_all_services_sanitize_vat_numbers() {
		$services      = array( Custom::class, EDD::class );
		$messy_vat      = ' d e 1 2 3 4 5 6 7 8 9 ';
		$expected_clean = 'DE123456789';

		foreach ( $services as $service_class ) {
			$result  = new Result( $this->valid_vat_number, $this->test_country_code );
			$service = new $service_class( $result );

			$sanitized = $service->sanitize_vat_number( $messy_vat );
			$this->assertEquals( $expected_clean, $sanitized, "Service $service_class failed to sanitize VAT number correctly" );
		}
	}
}
