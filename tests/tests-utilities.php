<?php
namespace EDD;

use EDD\Reports as Reports;

/**
 * Tests for EDD_Utilities.
 *
 * @group edd_utils
 *
 * @coversDefaultClass \EDD\Utilities
 */
class Utilities_Tests extends \EDD_UnitTestCase {

	/**
	 * \EDD\Utilities fixture.
	 *
	 * @var \EDD\Utilities
	 */
	protected static $utils;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_gmt_offset( true );

		self::$utils = new Utilities();
	}

	/**
	 * @dataProvider _test_includes_dp
	 * @covers ::includes()
	 *
	 * @group edd_includes
	 */
	public function test_includes( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_includes().
	 */
	public function _test_includes_dp() {
		$utils_dir = EDD_PLUGIN_DIR . 'includes/utils/';

		return array(

			// Interfaces.
			array( $utils_dir . 'interface-static-registry.php' ),
			array( $utils_dir . 'interface-error-logger.php' ),

			// Exceptions.
			array( $utils_dir . 'class-edd-exception.php' ),
			array( $utils_dir . 'exceptions/class-attribute-not-found.php' ),
			array( $utils_dir . 'exceptions/class-invalid-argument.php' ),
			array( $utils_dir . 'exceptions/class-invalid-parameter.php' ),

			// Date management.
			array( $utils_dir . 'class-date.php' ),

			// Registry.
			array( $utils_dir . 'class-registry.php' ),
		);
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
		new Reports\Init();

		$result = self::$utils->get_registry( 'reports' );

		$this->assertInstanceOf( '\EDD\Reports\Data\Report_Registry', $result );
	}

	/**
	 * @covers ::get_registry()
	 * @group edd_registry
	 */
	public function test_get_registry_with_reports_endpoints_should_retrieve_endpoints_registry_instance() {
		new Reports\Init();

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
	 * @covers ::get_gmt_offset()
	 * @group edd_dates
	 */
	public function test_get_gmt_offset_should_return_gmt_offset() {
		$expected = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;

		$this->assertSame( $expected, self::$utils->get_gmt_offset() );
	}

	/**
	 * @covers ::get_gmt_offset()
	 * @group edd_dates
	 */
	public function test_get_gmt_offset_refresh_true_should_refresh_the_stored_offset() {
		$current_gmt = get_option( 'gmt_offset', 0 );

		update_option( 'gmt_offset', -6 );

		$expected = get_option( 'gmt_offset', -6 ) * HOUR_IN_SECONDS;

		$this->assertSame( $expected, self::$utils->get_gmt_offset( true ) );

		// Clean up.
		update_option( 'gmt_offset', $current_gmt );
	}

	/**
	 * @covers ::get_date_format()
	 * @group edd_dates
	 */
	public function test_get_date_format_should_retrieve_the_WordPress_date_format() {
		$expected = get_option( 'date_format', '' );

		$this->assertSame( $expected, self::$utils->get_date_format() );
	}

	/**
	 * @covers ::get_time_format()
	 * @group edd_dates
	 */
	public function test_get_time_format_should_retrieve_the_WordPress_time_format() {
		$expected = get_option( 'time_format', '' );

		$this->assertSame( $expected, self::$utils->get_time_format() );
	}
}
