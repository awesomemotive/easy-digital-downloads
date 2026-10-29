<?php
namespace EDD\Tests\Reports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Reports;
use EDD\Reports\Dates\Range;

/**
 * Tests for the report date range options.
 *
 * @group edd_reports
 * @group edd_dates
 *
 * @coversDefaultClass \EDD\Reports\Dates\Range
 */
class Dates_Range extends EDD_UnitTestCase {

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		remove_filter( 'edd_report_date_options', array( $this, 'add_last_7_days' ) );

		parent::tear_down();
	}

	/**
	 * @covers ::options
	 */
	public function test_options_returns_the_twelve_default_ranges() {
		$expected = array(
			'other'        => __( 'Custom', 'easy-digital-downloads' ),
			'today'        => __( 'Today', 'easy-digital-downloads' ),
			'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
			'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
			'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
			'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
			'this_month'   => __( 'Month to Date', 'easy-digital-downloads' ),
			'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
			'this_quarter' => __( 'Quarter to Date', 'easy-digital-downloads' ),
			'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
			'this_year'    => __( 'Year to Date', 'easy-digital-downloads' ),
			'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
		);

		$this->assertSame( $expected, Range::options() );
	}

	/**
	 * @covers ::options
	 */
	public function test_options_does_not_initialize_the_reports_api() {
		$init_count = did_action( 'edd_reports_init' );

		Range::options();

		$this->assertSame( $init_count, did_action( 'edd_reports_init' ) );
	}

	/**
	 * @covers ::options
	 */
	public function test_options_applies_the_filter_on_every_call() {
		Range::options();

		add_filter( 'edd_report_date_options', array( $this, 'add_last_7_days' ) );
		$filtered = Range::options();

		remove_filter( 'edd_report_date_options', array( $this, 'add_last_7_days' ) );
		$unfiltered = Range::options();

		$this->assertArrayHasKey( 'last_7_days', $filtered );
		$this->assertArrayNotHasKey( 'last_7_days', $unfiltered );
	}

	/**
	 * @covers ::options
	 */
	public function test_options_restores_the_twelve_ranges_after_a_filter_removes_other() {
		$expected = Range::options();

		add_filter( 'edd_report_date_options', array( $this, 'remove_other' ) );
		$filtered = Range::options();

		remove_filter( 'edd_report_date_options', array( $this, 'remove_other' ) );
		$restored = Range::options();

		$this->assertArrayNotHasKey( 'other', $filtered );
		$this->assertCount( 12, $restored );
		$this->assertSame( $expected, $restored );
	}

	/**
	 * @covers \EDD\Reports\get_dates_filter_options
	 */
	public function test_procedural_function_delegates_to_range() {
		new Reports\Init();

		add_filter( 'edd_report_date_options', array( $this, 'add_last_7_days' ) );

		$this->assertSame( Range::options(), Reports\get_dates_filter_options() );
		$this->assertArrayHasKey( 'last_7_days', Reports\get_dates_filter_options() );
	}

	/**
	 * Removes the other range through the edd_report_date_options filter.
	 *
	 * @param array $options Date range options.
	 * @return array
	 */
	public function remove_other( $options ) {
		unset( $options['other'] );

		return $options;
	}

	/**
	 * Adds a range through the edd_report_date_options filter.
	 *
	 * @param array $options Date range options.
	 * @return array
	 */
	public function add_last_7_days( $options ) {
		$options['last_7_days'] = 'Last 7 Days';

		return $options;
	}
}
