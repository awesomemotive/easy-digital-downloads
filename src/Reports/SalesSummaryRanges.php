<?php
/**
 * Sales summary ranges.
 *
 * @package     EDD\Reports
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Defines the dashboard sales summary ranges and the stats arguments for each.
 *
 * @since 3.7.3
 */
class SalesSummaryRanges {

	/**
	 * Gets the sales summary range keys, in display order.
	 *
	 * @since 3.7.3
	 *
	 * @return array
	 */
	public static function get_ranges(): array {
		return array( 'this_month', 'last_month', 'today', 'total' );
	}

	/**
	 * Gets the EDD\Stats arguments for one sales summary range.
	 *
	 * @since 3.7.3
	 *
	 * @param string $range  The range key, one of get_ranges().
	 * @param string $output The EDD\Stats output format, such as 'raw' or 'formatted'.
	 * @return array
	 */
	public static function get_stats_args( string $range, string $output ): array {
		$args = array(
			'range'        => $range,
			'output'       => $output,
			'revenue_type' => 'net',
		);

		// The all-time totals are calculated by omitting the range entirely.
		if ( 'total' === $range ) {
			unset( $args['range'] );
		}

		return $args;
	}
}
