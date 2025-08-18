<?php
/**
 * Overview Refunds Chart
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
 * Refund Revenue Chart class.
 *
 * Builds chart data for the earnings chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class Refunds extends Graph {

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
		return 'refunds_orders_chart';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Number', 'easy-digital-downloads' );
	}

	/**
	 * Gets the chart heading for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		return __( 'Refunded Orders', 'easy-digital-downloads' ) . ' &mdash; ' . $this->get_chart_label();
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) AS value, {$this->sql_clauses['select']}
 				 FROM {$wpdb->edd_orders} edd_o
 				 WHERE status IN (%s, %s) AND date_created >= %s AND date_created <= %s AND type = 'refund'
				{$this->sql_clauses['where']}
				 GROUP BY {$this->sql_clauses['groupby']}
				 ORDER BY {$this->sql_clauses['orderby']} ASC",
				esc_sql( 'complete' ),
				esc_sql( 'partially_refunded' ),
				$this->dates['start']->copy()->format( 'mysql' ),
				$this->dates['end']->copy()->format( 'mysql' )
			)
		);
	}
}
