<?php
/**
 * Tests for date functions in date-functions.php.
 *
 * @group edd_dates
 * @group edd_functions
 */
class Date_Functions_Tests extends EDD_UnitTestCase {

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// All tests will take the -5 (Central Time Zone) into account.
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_gmt_offset( true );
	}

	public function tearDown() {
		$_REQUEST['range'] = '';

		parent::tearDown();
	}

	//
	// Tests
	//

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_timestamp_and_no_format_should_return_localized_date_in_date_format() {
		$expected = 'January 1, 2003';
		$actual   = edd_date_i18n( '01/02/2003' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_empty_format_should_return_localized_date_in_date_format() {
		$expected = 'January 1, 2003';
		$actual   = edd_date_i18n( '01/02/2003', '' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_invalid_timestamp_and_no_format_should_return_1970() {
		$this->assertSame( 'December 31, 1969', edd_date_i18n( 'foo' ) );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_invalid_timestamp_and_format_should_return_1970_and_respect_format() {
		$this->assertSame( 'December 31, 1969 7:00 pm', edd_date_i18n( 'foo', 'datetime' ) );
	}

	/**
	 * @covers ::edd_get_timezone_id()
	 */
	public function test_get_timezone_should_return_the_current_timezone_based_on_WP_settings() {
		if ( version_compare( phpversion(), '5.5', '<' ) ) {

			// Tests our logic around a shortcoming in PHP 5.3 and 5.4 with DateTimeZone
			$is_dst   = date( 'I' );
			$expected = timezone_name_from_abbr('', get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS, $is_dst );
			$this->assertSame( $expected, edd_get_timezone_id() );

		} else {
			$this->assertSame( 'GMT-5', edd_get_timezone_id() );
		}
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_empty_format_should_default_to_date_format() {
		$this->assertSame( get_option( 'date_format', '' ), edd_get_date_format( '' ) );
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_date_should_return_date_format_value() {
		$this->assertSame( get_option( 'date_format', '' ), edd_get_date_format( 'date' ) );
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_time_should_return_time_format_value() {
		$this->assertSame( get_option( 'time_format', '' ), edd_get_date_format( 'time' ) );
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_datetime_should_return_date_and_time_format_values() {
		$expected = get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );

		$this->assertSame( $expected, edd_get_date_format( 'datetime' ) );
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_mysql_should_return_mysql_format() {
		$this->assertSame( 'Y-m-d H:i:s', edd_get_date_format( 'mysql' ) );
	}

	/**
	 * @covers ::edd_get_date_format()
	 */
	public function test_get_date_format_non_shorthand_format_should_return_that_format() {
		$this->assertSame( 'm/d/Y', edd_get_date_format( 'm/d/Y' ) );
	}

	/**
	 * @covers ::edd_get_report_dates()
	 * @expectEDDeprecated edd_get_report_dates
	 */
	public function test_get_report_dates_correct_this_month_at_the_end_of_the_month_utc() {
		$_REQUEST['range'] = 'this_month';

		// Since we are using GMT time, the 'end of month' is techincally in next month.
		$dates = edd_get_report_dates( 'UTC' );
		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( date( 'n' ), $dates['m_start'] );
		$this->assertEquals( date( 'Y' ), $dates['year'] );
		$this->assertEquals( date( 't' ), $dates['day_end'] );
		$this->assertEquals( date( 'n' ), $dates['m_end'] );
		$this->assertEquals( date( 'Y' ), $dates['year_end'] );
	}

	/**
	 * @covers ::edd_get_report_dates()
	 * @expectEDDeprecated edd_get_report_dates
	 */
	public function test_get_report_dates_correct_this_month_at_the_end_of_the_month_nz() {
		$_REQUEST['range'] = 'this_month';

		$dates = edd_get_report_dates( 'Pacific/Auckland' );

		$auk_date = edd()->utils->date( 'now', 'Pacific/Auckland' );

		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( $auk_date->format( 'n' ), $dates['m_start'] );
		$this->assertEquals( $auk_date->format( 'Y' ), $dates['year'] );
		$this->assertEquals( 1, $dates['day_end'] );

		$expected_end_month = $auk_date->format( 'n' ) + 1;
		$expected_end_year  = $auk_date->format( 'Y' );

		if ( $expected_end_month > 12 ) {
			$roll_over_months = $expected_end_month - 12;
			$expected_end_month = $roll_over_months;
			$expected_end_year++;
		}

		$this->assertEquals( $expected_end_month, $dates['m_end'] );
		$this->assertEquals( $expected_end_year, $dates['year_end'] );
	}

	/**
	 * @covers ::edd_get_report_dates()
	 * @expectEDDeprecated edd_get_report_dates
	 */
	public function test_get_report_dates_correct_this_month_at_the_beginning_of_the_month_utc() {
		$_REQUEST['range'] = 'this_month';

		$dates = edd_get_report_dates( 'UTC' );

		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( date( 'n' ), $dates['m_start'] );
		$this->assertEquals( date( 'Y' ), $dates['year'] );
		$this->assertEquals( 1, $dates['day_end'] );
		$this->assertEquals( date( 'n', strtotime( '+1 month' ) ), $dates['m_end'] );
		$this->assertEquals( date( 'Y', strtotime( '+1 month' ) ), $dates['year_end'] );
	}

	/**
	 * @covers ::edd_get_report_dates()
	 * @expectEDDeprecated edd_get_report_dates
	 */
	public function test_get_report_dates_correct_this_month_at_the_beginning_of_the_month_pdt() {
		$_REQUEST['range'] = 'this_month';

		$dates = edd_get_report_dates( 'America/Los_Angeles' );

		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( date( 'n' ), $dates['m_start'] );
		$this->assertEquals( date( 'Y' ), $dates['year'] );
		$this->assertEquals( 1, $dates['day_end'] );
		$this->assertEquals( date( 'n', strtotime( '+1 month' ) ), $dates['m_end'] );
		$this->assertEquals( date( 'Y', strtotime( '+1 month' ) ), $dates['year_end'] );
	}

	/**
	 * @covers ::edd_get_report_dates()
	 * @expectEDDeprecated edd_get_report_dates
	 */
	public function test_get_report_dates_correct_this_moment_utc() {
		$_REQUEST['range'] = 'this_month';

		$current_time = current_time( 'timestamp' );
		$dates = edd_get_report_dates( 'UTC' );

		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( date( 'n', $current_time ), $dates['m_start'] );
		$this->assertEquals( date( 'Y', $current_time ), $dates['year'] );
		$this->assertEquals( 1, $dates['day_end'] );
		$this->assertEquals( date( 'n', strtotime( '+1 month' ) ), $dates['m_end'] );
		$this->assertEquals( date( 'Y', strtotime( '+1 month' ) ), $dates['year_end'] );
	}
}
