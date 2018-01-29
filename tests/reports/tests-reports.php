<?php
namespace EDD\Reports;

if ( ! class_exists( '\EDD\Reports' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
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
	 * @var \EDD\Reports
	 * @static
	 */
	protected static $reports;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports();
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

		$this->assertInstanceOf( 'EDD\Reports\Data\Endpoint', $result );
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
	 * @covers ::edd_reports_parse_endpoint_views()
	 */
	public function test_reports_parse_endpoint_views_with_invalid_view_should_leave_it_intact() {
		$expected = array(
			'fake' => array(
				'display_callback' => '__return_false'
			),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_reports_parse_endpoint_views( $expected ) );
	}

	/**
	 * @covers ::edd_reports_parse_endpoint_views()
	 */
	public function test_reports_parse_endpoint_views_with_valid_view_should_inject_defaults() {
		$expected = array(
			'tile' => array(
				'data_callback'    => '__return_zero',
				'display_callback' => 'edd_reports_display_tile',
				'display_args'     => array(
					'type'             => '' ,
					'context'          => 'primary',
					'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
				),
			),
		);

		$views = array(
			'tile' => array(
				'data_callback' => '__return_zero',
			),
		);

		$this->assertEqualSetsWithIndex( $expected, edd_reports_parse_endpoint_views( $views ) );
	}

	/**
	 * @covers ::edd_reports_parse_endpoint_views()
	 */
	public function test_reports_parse_endpoint_views_should_strip_invalid_fields() {
		$views = array(
			'tile' => array(
				'fake_field' => 'foo',
			),
		);

		$result = edd_reports_parse_endpoint_views( $views );

		$this->assertArrayNotHasKey( 'fake_field', $result['tile'] );
	}

	/**
	 * @covers ::edd_reports_parse_endpoint_views()
	 */
	public function test_Reports_parse_endpoint_views_should_inject_default_display_args() {
		$expected = array(
			'type'             => 'number',
			'context'          => 'primary',
			'comparison_label' => __( 'All time', 'easy-digital-downloads' ),
		);

		$views = array(
			'tile' => array(
				'display_args' => array(
					'type' => 'number',
				)
			)
		);

		$result = edd_reports_parse_endpoint_views( $views );

		$this->assertEqualSetsWithIndex( $expected, $result['tile']['display_args'] );
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
		$expected = 'EDD\Reports\Data\Tile_Endpoint';

		$this->assertSame( $expected, edd_reports_get_endpoint_handler( 'tile' ) );
	}

	/**
	 * @covers ::edd_reports_get_endpoint_handler()
	 */
	public function test_reports_get_endpoint_handler_with_invalid_view_should_return_empty() {
		$this->assertSame( '', edd_reports_get_endpoint_handler( 'fake' ) );
	}

}
