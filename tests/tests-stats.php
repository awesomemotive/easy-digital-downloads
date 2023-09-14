<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * EDD_Payment_Stats Tests.
 *
 * @group edd_stats
 * @coversDefaultClass EDD_Payment_Stats
 */
class Tests_Stats extends EDD_UnitTestCase {

	/**
	 * EDD_Payment_Stats fixture.
	 *
	 * @var \EDD_Payment_Stats
	 */
	protected static $stats;

	/**
	 * Order fixture.
	 *
	 * @var EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * Second Order fixture.
	 *
	 * @var EDD\Orders\Order
	 */
	protected static $order2;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$stats  = new \EDD_Payment_Stats();
		self::$order  = parent::edd()->order->create_and_get();
		self::$order2 = parent::edd()->order->create_and_get();
	}

	public function test_predefined_date_rages() {
		$expected = array(
			'today'        => 'Today',
			'yesterday'    => 'Yesterday',
			'this_week'    => 'This Week',
			'last_week'    => 'Last Week',
			'this_month'   => 'This Month',
			'last_month'   => 'Last Month',
			'this_quarter' => 'This Quarter',
			'last_quarter' => 'Last Quarter',
			'this_year'    => 'This Year',
			'last_year'    => 'Last Year',
		);

		$this->assertEqualSetsWithIndex( $expected, self::$stats->get_predefined_dates() );
	}

	public function test_setting_up_date_yesterday_start_should_return_true() {
		self::$stats->setup_dates( 'yesterday' );

		$this->assertIsNumeric( self::$stats->start_date );
		$this->assertIsNumeric( self::$stats->end_date );
		$this->assertGreaterThan( self::$stats->start_date, self::$stats->end_date );
	}

	public function test_setting_up_date_yesterday_start_today_end_should_return_true() {
		self::$stats->setup_dates( 'yesterday', 'today' );

		$this->assertIsNumeric( self::$stats->start_date );
		$this->assertIsNumeric( self::$stats->end_date );
		$this->assertGreaterThan( self::$stats->start_date, self::$stats->end_date );
	}

	public function test_setting_up_dates_with_date_time_strings_should_return_true() {
		self::$stats->setup_dates( 'January 15, 2013', 'February 24, 2013' );

		$this->assertIsNumeric( self::$stats->start_date );
		$this->assertIsNumeric( self::$stats->end_date );
		$this->assertGreaterThan( self::$stats->start_date, self::$stats->end_date );
	}

	public function test_setting_up_dates_With_timestamps_should_return_true() {
		self::$stats->setup_dates( '1379635200', '1379645200' );

		$this->assertIsNumeric( self::$stats->start_date );
		$this->assertIsNumeric( self::$stats->end_date );
		$this->assertGreaterThan( self::$stats->start_date, self::$stats->end_date );
	}

	public function test_setting_up_invalid_dates_should_return_WP_Error() {
		self::$stats->setup_dates( 'nonvaliddatestring', 'nonvaliddatestring' );

		$this->assertInstanceOf( 'WP_Error', self::$stats->start_date );
		$this->assertInstanceOf( 'WP_Error', self::$stats->end_date );
	}

	public function test_get_earnings_for_this_month_should_be_240() {
		$earnings = self::$stats->get_earnings( 0, 'this_month' );

		$this->assertSame( 240.0, $earnings );
	}

	public function test_get_earnings_for_this_month_excluding_taxes_should_be_190() {
		$earnings = self::$stats->get_earnings( 0, 'this_month', false, false );

		$this->assertSame( 190.0, $earnings );
	}

	public function test_get_earnings_for_this_month_for_download_should_return_240() {
		$earnings = self::$stats->get_earnings( 1, 'this_month' );
		$this->assertEquals( 240.0, $earnings );
	}

	public function test_get_earnings_for_this_month_for_download_excluding_taxes_should_return_190() {
		$earnings = self::$stats->get_earnings( 1, 'this_month', false, false );
		$this->assertEquals( 190.0, $earnings );
	}

	public function test_get_sales_for_this_month_should_be_2() {
		$sales = self::$stats->get_sales( 0, 'this_month' );

		$this->assertSame( 2, $sales );
	}

	public function test_get_sales_for_this_month_for_download_should_return_2() {
		$earnings = self::$stats->get_sales( 1, 'this_month' );
		$this->assertEquals( 2, $earnings );
	}

	public function test_get_sales_by_range_for_today() {
		$sales = self::$stats->get_sales_by_range( 'today' );

		$this->assertNotEmpty( $sales );
		$this->assertEquals( 2, $sales[0]['count'] );
		$this->assertEquals( date( 'Y' ), $sales[0]['y'] );
	}
}
