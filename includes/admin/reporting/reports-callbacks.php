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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The callback function which fetches the data for the overview_sales_earnings_chart reports endpoint.
 *
 * @since 3.0
 */
function edd_overview_sales_earnings_chart() {
	global $wpdb;

	$dates        = Reports\get_dates_filter( 'objects' );
	$day_by_day   = Reports\get_dates_filter_day_by_day();
	$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

	$sql_clauses = array(
		'select'  => 'date_created AS date',
		'groupby' => 'DATE(date_created)',
		'orderby' => 'DATE(date_created)',
	);

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COUNT(id) AS sales, SUM(total) AS earnings, {$sql_clauses['select']}
 				 FROM {$wpdb->edd_orders} edd_o
 				 WHERE date_created >= %s AND date_created <= %s
				 GROUP BY {$sql_clauses['groupby']}
				 ORDER BY {$sql_clauses['orderby']} ASC",
			$dates['start']->copy()->format( 'mysql' ),
			$dates['end']->copy()->format( 'mysql' )
		)
	);

	$compiled_results = array(
		'sales'    => array(),
		'earnings' => array(),
	);

	foreach ( $results as $result ) {
		$compiled_results['sales'][ $result->date ][1]    = $result->sales;
		$compiled_results['earnings'][ $result->date ][1] = edd_format_amount( $result->earnings );
	}

	print_r( $compiled_results );

	return $compiled_results;
}
