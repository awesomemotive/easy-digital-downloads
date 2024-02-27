<?php
/**
 * Tests for EDD\Utils\Date
 *
 * @coversDefaultClass EDD\Utils\Date
 *
 * @group edd_dates
 * @group edd_objects
 */
namespace EDD\Tests\Dates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Date;

class Utilities extends EDD_UnitTestCase {

	/**
	 * Date string test fixture.
	 *
	 * @var string
	 */
	protected static $date_string = '03/01/2024 4:08:09';

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_gmt_offset( true );
	}

	public function test_Date_should_extend_DateTime() {
		$this->assertInstanceOf( 'DateTime', $this->get_date_instance() );
	}

	public function test_Date_should_always_convert_date_to_WordPress_time() {
		$date     = $this->get_date_instance();
		$expected = gmdate( 'Y-m-d H:i:s', strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( 'mysql' ) );
	}

	public function test_format_empty_format_should_use_datetime_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'datetime' ), strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( '' ) );
	}

	public function test_format_true_format_should_use_datetime_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'datetime' ), strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( true ) );
	}

	public function test_format_date_should_use_date_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'date' ), strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( 'date' ) );
	}

	public function test_format_time_should_use_time_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'time' ), strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( 'time' ) );
	}

	public function test_format_mysql_should_use_mysql_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'mysql' ), strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( 'mysql' ) );
	}

	public function test_format_object_should_return_Date_object() {
		$date = $this->get_date_instance();

		$this->assertEquals( $date, $date->format( 'object' ) );
	}

	public function test_format_timestamp_should_return_original_timestamp() {
		$date = $this->get_date_instance();

		$this->assertSame( strtotime( self::$date_string ), $date->format( 'timestamp' ) + EDD()->utils->get_gmt_offset() );
	}

	public function test_format_wp_timestamp_should_return_WP_timestamp() {
		$date     = $this->get_date_instance();
		$expected = strtotime( self::$date_string );

		$this->assertSame( $expected, $date->format( 'wp_timestamp' ) );
	}

	public function test_format_generic_date_format_should_format_with_that_scheme() {
		$date     = $this->get_date_instance();
		$expected = gmdate( 'm/d/Y', strtotime( self::$date_string ) );

		$this->assertSame( $expected, $date->format( 'm/d/Y' ) );
	}

	public function test_getWPTimestamp_should_return_timestamp_with_offset_applied() {
		$date     = $this->get_date_instance();
		$expected = strtotime( self::$date_string );

		$this->assertSame( $expected, $date->getWPTimestamp() );
	}

	public function test_date_utility_matches_local_time_new_york() {
		$dates = $this->get_dates( 'America/New_York' );

		$this->assertEquals( $dates['local_time'], $dates['new_utils_date'] );
		$this->assertEquals( $dates['local_time'], $dates['utils_date'] );
	}

	public function test_date_utility_matches_local_time_fiji() {
		$dates = $this->get_dates( 'Pacific/Fiji' );

		$this->assertEquals( $dates['local_time'], $dates['new_utils_date'] );
		$this->assertEquals( $dates['local_time'], $dates['utils_date'] );
	}

	public function test_date_utility_matches_local_time_london() {
		$dates = $this->get_dates( 'Europe/London' );

		$this->assertEquals( $dates['local_time'], $dates['new_utils_date'] );
		$this->assertEquals( $dates['local_time'], $dates['utils_date'] );
	}

	public function test_date_utility_matches_local_time_offset() {
		$dates = $this->get_dates( '', '-7.5' );

		$this->assertEquals( $dates['local_time'], $dates['new_utils_date'] );
		$this->assertEquals( $dates['local_time'], $dates['utils_date'] );
	}

	public function test_date_utility_matches_utc_empty_offset() {
		$dates = $this->get_dates( 'UTC', '' );

		$this->assertEquals( $dates['local_time'], $dates['new_utils_date'] );
		$this->assertEquals( $dates['local_time'], $dates['utils_date'] );
	}

	public function test_date_utility_with_localize_string() {
		$timezone_format = 'Y-m-d H:i:00';
		$new_utils_date  = new \EDD\Utils\Date( 'now' );
		$utils_date      = EDD()->utils->date( 'now', null, 'invalid localize value' );
		$local_time      = date_i18n( $timezone_format );

		$this->assertEquals( $local_time, $new_utils_date->format( $timezone_format ) );
		$this->assertEquals( $local_time, $utils_date->format( $timezone_format ) );
	}

	private function get_dates( $timezone_string = 'America/New_York', $gmt_offset = 0 ) {
		// Update and refresh the timezone and gmt offset.
		update_option( 'gmt_offset', $gmt_offset );
		update_option( 'timezone_string', $timezone_string );

		// Set the timezone format.
		$timezone_format = 'Y-m-d H:i:00';

		// Create the date objects.
		$new_utils_date = new \EDD\Utils\Date( 'now' );
		$utils_date     = EDD()->utils->date( 'now', null, true );

		return array(
			'local_time'      => date_i18n( $timezone_format ),
			'new_utils_date'  => $new_utils_date->format( $timezone_format ),
			'utils_date'      => $utils_date->format( $timezone_format ),
		);
	}

	/**
	 * Helper to retrieve a Date instance.
	 *
	 * @return \EDD\Utils\Date
	 */
	protected function get_date_instance() {
		return new Date( self::$date_string );
	}
}
