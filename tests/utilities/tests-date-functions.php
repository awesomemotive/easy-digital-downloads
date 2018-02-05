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
		update_option( 'gmt_offset', -5 );

		EDD()->utils->refresh_wp_offset();
	}

	//
	// Tests
	//

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_timestamp_and_no_format_should_return_localized_date_in_date_format() {
		$expected = gmdate( get_option( 'date_format', '' ), strtotime( '01/02/2003' ) );
		$actual   = edd_date_i18n( strtotime( '01/02/2003' ) );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_empty_format_should_return_localized_date_in_date_format() {
		$expected = gmdate( get_option( 'date_format', '' ), strtotime( '01/02/2003' ) );
		$actual   = edd_date_i18n( strtotime( '01/02/2003' ), '' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_with_invalid_timestamp_and_no_format_should_return_1970() {
		$this->assertSame( 'January 1, 1970', edd_date_i18n( 'foo' ) );
	}

	/**
	 * @covers ::edd_date_i18n()
	 */
	public function test_date_i18n_invalid_timestamp_and_format_should_return_1970_and_respect_format() {
		$this->assertSame( 'January 1, 1970 12:00 am', edd_date_i18n( 'foo', 'datetime' ) );
	}

	/**
	 * @covers ::edd_get_timezone()
	 */
	public function test_get_timezone_should_return_the_current_timezone_based_on_WP_settings() {
		$this->assertSame( 'America/New_York', edd_get_timezone() );
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

}
