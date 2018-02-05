<?php
namespace EDD\Utils;

use EDD\Utils\Date;

/**
 * Tests for EDD\Utils\Date
 *
 * @coversDefaultClass EDD\Utils\Date
 *
 * @group edd_dates
 * @group edd_objects
 */
class Date_Tests extends \EDD_UnitTestCase {

	/**
	 * Date string test fixture.
	 *
	 * @var string
	 */
	protected static $date_string = '01-02-2003 7:08:09';

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		update_option( 'gmt_offset', -5 );

		EDD()->utils->get_wp_offset( true );
	}

	/**
	 * @covers ::__construct()
	 */
	public function test_Date_should_extend_DateTime() {
		$this->assertInstanceOf( 'DateTime', $this->get_date_instance() );
	}

	/**
	 * @covers ::__construct()
	 */
	public function test_Date_should_always_convert_date_to_WordPress_time() {
		$date     = $this->get_date_instance();
		$expected = gmdate( 'Y-m-d H:i:s', strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( 'mysql' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_empty_format_should_use_datetime_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'datetime' ), strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( '' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_true_format_should_use_datetime_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'datetime' ), strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( true ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_date_should_use_date_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'date' ), strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( 'date' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_time_should_use_time_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'time' ), strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( 'time' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_mysql_should_use_mysql_shorthand_format() {
		$date     = $this->get_date_instance();
		$expected = gmdate( edd_get_date_format( 'mysql' ), strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( 'mysql' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_object_should_return_Date_object() {
		$date = $this->get_date_instance();

		$this->assertEquals( $date, $date->format( 'object' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_timestamp_should_return_original_timestamp() {
		$date = $this->get_date_instance();

		$this->assertSame( strtotime( self::$date_string ), $date->format( 'timestamp' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_wp_timestamp_should_return_WP_timestamp() {
		$date     = $this->get_date_instance();
		$expected = strtotime( self::$date_string ) + EDD()->utils->get_wp_offset();

		$this->assertSame( $expected, $date->format( 'wp_timestamp' ) );
	}

	/**
	 * @covers ::format()
	 */
	public function test_format_generic_date_format_should_format_with_that_scheme() {
		$date     = $this->get_date_instance();
		$expected = gmdate( 'm/d/Y', strtotime( self::$date_string ) + EDD()->utils->get_wp_offset() );

		$this->assertSame( $expected, $date->format( 'm/d/Y' ) );
	}

	/**
	 * @covers ::getWPTimestamp()
	 */
	public function test_getWPTimestamp_should_return_timestamp_with_offset_applied() {
		$date     = $this->get_date_instance();
		$expected = strtotime( self::$date_string ) + EDD()->utils->get_wp_offset();

		$this->assertSame( $expected, $date->getWPTimestamp() );
	}

	/**
	 * Helper to retrieve a Date instance.
	 *
	 * @return \EDD\Utils\Date
	 */
	protected function get_date_instance() {
		return new Date( self::$date_string, new \DateTimeZone( edd_get_timezone() ) );
	}

}
