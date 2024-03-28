<?php
namespace EDD\Tests\Dates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Date;

/**
 * Tests for date functions in date-functions.php.
 */
class Functions extends EDD_UnitTestCase {

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// All tests will take the -5 (Central Time Zone) into account.
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_gmt_offset( true );
	}

	public function tearDown(): void {
		$_REQUEST['range'] = '';

		parent::tearDown();
	}

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
	public function test_get_timezone_should_return_the_current_timezone_based_on_WP_settings_with_offset() {
		$this->assertSame( 'GMT-05:00', edd_get_timezone_id( true ) );
	}

	/**
	 * @covers ::edd_get_timezone_id()
	 */
	public function test_get_timezone_should_return_the_current_timezone_based_on_WP_settings() {
		update_option( 'timezone_string', 'America/Chicago' );

		$this->assertSame( 'America/Chicago', edd_get_timezone_id() );

		delete_option( 'timezone_string' );
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

		// Since we are using GMT time, the 'end of month' is technically in next month.
		$dates = edd_get_report_dates( 'UTC' );

		/**
		 * We know that these will fail near the end of the month, the above is a deprecated function
		 * and we re-wrote a lot of the date logic with this in mind.
		 */
		$this->markTestIncomplete();

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
	public function test_get_report_dates_correct_this_month_at_the_end_of_the_month_nz() {
		$_REQUEST['range'] = 'this_month';

		$dates = edd_get_report_dates( 'Pacific/Auckland' );

		/**
		 * We know that these will fail near the end of the month, the above is a deprecated function
		 * and we re-wrote a lot of the date logic with this in mind.
		 */
		$this->markTestIncomplete();

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

		/**
		 * We know that these will fail near the end of the month, the above is a deprecated function
		 * and we re-wrote a lot of the date logic with this in mind.
		 */
		$this->markTestIncomplete();

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

		/**
		 * We know that these will fail near the end of the month, the above is a deprecated function
		 * and we re-wrote a lot of the date logic with this in mind.
		 */
		$this->markTestIncomplete();

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

		/**
		 * We know that these will fail near the end of the month, the above is a deprecated function
		 * and we re-wrote a lot of the date logic with this in mind.
		 */
		$this->markTestIncomplete();

		$this->assertEquals( 1, $dates['day'] );
		$this->assertEquals( date( 'n', $current_time ), $dates['m_start'] );
		$this->assertEquals( date( 'Y', $current_time ), $dates['year'] );
		$this->assertEquals( 1, $dates['day_end'] );
		$this->assertEquals( date( 'n', strtotime( '+1 month' ) ), $dates['m_end'] );
		$this->assertEquals( date( 'Y', strtotime( '+1 month' ) ), $dates['year_end'] );
	}

	/**
	 * @covers ::EDD()->utils->date()
	 *
	 */
	public function test_date_invalid_date_returns_date() {
		$date = EDD()->utils->date( '::00', edd_get_timezone_id(), false );

		$this->assertTrue( $date instanceof Date );
	}

	/**
	 * @covers ::EDD()->utils->get_date_string()
	 */
	public function test_get_date_string_valid_returns_valid_string() {
		$actual = EDD()->utils->get_date_string( '2020-01-10', 13, 9 );

		$this->assertSame( '2020-01-10 13:09:00', $actual );
	}

	/**
	 * @covers ::EDD()->utils->get_date_string()
	 */
	public function test_get_date_string_empty_returns_valid_string() {
		$actual   = EDD()->utils->get_date_string();
		$expected = date( 'Y-m-d' ) . ' 00:00:00';

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::EDD()->utils->get_date_string()
	 */
	public function test_get_date_string_invalid_returns_valid_string() {
		$actual   = EDD()->utils->get_date_string( '2020-01-100', 100, 99 );
		$expected = date( 'Y-m-d' ) . ' 23:59:00';

		$this->assertStringContainsString( $expected, $actual );
	}

	/**
	 * @covers ::edd_get_utc_date_string()
	 */
	public function test_get_utc_date_string_from_local() {
		$local_date = '2020-01-10 13:09:00';
		$utc_date   = edd_get_utc_date_string( $local_date );

		$this->assertSame( '2020-01-10 18:09:00', $utc_date );
	}

	/**
	 * @covers ::edd_get_utc_date_string()ß
	 */
	public function test_get_utc_date_string_from_local_with_possitive_offset() {
		update_option( 'gmt_offset', +5 );

		$local_date = '2020-01-10 13:09:00';
		$utc_date   = edd_get_utc_date_string( $local_date, 'Y-m-d H:i:s' );

		$this->assertSame( '2020-01-10 08:09:00', $utc_date );
	}

	/**
	 * @covers ::edd_get_utc_date_string()
	 */
	public function test_get_utc_date_string_from_local_with_zero_offset() {
		update_option( 'gmt_offset', 0 );

		$local_date = '2020-01-10 13:09:00';
		$utc_date   = edd_get_utc_date_string( $local_date, 'Y-m-d H:i:s' );

		$this->assertSame( '2020-01-10 13:09:00', $utc_date );
	}
}
