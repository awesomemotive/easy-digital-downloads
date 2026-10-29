<?php
/**
 * Tests for the shared sales summary ranges.
 *
 * @package     EDD\Tests\Reports
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Reports;

use EDD\Reports\SalesSummaryRanges as Ranges;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Sales summary ranges tests.
 *
 * @group edd_reports
 *
 * @coversDefaultClass \EDD\Reports\SalesSummaryRanges
 */
class SalesSummaryRanges extends EDD_UnitTestCase {

	/**
	 * @covers ::get_ranges
	 */
	public function test_get_ranges_returns_the_four_dashboard_ranges_in_order() {
		$this->assertSame(
			array( 'this_month', 'last_month', 'today', 'total' ),
			Ranges::get_ranges()
		);
	}

	/**
	 * @covers ::get_stats_args
	 */
	public function test_total_omits_the_range_and_other_ranges_keep_it() {
		$total_args = Ranges::get_stats_args( 'total', 'raw' );
		$today_args = Ranges::get_stats_args( 'today', 'raw' );

		$this->assertArrayNotHasKey( 'range', $total_args );
		$this->assertSame( 'net', $total_args['revenue_type'] );
		$this->assertSame( 'raw', $total_args['output'] );

		$this->assertSame( 'today', $today_args['range'] );
		$this->assertSame( 'net', $today_args['revenue_type'] );
		$this->assertSame( 'raw', $today_args['output'] );
	}

	public function test_dashboard_widget_data_has_every_range_with_earnings_and_count() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';

		$data = edd_get_dashboard_sales_widget_data();

		$this->assertSame( Ranges::get_ranges(), array_keys( $data ) );
		foreach ( $data as $range_data ) {
			$this->assertArrayHasKey( 'earnings', $range_data );
			$this->assertArrayHasKey( 'count', $range_data );
			$this->assertIsString( $range_data['earnings'] );
		}
	}
}
