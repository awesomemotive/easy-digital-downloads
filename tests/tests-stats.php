<?php

/**
 * @group edd_stats
 */
class Tests_Stats extends WP_UnitTestCase {

	protected $_post;
	protected $_stats;
	protected $_payment_stats;

	public function setUp() {
		parent::setUp();
		$this->_payment_id = EDD_Helper_Payment::create_simple_payment_with_tax();
		edd_update_payment_status( $this->_payment_id );
	}

	public function tearDown() {
		global $wpdb;

		parent::tearDown();
		EDD_Helper_Payment::delete_payment( $this->_payment_id );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'edd_stats_%'" );
	}

	/*
	 *
	 * EDD_Stats tests
	 *
	 */

	public function test_predefined_date_rages() {

		$stats = new EDD_Stats();
		$out = $stats->get_predefined_dates();

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
			'last_year'    => 'Last Year'
		);

		$this->assertEquals( $expected, $out );

	}

	public function test_setup_dates() {

		$stats = new EDD_Stats();

		// Set start date only
		$stats->setup_dates( 'yesterday' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );
		$this->assertEquals( $stats->end_date - $stats->start_date, DAY_IN_SECONDS - 1 );

		// Set some valid predefined date ranges
		$stats->setup_dates( 'yesterday', 'today' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some valid dates
		$stats->setup_dates( '2012-01-12', '2012-04-15' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some valid date strings
		$stats->setup_dates( 'January 15, 2013', 'February 24, 2013' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );


		// Set some valid timestamps
		$stats->setup_dates( '1379635200', '1379645200' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some invalid dates
		$stats->setup_dates( 'nonvaliddatestring', 'nonvaliddatestring' );
		$this->assertInstanceOf( 'WP_Error', $stats->start_date );
		$this->assertInstanceOf( 'WP_Error', $stats->end_date );

	}


	/*
	 *
	 * EDD_Payment_Stats tests
	 *
	 */

	public function test_get_earnings_by_date() {

		$stats = new EDD_Payment_Stats;
		$earnings = $stats->get_earnings( 0, 'this_month' );
		$this->assertEquals( 131, $earnings );

		$earnings_minus_taxes = $stats->get_earnings( 0, 'this_month', false, false );
		$this->assertEquals( 120, $earnings_minus_taxes );
	}

	public function test_get_sales_by_date() {

		$stats = new EDD_Payment_Stats;
		$sales = $stats->get_sales( 0, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

	public function test_get_earnings_by_date_of_download() {
		$payment = new EDD_Payment( $this->_payment_id );
		$download_id = $payment->downloads[0]['id'];

		$stats = new EDD_Payment_Stats;
		$earnings = $stats->get_earnings( $download_id, 'this_month' );
		$this->assertEquals( 21, $earnings );

		$earnings_minus_taxes = $stats->get_earnings( $download_id, 'this_month', false, false );
		$this->assertEquals( 20, $earnings_minus_taxes );
	}

	public function test_get_sales_by_date_of_download() {
		$payment = new EDD_Payment( $this->_payment_id );
		$download_id = $payment->downloads[0]['id'];

		$stats = new EDD_Payment_Stats;
		$sales = $stats->get_sales( $download_id, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

}
