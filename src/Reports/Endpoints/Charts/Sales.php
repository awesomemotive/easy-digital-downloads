<?php
/**
 * Overview Sales Chart
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
 * Sales Chart class.
 *
 * Builds chart data for the sales chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class Sales extends Graph {

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'sales';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'overview_sales_chart';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Sales', 'easy-digital-downloads' );
	}

	/**
	 * Gets the heading for the chart.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		return parent::get_heading() . ' &mdash; ' . __( 'Net', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		// Sales query - uses net statuses to exclude refunds.
		$sales_statuses = edd_get_net_order_statuses();
		$sales_statuses = apply_filters( 'edd_payment_stats_post_statuses', $sales_statuses );
		$sales_statuses = "'" . implode( "', '", $sales_statuses ) . "'";

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) AS value, {$this->sql_clauses['select']}
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE date_created >= %s AND date_created <= %s
				 AND status IN( {$sales_statuses} )
				 AND type = 'sale'
				 {$this->sql_clauses['where']}
				 GROUP BY {$this->sql_clauses['groupby']}
				 ORDER BY {$this->sql_clauses['orderby']} ASC",
				$this->dates['start']->copy()->format( 'mysql' ),
				$this->dates['end']->copy()->format( 'mysql' )
			)
		);
	}
}
