<?php
/**
 * Tests for EDD_Utilities.
 *
 * @group edd_utils
 *
 * @coversDefaultClass \EDD_Utilities
 */
class EDD_Utilities_Tests extends \EDD_UnitTestCase {

	/**
	 * EDD_Utilities fixture.
	 *
	 * @var \EDD_Utilities
	 */
	protected static $utils;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_wp_offset( true );

		self::$utils = new \EDD_Utilities;
	}

	/**
	 * @covers ::get_registry()
	 * @group edd_registry
	 * @group edd_errors
	 */
	public function test_get_registry_with_invalid_registry_should_return_a_WP_Error() {
		$result = self::$utils->get_registry( 'fake' );

		$this->assertWPError( $result );
	}

	/**
	 * @covers ::get_registry()
	 * @group edd_registry
	 * @group edd_errors
	 */
	public function test_get_registry_with_invalid_registry_should_return_a_WP_Error_including_code_invalid_registry() {
		$result = self::$utils->get_registry( 'fake' );

		$this->assertContains( 'invalid_registry', $result->get_error_codes() );
	}

	/**
	 * @covers ::get_registry()
	 * @group edd_registry
	 */
	public function test_get_registry_with_reports_should_retrieve_reports_registry_instance() {
		new \EDD\Reports\Init();

		$result = self::$utils->get_registry( 'reports' );

		$this->assertInstanceOf( '\EDD\Reports\Data\Reports_Registry', $result );
	}

	/**
	 * @covers ::get_registry()
	 * @group edd_registry
	 */
	public function test_get_registry_with_reports_endpoints_should_retrieve_endpoints_registry_instance() {
		new \EDD\Reports\Init();

		$result = self::$utils->get_registry( 'reports:endpoints' );

		$this->assertInstanceOf( '\EDD\Reports\Data\Endpoint_Registry', $result );
	}

	/**
	 * @covers ::date()
	 * @group edd_dates
	 */
	public function test_date_default_date_string_and_timeszone_should_return_a_Carbon_instance() {
		$this->assertInstanceOf( '\Carbon\Carbon', self::$utils->date() );
	}

	/**
	 * @covers ::date()
	 * @group edd_dates
	 */
	public function test_date_with_now_date_string_should_use_now() {
		$format = self::$utils->get_date_format_string( 'date' );

		$this->assertSame( self::$utils->date()->format( $format ), current_time( $format ) );
	}

	/**
	 * @covers ::date()
	 */
	public function test_date_with_date_string_and_default_timezone_should_use_WP_timezone() {
		$date_string = '3/30/2015';
		$format      = self::$utils->get_date_format_string( 'date' );

		$expected = date( $format, strtotime( $date_string ) );
		$result   = self::$utils->date( $date_string );

		$this->assertSame( $expected, $result->format( $format ) );
	}

}
