<?php
namespace EDD;

/**
 * Tests for EDD_Utilities.
 *
 * @group edd_utils
 */
class Utilities_Tests extends \EDD_UnitTestCase {

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
		self::$utils = new \EDD_Utilities;
	}

	/**
	 * @covers \EDD_Utilities::get_registry()
	 * @group edd_registry
	 * @group edd_errors
	 */
	public function test_get_registry_with_invalid_registry_should_return_a_WP_Error() {
		$result = self::$utils->get_registry( 'fake' );

		$this->assertWPError( $result );
	}

	/**
	 * @covers \EDD_Utilities::get_registry()
	 * @group edd_registry
	 * @group edd_errors
	 */
	public function test_get_registry_with_invalid_registry_should_return_a_WP_Error_including_code_invalid_registry() {
		$result = self::$utils->get_registry( 'fake' );

		$this->assertContains( 'invalid_registry', $result->get_error_codes() );
	}

	/**
	 * @covers \EDD_Utilities::get_registry()
	 * @group edd_registry
	 */
	public function test_get_registry_with_reports_should_retrieve_reports_registry_instance() {
		$result = self::$utils->get_registry( 'reports' );

		$this->assertInstanceOf( '\EDD\Admin\Reports\Reports_Registry', $result );
	}

	/**
	 * @covers \EDD_Utilities::get_registry()
	 * @group edd_registry
	 */
	public function test_get_registry_with_reports_endpoints_should_retrieve_endpoints_registry_instance() {
		$result = self::$utils->get_registry( 'reports:endpoints' );

		$this->assertInstanceOf( '\EDD\Admin\Reports\Data\Endpoint_Registry', $result );
	}

}
