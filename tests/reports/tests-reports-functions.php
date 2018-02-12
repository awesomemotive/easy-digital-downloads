<?php
namespace EDD\Reports;

if ( ! class_exists( '\EDD\Reports\Init' ) ) {
	require_once( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' );
}

/**
 * Tests for the Endpoint object.
 *
 * @group edd_reports
 * @group edd_reports_endpoints
 * @group edd_reports_functions
 * @group edd_objects
 */
class Reports_Functions_Tests extends \EDD_UnitTestCase {

	/**
	 * Reports fixture.
	 *
	 * @var \EDD\Reports\Init
	 * @static
	 */
	protected static $reports;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports\Init();
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error() {
		$result = get_endpoint( 'fake', 'tile' );

		$this->assertWPError( $result );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 */
	public function test_get_endpoint_with_invalid_endpoint_id_should_return_WP_Error_code_invalid_endpoint() {
		$result = get_endpoint( 'fake', 'tile' );

		$this->assertSame( 'invalid_endpoint', $result->get_error_code() );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoint_with_valid_endpoint_id_valid_type_should_return_an_Endpoint_object() {
		register_endpoint( 'foo', array(
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

		$result = get_endpoint( 'foo', 'tile' );

		$this->assertInstanceOf( 'EDD\Reports\Data\Endpoint', $result );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint()
	 * @group edd_errors
	 * @throws \EDD_Exception
	 */
	public function test_get_endpoint_with_valid_endpoint_id_invalid_type_should_return_WP_Error_including_invalid_view_error_code() {
		register_endpoint( 'foo', array(
			'label' => 'Foo',
			'views' => array(
				'tile' => array(
					'display_args'     => array( 'some_value' ),
					'display_callback' => '__return_false',
					'data_callback'    => '__return_false',
				),
			),
		) );

		$result = get_endpoint( 'foo', 'fake' );

		$this->assertSame( 'invalid_view', $result->get_error_code() );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_with_invalid_view_should_leave_it_intact() {
		$expected = array(
			'fake' => array(
				'display_callback' => '__return_false'
			),
		);

		$this->assertEqualSetsWithIndex( $expected, parse_endpoint_views( $expected ) );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_with_valid_view_should_inject_defaults() {
		$expected = array(
			'tile' => array(
				'data_callback'    => '__return_zero',
				'display_callback' => __NAMESPACE__ . '\\default_display_tile',
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

		$this->assertEqualSetsWithIndex( $expected, parse_endpoint_views( $views ) );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_should_strip_invalid_fields() {
		$views = array(
			'tile' => array(
				'fake_field' => 'foo',
			),
		);

		$result = parse_endpoint_views( $views );

		$this->assertArrayNotHasKey( 'fake_field', $result['tile'] );
	}

	/**
	 * @covers ::\EDD\Reports\parse_endpoint_views()
	 */
	public function test_parse_endpoint_views_should_inject_default_display_args() {
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

		$result = parse_endpoint_views( $views );

		$this->assertEqualSetsWithIndex( $expected, $result['tile']['display_args'] );
	}

	/**
	 * @covers ::\EDD\Reports\validate_view()
	 */
	public function test_validate_view_with_valid_view_should_return_true() {
		$this->assertTrue( validate_view( 'tile' ) );
	}

	/**
	 * @covers ::\EDD\Reports\validate_view()
	 */
	public function test_validate_view_with_invalid_view_should_return_false() {
		$this->assertFalse( validate_view( 'fake' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_handler()
	 */
	public function test_get_endpoint_handler_with_valid_view_should_return_the_handler() {
		$expected = 'EDD\Reports\Data\Tile_Endpoint';

		$this->assertSame( $expected, get_endpoint_handler( 'tile' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_handler()
	 */
	public function test_get_endpoint_handler_with_invalid_view_should_return_empty() {
		$this->assertSame( '', get_endpoint_handler( 'fake' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_group_callback()
	 */
	public function test_get_endpoint_group_callback_with_tile_view_should_return_that_group_callback() {
		$expected = 'EDD\Reports\default_display_tiles_group';

		$this->assertSame( $expected, get_endpoint_group_callback( 'tile' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_group_callback()
	 */
	public function test_get_endpoint_group_callback_with_table_view_should_return_that_group_callback() {
		$expected = 'EDD\Reports\default_display_tables_group';

		$this->assertSame( $expected, get_endpoint_group_callback( 'table' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_group_callback()
	 */
	public function test_get_endpoint_group_callback_with_invalid_view_should_return_an_empty_string() {
		$this->assertSame( '', get_endpoint_group_callback( 'fake' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_endpoint_filters()
	 */
	public function test_get_endpoint_filters_should_return_records_for_all_official_filters() {
		$expected = array( 'dates', 'products', 'taxes' );

		$this->assertEqualSets( $expected, array_keys( get_endpoint_filters() ) );
	}

	/**
	 * @covers ::\EDD\Reports\validate_filter()
	 */
	public function test_validate_filter_with_valid_filter_should_return_true() {
		$this->assertTrue( validate_filter( 'dates' ) );
	}

	/**
	 * @covers ::\EDD\Reports\validate_filter()
	 */
	public function test_validate_filter_with_invalid_filter_should_return_false() {
		$this->assertFalse( validate_filter( 'fake' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_filter_value()
	 */
	public function test_get_filter_value_with_invalid_filter_should_return_an_empty_string() {
		$this->assertSame( '', get_filter_value( 'fake', 'some_report_id' ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_filter_value()
	 */
	public function test_get_filter_value_with_a_valid_filter_should_retrieve_that_filters_value() {
		$report_id = rand_str( 10 );

		$expected = array(
			'from' => date( 'Y-m-d H:i:s' ),
			'to'   => date( 'Y-m-d H:i:s' ),
		);

		EDD()->session->set( "{$report_id}:dates", $expected );

		$this->assertEqualSetsWithIndex( $expected, get_filter_value( 'dates', $report_id ) );
	}

	/**
	 * @covers ::\EDD\Reports\get_dates_filter_options()
	 */
	public function test_get_dates_filter_options_should_match_defaults() {
		$expected = array(
			'today'        => __( 'Today', 'easy-digital-downloads' ),
			'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
			'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
			'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
			'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
			'this_month'   => __( 'This Month', 'easy-digital-downloads' ),
			'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
			'this_quarter' => __( 'This Quarter', 'easy-digital-downloads' ),
			'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
			'this_year'    => __( 'This Year', 'easy-digital-downloads' ),
			'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
			'other'        => __( 'Custom', 'easy-digital-downloads' )
		);
		
		$this->assertEqualSetsWithIndex( $expected, get_dates_filter_options() );
	}

}
