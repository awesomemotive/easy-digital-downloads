<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_filters
 */
class Tests_Stats extends EDD_UnitTestCase {

	public $_stats;
	public $_payment_stats;

	public function setUp() {
		parent::setUp();
		error_reporting( 0 & ~E_WARNING );

		// Instantiating these kills phpunit

	//	$this->_stats = new EDD_Stats();
	//	$this->_payment_stats = new EDD_Payment_Stats;
	}

	/*
	 *
	 * EDD_Stats tests
	 *
	 */

	public function test_predefined_date_ranges() {
		$this->_stats = new EDD_Stats();
		//$this->_payment_stats = new EDD_Payment_Stats;
	//	$out = $this->_stats->get_predefined_dates();
		$expected = array(
			'today'        => 'Today',
			'yesterday'    => 'Yesterday',
			'this_week'    => 'This Week',
			'last_week'    => 'Last Week',
			'this_month'   => 'This Month',
			'last_month'   => 'Last Month',
			'this_quarter' => 'This Quarter',
			'last_quarter' => 'Last Quater',
			'this_year'    => 'This Year',
			'last_year'    => 'Last Year'
		);

		$this->assertEquals( $expected, $out );

	}
	/*
	public function test_get_earnings_by_date() {

		$stats = new EDD_Payment_Stats;
		$earnings = $stats->get_earnings( 0, 'this_month' );

		$this->assertEquals( 100, $earnings );
	}

	public function test_get_sales_by_date() {

		$stats = new EDD_Payment_Stats;
		$sales = $stats->get_sales( 0, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

	public function test_get_earnings_by_date_of_download() {

		$stats = new EDD_Payment_Stats;
		$earnings = $stats->get_earnings( $this->_post->ID, 'this_month' );

		$this->assertEquals( 100, $earnings );
	}

	public function test_get_sales_by_date_of_download() {

		$stats = new EDD_Payment_Stats;
		$sales = $stats->get_sales( $this->_post->ID, 'this_month' );

		$this->assertEquals( 1, $sales );
	}
*/
}
