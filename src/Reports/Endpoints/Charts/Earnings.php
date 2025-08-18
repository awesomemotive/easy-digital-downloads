<?php
/**
 * Overview Earnings Chart
 *
 * @package     EDD\Reports\Endpoints\Charts
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Charts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Earnings Chart class.
 *
 * Builds chart data for the earnings chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class Earnings extends Graph {

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'overview_earnings_chart';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Earnings', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		// Revenue calculations should include gross statuses to negate refunds properly.
		$statuses = edd_get_gross_order_statuses();
		$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
		$statuses = "'" . implode( "', '", $statuses ) . "'";

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SUM({$this->column}) AS value, {$this->sql_clauses['select']}
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE date_created >= %s AND date_created <= %s
				 AND status IN( {$statuses} )
				 AND type IN ( 'sale', 'refund' )
				 {$this->sql_clauses['where']}
				 GROUP BY {$this->sql_clauses['groupby']}
				 ORDER BY {$this->sql_clauses['orderby']} ASC",
				$this->dates['start']->copy()->format( 'mysql' ),
				$this->dates['end']->copy()->format( 'mysql' )
			)
		);
	}
}
