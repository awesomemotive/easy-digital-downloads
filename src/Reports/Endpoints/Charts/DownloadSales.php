<?php
/**
 * Download Sales Chart
 *
 * @package     EDD\Reports\Endpoints\Charts
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Charts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports;

/**
 * Download Sales Chart class.
 *
 * Builds chart data for the earnings chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class DownloadSales extends Graph {

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
		return 'download_sales_chart';
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
		return __( 'Sales', 'easy-digital-downloads' ) . Reports\get_download_label( $this->get_download_data() );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		$download_data = $this->get_download_data();
		$sql_clauses   = Reports\get_sql_clauses( $this->period, 'edd_oi.date_created' );
		$union_clauses = array(
			'select'  => 'date',
			'groupby' => 'date',
			'orderby' => 'date',
		);

		$price_id = isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] )
			? sprintf( 'AND price_id = %d', absint( $download_data['price_id'] ) )
			: '';

		$statuses      = edd_get_net_order_statuses();
		$status_string = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

		$join = $wpdb->prepare(
			"INNER JOIN {$wpdb->edd_orders} edd_o ON (edd_oi.order_id = edd_o.id) AND edd_o.status IN({$status_string}) AND edd_o.type = 'sale' ",
			...$statuses
		);

		$sales_sql = $wpdb->prepare(
			"SELECT COUNT(edd_oi.total) AS value, {$sql_clauses['select']}
			 FROM {$wpdb->edd_order_items} edd_oi
			 {$join}
			 WHERE edd_oi.product_id = %d %1s AND edd_oi.date_created >= %s AND edd_oi.date_created <= %s AND edd_oi.status IN ({$status_string})
			 GROUP BY {$sql_clauses['groupby']}
			 ORDER BY {$sql_clauses['orderby']} ASC",
			$download_data['download_id'],
			$price_id,
			$this->dates['start']->copy()->format( 'mysql' ),
			$this->dates['end']->copy()->format( 'mysql' ),
			...$statuses
		);

		return $wpdb->get_results( $sales_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
