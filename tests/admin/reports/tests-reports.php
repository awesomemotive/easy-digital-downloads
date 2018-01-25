<?php
namespace EDD\Admin\Reports;

if ( ! class_exists( '\EDD\Admin\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/class-edd-reports.php' );
}

/**
 * Tests for the Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_objects
 */
class Reports_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Admin\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Admin\Reports();
	}

	/**
	 * @covers ::edd_reports_get_endpoint()
	 * @group edd_errors
	 */
	public function test_reports_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error() {
		$result = \edd_reports_get_endpoint( 'fake', 'tile' );

		$this->assertWPError( $result );
	}

	/**
	 * @covers ::edd_reports_get_endpoint()
	 * @group edd_errors
	 */
	public function test_reports_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error_code_invalid_endpoint() {
		$result = \edd_reports_get_endpoint( 'fake', 'tile' );

		$this->assertSame( 'invalid_endpoint', $result->get_error_code() );
	}

	/**
	 * @covers ::edd_reports_get_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_reports_get_endpoint_with_valid_endpoint_id_valid_type_should_return_an_Endpoint_object() {
		\edd_reports_register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$registry = EDD()->utils->get_registry( 'reports:endpoints' );

		$result = \edd_reports_get_endpoint( 'foo', 'tile' );

		$this->assertInstanceOf( 'EDD\Admin\Reports\Data\Endpoint', $result );
	}

	/**
	 * @covers ::edd_reports_get_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_reports_get_endpoint_with_valid_endpoint_id_invalid_type_should_return_WP_Error_including_invalid_view_error_code() {
		\edd_reports_register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$result = \edd_reports_get_endpoint( 'foo', 'fake' );

		$this->assertSame( 'invalid_view', $result->get_error_code() );
	}

	/**
	 * @covers ::edd_reports_is_view_valid()
	 */
	public function test_reports_is_view_valid_with_valid_view_should_return_true() {
		$this->assertTrue( edd_reports_is_view_valid( 'tile' ) );
	}

	/**
	 * @covers ::edd_reports_is_view_valid()
	 */
	public function test_reports_is_view_valid_with_invalid_view_should_return_false() {
		$this->assertFalse( edd_reports_is_view_valid( 'fake' ) );
	}

	/**
	 * @covers ::edd_reports_get_endpoint_handler()
	 */
	public function test_reports_get_endpoint_handler_with_valid_view_should_return_the_handler() {
		$expected = 'EDD\Admin\Reports\Data\Tile_Endpoint';

		$this->assertSame( $expected, edd_reports_get_endpoint_handler( 'tile' ) );
	}

	/**
	 * @covers ::edd_reports_get_endpoint_handler()
	 */
	public function test_reports_get_endpoint_handler_with_invalid_view_should_return_empty() {
		$this->assertSame( '', edd_reports_get_endpoint_handler( 'fake' ) );
	}

}
