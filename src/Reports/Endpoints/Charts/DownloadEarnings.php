<?php
/**
 * Download Earnings Chart
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
 * Download Earnings Chart class.
 *
 * Builds chart data for the earnings chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class DownloadEarnings extends Graph {

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'download_earnings_chart';
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
	 * Gets the heading for the chart.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		return __( 'Earnings', 'easy-digital-downloads' ) . Reports\get_download_label( $this->get_download_data() );
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

		$earnings_statuses      = edd_get_gross_order_statuses();
		$earnings_status_string = implode( ', ', array_fill( 0, count( $earnings_statuses ), '%s' ) );
		$order_item_column      = true === $this->exclude_taxes ? '( edd_oi.total - edd_oi.tax )' : 'edd_oi.total';

		$order_item_earnings_query = "SELECT SUM($order_item_column / edd_oi.rate) AS earnings, {$sql_clauses['select']}
			FROM {$wpdb->edd_order_items} edd_oi
			INNER JOIN {$wpdb->edd_orders} edd_o ON edd_oi.order_id = edd_o.id
			WHERE edd_oi.product_id = %d {$price_id} AND edd_oi.date_created >= %s AND edd_oi.date_created <= %s AND edd_o.status IN ({$earnings_status_string})
			GROUP BY {$sql_clauses['groupby']}";

		$order_item_earnings = $wpdb->prepare(
			$order_item_earnings_query, //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$download_data['download_id'],
			$this->dates['start']->copy()->format( 'mysql' ),
			$this->dates['end']->copy()->format( 'mysql' ),
			...$earnings_statuses
		);

		/**
		 * The adjustments query needs a different order status check than the order items. This is due to the fact that
		 * adjustments refunded would end up being double counted, and therefore create an inaccurate revenue report.
		 */
		$adjustments_statuses      = edd_get_net_order_statuses();
		$adjustments_status_string = implode( ', ', array_fill( 0, count( $adjustments_statuses ), '%s' ) );
		$order_adjustment_column   = true === $this->exclude_taxes ? '( edd_oa.total - edd_oa.tax )' : 'edd_oa.total';

		$order_adjustments_query = "SELECT SUM($order_adjustment_column / edd_oa.rate) AS earnings, {$sql_clauses['select']}
			FROM {$wpdb->edd_order_adjustments} edd_oa
			INNER JOIN {$wpdb->edd_order_items} edd_oi ON
				edd_oi.id = edd_oa.object_id
				AND edd_oi.product_id = %d
				{$price_id}
				AND edd_oi.date_created >= %s AND edd_oi.date_created <= %s
			INNER JOIN {$wpdb->edd_orders} edd_o ON edd_oi.order_id = edd_o.id AND edd_o.type = 'sale' AND edd_o.status IN ({$adjustments_status_string})
			WHERE edd_oa.object_type = 'order_item'
			AND edd_oa.type != 'discount'
			GROUP BY {$sql_clauses['groupby']}";

		$order_adjustments = $wpdb->prepare(
			$order_adjustments_query, //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$download_data['download_id'],
			$this->dates['start']->copy()->format( 'mysql' ),
			$this->dates['end']->copy()->format( 'mysql' ),
			...$adjustments_statuses
		);

		$earnings_sql = "SELECT SUM(earnings) as value, {$union_clauses['select']} FROM ({$order_item_earnings} UNION {$order_adjustments})a GROUP BY {$union_clauses['groupby']} ORDER BY {$union_clauses['orderby']}";

		return $wpdb->get_results( $earnings_sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
