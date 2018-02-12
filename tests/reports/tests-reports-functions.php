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
	 * Date fixture.
	 *
	 * @var \EDD\Utils\Date
	 */
	protected static $date;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$reports = new \EDD\Reports\Init();

		self::$date = EDD()->utils->date();
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

	/**
	 * @covers ::\EDD\Reports\get_dates_filter()
	 */
	public function test_get_dates_filter_should_return_strings() {
		$expected = array(
			'start' => self::$date->copy()->subDay( 30 )->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfDay()->toDateTimeString(),
		);

		$result = get_dates_filter();

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $result );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\get_dates_filter()
	 */
	public function test_get_dates_filter_values_objects_should_return_objects() {
		$expected = array(
			'start' => self::$date->copy()->subDay( 30 )->startOfDay(),
			'end'   => self::$date->copy()->endOfDay(),
		);

		$result = get_dates_filter( 'objects' );

		$this->assertInstanceOf( '\EDD\Utils\Date', $result['start'] );
		$this->assertInstanceOf( '\EDD\Utils\Date', $result['end'] );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_this_month_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfMonth()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfMonth()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'this_month' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_last_month_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subMonth( 1 )->startOfMonth()->toDateTimeString(),
			'end'   => self::$date->copy()->subMonth( 1 )->endOfMonth()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'last_month' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_today_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfDay()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'today' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_yesterday_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subDay( 1 )->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->subDay( 1 )->endOfDay()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'yesterday' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_this_week_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfWeek()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfWeek()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'this_week' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_last_week_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subWeek( 1 )->startOfWeek()->toDateTimeString(),
			'end'   => self::$date->copy()->subWeek( 1 )->endOfWeek()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'last_week' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_last_30_days_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subDay( 30 )->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfDay()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'last_30_days' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_this_quarter_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfQuarter()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfQuarter()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'this_quarter' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_last_quarter_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subQuarter( 1 )->startOfQuarter()->toDateTimeString(),
			'end'   => self::$date->copy()->subQuarter( 1 )->endOfQuarter()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'last_quarter' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_this_year_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfYear()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfYear()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'this_year' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_last_year_range_should_return_those_dates() {
		$expected = array(
			'start' => self::$date->copy()->subYear( 1 )->startOfYear()->toDateTimeString(),
			'end'   => self::$date->copy()->subYear( 1 )->endOfYear()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'last_year' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_other_range_should_return_dates_for_request_vars() {
		$_REQUEST['filter_from'] = self::$date->copy()->subCentury( 2 )->startOfDay()->toDateTimeString();
		$_REQUEST['filter_to']   = self::$date->copy()->addCentury( 2 )->endOfDay()->toDateTimeString();

		$expected = array(
			'start' => self::$date->copy()->subCentury( 2 )->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->addCentury( 2 )->endOfDay()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'other' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );

		// Clean up.
		unset( $_REQUEST['filter_from'] );
		unset( $_REQUEST['filter_to'] );
	}

	/**
	 * @covers ::\EDD\Reports\parse_dates_for_range()
	 */
	public function test_parse_dates_for_range_with_invalid_range_should_use_request_dates() {
		$expected = array(
			'start' => self::$date->copy()->startOfDay()->toDateTimeString(),
			'end'   => self::$date->copy()->endOfDay()->toDateTimeString(),
		);

		$result = parse_dates_for_range( self::$date, 'fake' );

		// Explicitly strip seconds in case the test is slow.
		$expected = $this->strip_seconds( $expected );
		$result   = $this->strip_seconds( $this->objects_to_date_strings( $result ) );

		$this->assertEqualSetsWithIndex( $expected, $result );
	}

	/**
	 * Strips the seconds from start and end datetime strings to guard against slow tests.
	 *
	 * @param array $dates Start/end dates array.
	 * @return array Start/end dates minus their seconds.
	 */
	protected function strip_seconds( $dates ) {
		$dates['start'] = date( 'Y-m-d H:i', strtotime( $dates['start'] ) );
		$dates['end']   = date( 'Y-m-d H:i', strtotime( $dates['end'] ) );

		return $dates;
	}

	/**
	 * Converts start and end date objects to strings.
	 *
	 * @param array $dates Start/end date objects array.
	 * @return array Start/end date strings array.
	 */
	protected function objects_to_date_strings( $dates ) {
		$dates['start'] = $dates['start']->toDateTimeString();
		$dates['end']   = $dates['end']->toDateTimeString();

		return $dates;
	}
}
