<?php
/**
 * Reports functions.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 * @since       3.0 Full refactor of Reports.
 */

use EDD\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The callback function which fetches the data for the overview_sales_earnings_chart reports endpoint.
 *
 * @since 3.0
 */
function edd_overview_sales_earnings_chart() {
	global $wpdb;

	$dates       = Reports\get_dates_filter( 'objects' );
	$chart_dates = Reports\parse_dates_for_range( null, 'now', false );
	$column      = Reports\get_taxes_excluded_filter() ? '(total - tax)' : 'total';
	$currency    = Reports\get_filter_value( 'currencies' );
	$period      = Reports\get_graph_period();

	if ( empty( $currency ) || 'convert' === $currency ) {
		$column .= ' / rate';
	}

	$sql_clauses = Reports\get_sql_clauses( $period );

	if ( ! empty( $currency ) && array_key_exists( strtoupper( $currency ), edd_get_currencies() ) ) {
		$sql_clauses['where'] = $wpdb->prepare( " AND currency = %s ", strtoupper( $currency ) );
	}

	// Revenue calculations should include gross statuses to negate refunds properly.
	$statuses = edd_get_gross_order_statuses();
	$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
	$statuses = "'" . implode( "', '", $statuses ) . "'";

	$earnings_results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT SUM({$column}) AS earnings, {$sql_clauses['select']}
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE date_created >= %s AND date_created <= %s
				 AND status IN( {$statuses} )
				 AND type IN ( 'sale', 'refund' )
				 {$sql_clauses['where']}
				 GROUP BY {$sql_clauses['groupby']}
				 ORDER BY {$sql_clauses['orderby']} ASC",
			$dates['start']->copy()->format( 'mysql' ),
			$dates['end']->copy()->format( 'mysql' )
		)
	);

	// Sales counts should count by 'net' statuses, which excludes refunds.
	$statuses = edd_get_net_order_statuses();
	$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
	$statuses = "'" . implode( "', '", $statuses ) . "'";

	$sales_results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COUNT(*) AS sales, {$sql_clauses['select']}
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE date_created >= %s AND date_created <= %s
				 AND status IN( {$statuses} )
				 AND type = 'sale'
				 {$sql_clauses['where']}
				 GROUP BY {$sql_clauses['groupby']}
				 ORDER BY {$sql_clauses['orderby']} ASC",
			$dates['start']->copy()->format( 'mysql' ),
			$dates['end']->copy()->format( 'mysql' )
		)
	);

	$sales    = array();
	$earnings = array();

	/**
	 * Initialise all arrays with timestamps and set values to 0.
	 *
	 * We use the Chart based dates for this loop, so the graph shows in the proper date ranges while the actual DB queries are all UTC based.
	 */
	while ( strtotime( $chart_dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $chart_dates['end']->copy()->format( 'mysql' ) ) ) {
		$timestamp     = $chart_dates['start']->copy()->format( 'U' );
		$date_on_chart = $chart_dates['start'];

		$sales[ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );
		$sales[ $timestamp ][1] = 0;

		$earnings[ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );
		$earnings[ $timestamp ][1] = 0.00;

		// Loop through each date there were sales/earnings, which we queried from the database.
		foreach ( $earnings_results as $earnings_result ) {
			$date_of_db_value = EDD()->utils->date( $earnings_result->date );

			// Add any sales/earnings that happened during this hour.
			if ( 'hour' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d H' ) === $date_on_chart->format( 'Y-m-d H' ) ) {
					$earnings[ $timestamp ][1] += $earnings_result->earnings;
				}
				// Add any sales/earnings that happened during this day.
			} elseif ( 'day' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d' ) === $date_on_chart->format( 'Y-m-d' ) ) {
					$earnings[ $timestamp ][1] += $earnings_result->earnings;
				}
				// Add any sales/earnings that happened during this month.
			} else {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m' ) === $date_on_chart->format( 'Y-m' ) ) {
					$earnings[ $timestamp ][1] += $earnings_result->earnings;
				}
			}
		}

		// Loop through each date there were sales/earnings, which we queried from the database.
		foreach ( $sales_results as $sales_result ) {
			$date_of_db_value = EDD()->utils->date( $sales_result->date );

			// Add any sales/earnings that happened during this hour.
			if ( 'hour' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d H' ) === $date_on_chart->format( 'Y-m-d H' ) ) {
					$sales[ $timestamp ][1] += $sales_result->sales;
				}
				// Add any sales/earnings that happened during this day.
			} elseif ( 'day' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d' ) === $date_on_chart->format( 'Y-m-d' ) ) {
					$sales[ $timestamp ][1] += $sales_result->sales;
				}
				// Add any sales/earnings that happened during this month.
			} else {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m' ) === $date_on_chart->format( 'Y-m' ) ) {
					$sales[ $timestamp ][1] += $sales_result->sales;
				}
			}
		}

		// Move the chart along to the next hour/day/month to get ready for the next loop.
		if ( 'hour' === $period ) {
			$chart_dates['start']->addHour( 1 );
		} elseif ( 'day' === $period ) {
			$chart_dates['start']->addDays( 1 );
		} else {
			$chart_dates['start']->addMonth( 1 );
		}
	}

	return array(
		'sales'    => array_values( $sales ),
		'earnings' => array_values( $earnings ),
	);

}

/**
 * The callback function which fetches the data for the edd_overview_refunds_chart reports endpoint.
 *
 * @since 3.0
 */
function edd_overview_refunds_chart() {
	global $wpdb;

	$dates       = Reports\get_dates_filter( 'objects' );
	$chart_dates = Reports\parse_dates_for_range( null, 'now', false );
	$column      = Reports\get_taxes_excluded_filter() ? 'total - tax' : 'total';
	$currency    = Reports\get_filter_value( 'currencies' );
	$period      = Reports\get_graph_period();
	$sql_clauses = Reports\get_sql_clauses( $period );

	if ( empty( $currency ) || 'convert' === $currency ) {
		$column = sprintf( '(%s) / rate', $column );
	} else {
		$sql_clauses['where'] = $wpdb->prepare( " AND currency = %s ", strtoupper( $currency ) );
	}

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COUNT(*) AS number, SUM({$column}) AS amount, {$sql_clauses['select']}
 				 FROM {$wpdb->edd_orders} edd_o
 				 WHERE status IN (%s, %s) AND date_created >= %s AND date_created <= %s AND type = 'refund'
				{$sql_clauses['where']}
				 GROUP BY {$sql_clauses['groupby']}
				 ORDER BY {$sql_clauses['orderby']} ASC",
			esc_sql( 'complete' ),
			esc_sql( 'partially_refunded' ),
			$dates['start']->copy()->format( 'mysql' ),
			$dates['end']->copy()->format( 'mysql' )
		)
	);

	$number = array();
	$amount = array();

	// Initialise all arrays with timestamps and set values to 0.
	while ( strtotime( $chart_dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $chart_dates['end']->copy()->format( 'mysql' ) ) ) {
		$timestamp     = $chart_dates['start']->copy()->format( 'U' );
		$date_on_chart = $chart_dates['start'];

		$number[ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );
		$number[ $timestamp ][1] = 0;

		$amount[ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );
		$amount[ $timestamp ][1] = 0.00;

		// Loop through each date there were refunds, which we queried from the database.
		foreach ( $results as $result ) {
			$date_of_db_value = EDD()->utils->date( $result->date );

			// Add any refunds that happened during this hour.
			if ( 'hour' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d H' ) === $date_on_chart->format( 'Y-m-d H' ) ) {
					$number[ $timestamp ][1] += $result->number;
					$amount[ $timestamp ][1] += abs( $result->amount );
				}
				// Add any refunds that happened during this day.
			} elseif ( 'day' === $period ) {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m-d' ) === $date_on_chart->format( 'Y-m-d' ) ) {
					$number[ $timestamp ][1] += $result->number;
					$amount[ $timestamp ][1] += abs( $result->amount );
				}
				// Add any refunds that happened during this month.
			} else {
				// If the date of this db value matches the date on this line graph/chart, set the y axis value for the chart to the number in the DB result.
				if ( $date_of_db_value->format( 'Y-m' ) === $date_on_chart->format( 'Y-m' ) ) {
					$number[ $timestamp ][1] += $result->number;
					$amount[ $timestamp ][1] += abs( $result->amount );
				}
			}
		}

		// Move the chart along to the next hour/day/month to get ready for the next loop.
		if ( 'hour' === $period ) {
			$chart_dates['start']->addHour( 1 );
		} elseif ( 'day' === $period ) {
			$chart_dates['start']->addDays( 1 );
		} else {
			$chart_dates['start']->addMonth( 1 );
		}
	}

	return array(
		'number' => array_values( $number ),
		'amount' => array_values( $amount ),
	);
}
