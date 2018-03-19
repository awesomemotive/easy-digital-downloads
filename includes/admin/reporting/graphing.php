<?php
/**
 * Graphing Functions
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Show report graphs
 *
 * @since 1.3
 * @return void
*/
function edd_reports_graph() {
	// Retrieve the queried dates
	$dates = edd_get_report_dates();

	// Determine graph options
	switch ( $dates['range'] ) {
		case 'today' :
		case 'yesterday' :
			$day_by_day = true;
			break;
		case 'last_year' :
		case 'this_year' :
			$day_by_day = false;
			break;
		case 'last_quarter' :
		case 'this_quarter' :
			$day_by_day = true;
			break;
		case 'other' :
			if ( $dates['m_start'] == 12 && $dates['m_end'] == 1 ) {
				$day_by_day = true;
			} elseif ( $dates['m_end'] - $dates['m_start'] >= 3 || ( $dates['year_end'] > $dates['year'] && ( $dates['m_start'] - $dates['m_end'] ) != 10 ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	}

	$earnings_totals = 0.00; // Total earnings for time period shown
	$sales_totals    = 0;    // Total sales for time period shown

	$include_taxes = empty( $_GET['exclude_taxes'] ) ? true : false;

	if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
		// Hour by hour
		$hour  = 0;
		$month = $dates['m_start'];

		$i = 0;
		$j = 0;

		$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'];
		$end = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'];

		$sales = EDD()->payment_stats->get_sales_by_range( $dates['range'], true, $start, $end );
		$earnings = EDD()->payment_stats->get_earnings_by_range( $dates['range'], true, $start, $end, $include_taxes );

		while ( $hour <= 23 ) {
			$date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;

			if ( isset( $earnings[ $i ] ) && $earnings[ $i ]['h'] == $hour ) {
				$earnings_data[] = array( $date, $earnings[ $i ]['total'] );
				$earnings_totals += $earnings[ $i ]['total'];
				$i++;
			} else {
				$earnings_data[] = array( $date, 0 );
			}

			if ( isset( $sales[ $j ] ) && $sales[ $j ]['h'] == $hour ) {
				$sales_data[] = array( $date, $sales[ $j ]['count'] );
				$sales_totals += $sales[ $j ]['count'];
				$j++;
			} else {
				$sales_data[] = array( $date, 0 );
			}

			$hour++;
		}
	} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {
		$report_dates = array();
		$i = 0;
		while ( $i <= 6 ) {
			if ( ( $dates['day'] + $i ) <= $dates['day_end'] ) {
				$report_dates[ $i ] = array(
					'day'   => (string) $dates['day'] + $i,
					'month' => $dates['m_start'],
					'year'  => $dates['year'],
				);
			} else {
				$report_dates[ $i ] = array(
					'day'   => (string) $i,
					'month' => $dates['m_end'],
					'year'  => $dates['year_end'],
				);
			}

			$i++;
		}

		$start_date = $report_dates[0];
		$end_date = end( $report_dates );

		$sales = EDD()->payment_stats->get_sales_by_range( $dates['range'], true, $start_date['year'] . '-' . $start_date['month'] . '-' . $start_date['day'], $end_date['year'] . '-' . $end_date['month'] . '-' . $end_date['day'] );
		$earnings = EDD()->payment_stats->get_earnings_by_range( $dates['range'], true, $start_date['year'] . '-' . $start_date['month'] . '-' . $start_date['day'], $end_date['year'] . '-' . $end_date['month'] . '-' . $end_date['day'], $include_taxes );

		$i = 0;
		$j = 0;
		foreach ( $report_dates as $report_date ) {
			$date = mktime( 0, 0, 0,  $report_date['month'], $report_date['day'], $report_date['year']  ) * 1000;

			if ( array_key_exists( $i, $sales ) && $report_date['day'] == $sales[ $i ]['d'] && $report_date['month'] == $sales[ $i ]['m'] && $report_date['year'] == $sales[ $i ]['y'] ) {
				$sales_data[] = array( $date, $sales[ $i ]['count'] );
				$sales_totals += $sales[ $i ]['count'];
				$i++;
			} else {
				$sales_data[] = array( $date, 0 );
			}

			if ( array_key_exists( $j, $earnings ) && $report_date['day'] == $earnings[ $j ]['d'] && $report_date['month'] == $earnings[ $j ]['m'] && $report_date['year'] == $earnings[ $j ]['y'] ) {
				$earnings_data[] = array( $date, $earnings[ $j ]['total'] );
				$earnings_totals += $earnings[ $j ]['total'];
				$j++;
			} else {
				$earnings_data[] = array( $date, 0 );
			}
		}

	} else {
		if ( cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) < $dates['day'] ) {
			$next_day = mktime( 0, 0, 0, $dates['m_start'] + 1, 1, $dates['year'] );
			$day = date( 'd', $next_day );
			$month = date( 'm', $next_day );
			$year = date( 'Y', $next_day );
			$date_start = $year . '-' . $month . '-' . $day;
		} else {
			$date_start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'];
		}

		if ( cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] ) < $dates['day_end'] ) {
			$date_end = $dates['year_end'] . '-' . $dates['m_end'] . '-' . cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
		} else {
			$date_end = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'];
		}

		$sales = EDD()->payment_stats->get_sales_by_range( $dates['range'], $day_by_day, $date_start, $date_end );
		$earnings = EDD()->payment_stats->get_earnings_by_range( $dates['range'], $day_by_day, $date_start, $date_end, $include_taxes );

		$y = $dates['year'];
		$temp_data = array(
			'sales'    => array(),
			'earnings' => array(),
		);

		foreach ( $sales as $sale ) {
			if ( $day_by_day ) {
				$temp_data['sales'][ $sale['y'] ][ $sale['m'] ][ $sale['d'] ] = $sale['count'];
			} else {
				$temp_data['sales'][ $sale['y'] ][ $sale['m'] ] = $sale['count'];
			}
			$sales_totals += $sale['count'];
		}

		foreach ( $earnings as $earning ) {
			if ( $day_by_day ) {
				$temp_data['earnings'][ $earning['y'] ][ $earning['m'] ][ $earning['d'] ] = $earning['total'];
			} else {
				$temp_data['earnings'][ $earning['y'] ][ $earning['m'] ] = $earning['total'];
			}
			$earnings_totals += $earning['total'];
		}

		while ( $day_by_day && ( strtotime( $date_start ) <= strtotime( $date_end ) ) ) {
			$d = date( 'd', strtotime( $date_start ) );
			$m = date( 'm', strtotime( $date_start ) );
			$y = date( 'Y', strtotime( $date_start ) );

			if ( ! isset( $temp_data['sales'][ $y ][ $m ][ $d ] ) ) {
				$temp_data['sales'][ $y ][ $m ][ $d ] = 0;
			}

			if ( ! isset( $temp_data['earnings'][ $y ][ $m ][ $d ] ) ) {
				$temp_data['earnings'][ $y ][ $m ][ $d ] = 0;
			}

			$date_start = date( 'Y-m-d', strtotime( '+1 day', strtotime( $date_start ) ) );
		}

		while ( ! $day_by_day && ( strtotime( $date_start ) <= strtotime( $date_end ) ) ) {
			$m = date( 'm', strtotime( $date_start ) );
			$y = date( 'Y', strtotime( $date_start ) );

			if ( ! isset( $temp_data['sales'][ $y ][ $m ] ) ) {
				$temp_data['sales'][ $y ][ $m ] = 0;
			}

			if ( ! isset( $temp_data['earnings'][ $y ][ $m ] ) ) {
				$temp_data['earnings'][ $y ][ $m ] = 0;
			}

			$date_start = date( 'Y-m', strtotime( '+1 month', strtotime( $date_start ) ) );
		}

		$sales_data    = array();
		$earnings_data = array();

		// When using 3 months or smaller as the custom range, show each day individually on the graph
		if ( $day_by_day ) {
			foreach ( $temp_data['sales'] as $year => $months ) {
				foreach ( $months as $month => $days ) {
					foreach ( $days as $day => $count ) {
						$date         = mktime( 0, 0, 0, $month, $day, $year ) * 1000;
						$sales_data[] = array( $date, $count );
					}
				}
			}

			foreach ( $temp_data['earnings'] as $year => $months ) {
				foreach ( $months as $month => $days ) {
					foreach ( $days as $day => $total ) {
						$date            = mktime( 0, 0, 0, $month, $day, $year ) * 1000;
						$earnings_data[] = array( $date, $total );
					}
				}
			}

			// Sort dates in ascending order
			foreach ( $sales_data as $key => $value ) {
				$timestamps[ $key ] = $value[0];
			}
			if ( ! empty( $timestamps ) ) {
				array_multisort( $timestamps, SORT_ASC, $sales_data );
			}

			foreach ( $earnings_data as $key => $value ) {
				$earnings_timestamps[ $key ] = $value[0];
			}
			if ( ! empty( $earnings_timestamps ) ) {
				array_multisort( $earnings_timestamps, SORT_ASC, $earnings_data );
			}

		// When showing more than 3 months of results, group them by month, by the first (except for the last month, group on the last day of the month selected)
		} else {

			foreach ( $temp_data['sales'] as $year => $months ) {
				$month_keys = array_keys( $months );
				$last_month = end( $month_keys );

				if ( $day_by_day ) {
					foreach ( $months as $month => $days ) {
						$day_keys = array_keys( $days );
						$last_day = end( $day_keys );

						$month_keys = array_keys( $months );

						$consolidated_date = $month === end( $month_keys ) ? cal_days_in_month( CAL_GREGORIAN, $month, $year ) : 1;

						$sales        = array_sum( $days );
						$date         = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
						$sales_data[] = array( $date, $sales );
					}
				} else {
					foreach ( $months as $month => $count ) {
						$month_keys = array_keys( $months );
						$consolidated_date = $month === end( $month_keys ) ? cal_days_in_month( CAL_GREGORIAN, $month, $year ) : 1;

						$date = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
						$sales_data[] = array( $date, $count );
					}
				}
			}

			// Sort dates in ascending order
			foreach ( $sales_data as $key => $value ) {
				$timestamps[ $key ] = $value[0];
			}
			if ( ! empty( $timestamps ) ) {
				array_multisort( $timestamps, SORT_ASC, $sales_data );
			}

			foreach ( $temp_data['earnings'] as $year => $months ) {
				$month_keys = array_keys( $months );
				$last_month = end( $month_keys );

				if ( $day_by_day ) {
					foreach ( $months as $month => $days ) {
						$day_keys = array_keys( $days );
						$last_day = end( $day_keys );

						$month_keys = array_keys( $months );

						$consolidated_date = $month === end( $month_keys ) ? cal_days_in_month( CAL_GREGORIAN, $month, $year ) : 1;

						$earnings        = array_sum( $days );
						$date            = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
						$earnings_data[] = array( $date, $earnings );
					}
				} else {
					foreach ( $months as $month => $count ) {
						$month_keys = array_keys( $months );
						$consolidated_date = $month === end( $month_keys ) ? cal_days_in_month( CAL_GREGORIAN, $month, $year ) : 1;

						$date = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
						$earnings_data[] = array( $date, $count );
					}
				}
			}

			// Sort dates in ascending order
			foreach ( $earnings_data as $key => $value ) {
				$earnings_timestamps[ $key ] = $value[0];
			}
			if ( ! empty( $earnings_timestamps ) ) {
				array_multisort( $earnings_timestamps, SORT_ASC, $earnings_data );
			}
		}
	}

	$data = array(
		__( 'Earnings', 'easy-digital-downloads' ) => $earnings_data,
		__( 'Sales', 'easy-digital-downloads' )    => $sales_data
	);

	// start our own output buffer
	ob_start();
	do_action( 'edd_reports_graph_before' ); ?>
	<div id="edd-dashboard-widgets-wrap">
		<div class="metabox-holder" style="padding-top: 0;">
			<div class="postbox">
				<h3><span><?php _e('Earnings Over Time','easy-digital-downloads' ); ?></span></h3>

				<div class="inside">
					<?php
					edd_reports_graph_controls();
					$graph = new EDD_Graph( $data );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$graph->display();

					if( ! empty( $dates['range'] ) && 'this_month' == $dates['range'] ) {
						$estimated = edd_estimated_monthly_stats( $include_taxes );
					}
					?>

					<p class="edd_graph_totals">
						<strong>
							<?php
								_e( 'Total earnings for period shown: ', 'easy-digital-downloads' );
								echo edd_currency_filter( edd_format_amount( $earnings_totals ) );
							?>
						</strong>
						<?php if ( ! $include_taxes ) : ?>
							<sup>&dagger;</sup>
						<?php endif; ?>
					</p>
					<p class="edd_graph_totals"><strong><?php _e( 'Total sales for period shown: ', 'easy-digital-downloads' ); echo edd_format_amount( $sales_totals, false ); ?></strong></p>

					<?php if( ! empty( $dates['range'] ) && 'this_month' == $dates['range'] ) : ?>
						<p class="edd_graph_totals">
							<strong>
								<?php
									_e( 'Estimated monthly earnings: ', 'easy-digital-downloads' );
									echo edd_currency_filter( edd_format_amount( $estimated['earnings'] ) );
								?>
							</strong>
							<?php if ( ! $include_taxes ) : ?>
								<sup>&dagger;</sup>
							<?php endif; ?>
						</p>
						<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly sales: ', 'easy-digital-downloads' ); echo edd_format_amount( $estimated['sales'], false ); ?></strong></p>
					<?php endif; ?>

					<?php do_action( 'edd_reports_graph_additional_stats' ); ?>

					<p class="edd_graph_notes">
						<?php if ( false === $include_taxes ) : ?>
							<em><sup>&dagger;</sup> <?php _e( 'Excludes sales tax.', 'easy-digital-downloads' ); ?></em>
						<?php endif; ?>
					</p>

				</div>
			</div>
		</div>
	</div>
	<?php do_action( 'edd_reports_graph_after' );

	// get output buffer contents and end our own buffer
	$output = ob_get_contents();
	ob_end_clean();

	echo $output;
}

/**
 * Show report graphs of a specific product
 *
 * @since 1.9
 * @return void
*/
function edd_reports_graph_of_download( $download_id = 0 ) {
	// Retrieve the queried dates
	$dates = edd_get_report_dates();

	// Determine graph options
	switch ( $dates['range'] ) {
		case 'today' :
		case 'yesterday' :
			$day_by_day = true;
			break;
		case 'last_year' :
		case 'this_year' :
			$day_by_day = false;
			break;
		case 'last_quarter' :
		case 'this_quarter' :
			$day_by_day = true;
			break;
		case 'other' :
			if ( $dates['m_start'] == 12 && $dates['m_end'] == 1 ) {
				$day_by_day = true;
			} elseif ( $dates['m_end'] - $dates['m_start'] >= 3 || ( $dates['year_end'] > $dates['year'] && ( $dates['m_start'] - $dates['m_end'] ) != 10 ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	}

	$earnings_totals = (float) 0.00; // Total earnings for time period shown
	$sales_totals    = 0;            // Total sales for time period shown

	$include_taxes = empty( $_GET['exclude_taxes'] ) ? true : false;
	$earnings_data = array();
	$sales_data    = array();

	if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
		// Hour by hour
		$month  = $dates['m_start'];
		$hour   = 0;
		$minute = 0;
		$second = 0;
		while ( $hour <= 23 ) :
			if ( $hour == 23 ) {
				$minute = $second = 59;
			}

			$date = mktime( $hour, $minute, $second, $month, $dates['day'], $dates['year'] );
			$date_end = mktime( $hour + 1, $minute, $second, $month, $dates['day'], $dates['year'] );

			$sales = EDD()->payment_stats->get_sales( $download_id, $date, $date_end );
			$sales_totals += $sales;

			$earnings = EDD()->payment_stats->get_earnings( $download_id, $date, $date_end, $include_taxes );
			$earnings_totals += $earnings;

			$sales_data[] = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );

			$hour++;
		endwhile;
	} elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {
		$num_of_days = cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] );

		$report_dates = array();
		$i = 0;
		while ( $i <= 6 ) {
			if ( ( $dates['day'] + $i ) <= $num_of_days ) {
				$report_dates[ $i ] = array(
					'day'   => (string) $dates['day'] + $i,
					'month' => $dates['m_start'],
					'year'  => $dates['year'],
				);
			} else {
				$report_dates[ $i ] = array(
					'day'   => (string) $i,
					'month' => $dates['m_end'],
					'year'  => $dates['year_end'],
				);
			}

			$i++;
		}

		foreach ( $report_dates as $report_date ) {
			$date  = mktime( 0, 0, 0, $report_date['month'], $report_date['day'], $report_date['year'] );
			$date_end = mktime( 23, 59, 59, $report_date['month'], $report_date['day'], $report_date['year'] );
			$sales = EDD()->payment_stats->get_sales( $download_id, $date, $date_end );
			$sales_totals += $sales;

			$earnings = EDD()->payment_stats->get_earnings( $download_id, $date, $date_end, $include_taxes );
			$earnings_totals += $earnings;

			$sales_data[] = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );
		}
	} else {
		$y = $dates['year'];
		$temp_data = array();

		while( $y <= $dates['year_end'] ) {

			$last_year = false;

			if( $dates['year'] == $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
				$last_year   = true;
			} elseif( $y == $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} elseif ( $y == $dates['year_end'] ) {
				$month_start = 1;
				$month_end   = $dates['m_end'];
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) {
				$d = $dates['day'];

				if ( $i == $month_end ) {
					$num_of_days = $dates['day_end'];

					if ( $month_start < $month_end ) {
						$d = 1;
					}
				} elseif ( $i > $month_start && $i < $month_end ) {
					$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
					$d = 1;
				} else {
					$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
				}

				while ( $d <= $num_of_days ) {
					$date      = mktime( 0, 0, 0, $i, $d, $y );
					$end_date  = mktime( 23, 59, 59, $i, $d, $y );

					$earnings         = EDD()->payment_stats->get_earnings( $download_id, $date, $end_date, $include_taxes );
					$earnings_totals += $earnings;

					$sales         = EDD()->payment_stats->get_sales( $download_id, $date, $end_date );
					$sales_totals += $sales;

					$temp_data['earnings'][ $y ][ $i ][ $d ] = $earnings;
					$temp_data['sales'][ $y ][ $i ][ $d ]    = $sales;

					$d++;
				}

				$i++;
			}

			$y++;
		}

		$sales_data    = array();
		$earnings_data = array();

		// When using 2 months or smaller as the custom range, show each day individually on the graph
		if ( $day_by_day ) {
			foreach ( $temp_data[ 'sales' ] as $year => $months ) {
				foreach( $months as $month => $dates ) {
					foreach ( $dates as $day => $sales ) {
						$date         = mktime( 0, 0, 0, $month, $day, $year ) * 1000;
						$sales_data[] = array( $date, $sales );
					}
				}
			}

			foreach ( $temp_data[ 'earnings' ] as $year => $months ) {
				foreach( $months as $month => $dates ) {
					foreach ( $dates as $day => $earnings ) {
						$date            = mktime( 0, 0, 0, $month, $day, $year ) * 1000;
						$earnings_data[] = array( $date, $earnings );
					}
				}
			}

		// When showing more than 2 months of results, group them by month, by the first (except for the last month, group on the last day of the month selected)
		} else {
			foreach ( $temp_data[ 'sales' ] as $year => $months ) {
				$month_keys = array_keys( $months );
				$last_month = end( $month_keys );

				foreach ( $months as $month => $days ) {
					$day_keys = array_keys( $days );
					$last_day = end( $day_keys );

					$consolidated_date = $month === $last_month ? $last_day : 1;

					$sales        = array_sum( $days );
					$date         = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
					$sales_data[] = array( $date, $sales );
				}
			}

			foreach ( $temp_data[ 'earnings' ] as $year => $months ) {
				$month_keys = array_keys( $months );
				$last_month = end( $month_keys );

				foreach ( $months as $month => $days ) {
					$day_keys = array_keys( $days );
					$last_day = end( $day_keys );

					$consolidated_date = $month === $last_month ? $last_day : 1;

					$earnings        = array_sum( $days );
					$date            = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
					$earnings_data[] = array( $date, $earnings );
				}
			}
		}
	}

	$data = array(
		__( 'Earnings', 'easy-digital-downloads' ) => $earnings_data,
		__( 'Sales', 'easy-digital-downloads' )    => $sales_data
	);

	?>
	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php printf( __('Earnings Over Time for %s', 'easy-digital-downloads' ), get_the_title( $download_id ) ); ?></span></h3>

			<div class="inside">
				<?php
				edd_reports_graph_controls();
				$graph = new EDD_Graph( $data );
				$graph->set( 'x_mode', 'time' );
				$graph->set( 'multiple_y_axes', true );
				$graph->display();
				?>
				<p class="edd_graph_totals"><strong><?php _e( 'Total earnings for period shown: ', 'easy-digital-downloads' ); echo edd_currency_filter( edd_format_amount( $earnings_totals ) ); ?></strong></p>
				<p class="edd_graph_totals"><strong><?php _e( 'Total sales for period shown: ', 'easy-digital-downloads' ); echo $sales_totals; ?></strong></p>
				<p class="edd_graph_totals"><strong><?php printf( __( 'Average monthly earnings: %s', 'easy-digital-downloads' ), edd_currency_filter( edd_format_amount( edd_get_average_monthly_download_earnings( $download_id ) ) ) ); ?>
				<p class="edd_graph_totals"><strong><?php printf( __( 'Average monthly sales: %s', 'easy-digital-downloads' ), number_format( edd_get_average_monthly_download_sales( $download_id ), 0 ) ); ?>
			</div>
		</div>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Show report graph date filters
 *
 * @since 1.3
 * @return void
*/
function edd_reports_graph_controls() {
	$date_options = apply_filters( 'edd_report_date_options', array(
		'today'        => __( 'Today', 'easy-digital-downloads' ),
		'yesterday'    => __( 'Yesterday', 'easy-digital-downloads' ),
		'this_week'    => __( 'This Week', 'easy-digital-downloads' ),
		'last_week'    => __( 'Last Week', 'easy-digital-downloads' ),
		'last_30_days' => __( 'Last 30 Days', 'easy-digital-downloads' ),
		'this_month'   => __( 'This Month', 'easy-digital-downloads' ),
		'last_month'   => __( 'Last Month', 'easy-digital-downloads' ),
		'this_quarter' => __( 'This Quarter', 'easy-digital-downloads' ),
		'last_quarter' => __( 'Last Quarter', 'easy-digital-downloads' ),
		'this_year'    => __( 'This Year', 'easy-digital-downloads' ),
		'last_year'    => __( 'Last Year', 'easy-digital-downloads' ),
		'other'        => __( 'Custom', 'easy-digital-downloads' )
	) );

	$dates   = edd_get_report_dates();
	$display = $dates['range'] == 'other' ? '' : 'style="display:none;"';
	$view    = edd_get_reporting_view();
	$taxes   = ! empty( $_GET['exclude_taxes'] ) ? false : true;

	if( empty( $dates['day_end'] ) ) {
		$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) );
	}

	?>
	<form id="edd-graphs-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">

				<input type="hidden" name="post_type" value="download"/>
				<input type="hidden" name="page" value="edd-reports"/>
				<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>"/>

				<?php if( isset( $_GET['download-id'] ) ) : ?>
					<input type="hidden" name="download-id" value="<?php echo absint( $_GET['download-id'] ); ?>"/>
				<?php endif; ?>

				<select id="edd-graphs-date-options" name="range">
				<?php foreach ( $date_options as $key => $option ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $dates['range'] ); ?>><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>

				<div id="edd-date-range-options" <?php echo $display; ?>>
					<span><?php _e( 'From', 'easy-digital-downloads' ); ?>&nbsp;</span>
					<select id="edd-graphs-month-start" name="m_start">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-day-start" name="day">
						<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['day'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-year-start" name="year">
						<?php for ( $i = 2007; $i <= date( 'Y' ); $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<span><?php _e( 'To', 'easy-digital-downloads' ); ?>&nbsp;</span>
					<select id="edd-graphs-month-end" name="m_end">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-day-end" name="day_end">
						<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['day_end'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-year-end" name="year_end">
						<?php for ( $i = 2007; $i <= date( 'Y' ); $i++ ) : ?>
						<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year_end'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>

				<div class="edd-graph-filter-options graph-option-section">
					<input type="checkbox" id="exclude_taxes" <?php checked( false, $taxes, true ); ?> value="1" name="exclude_taxes" />
					<label for="exclude_taxes"><?php _e( 'Exclude Taxes', 'easy-digital-downloads' ); ?></label>
				</div>

				<div class="edd-graph-filter-submit graph-option-section">
					<input type="hidden" name="edd_action" value="filter_reports" />
					<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'easy-digital-downloads' ); ?>"/>
				</div>
			</div>
		</div>
	</form>
	<?php
}

/**
 * Sets up the dates used to filter graph data
 *
 * Date sent via $_GET is read first and then modified (if needed) to match the
 * selected date-range (if any)
 *
 * @since 1.3
 * @return array
*/
function edd_get_report_dates() {
	$dates = array();

	$current_time = current_time( 'timestamp' );

	$dates['range'] = isset( $_GET['range'] ) ? $_GET['range'] : apply_filters( 'edd_get_report_dates_default_range', 'last_30_days' );

	if ( 'custom' !== $dates['range'] ) {
		$dates['year']       = isset( $_GET['year'] )    ? $_GET['year']    : date( 'Y' );
		$dates['year_end']   = isset( $_GET['year_end'] )? $_GET['year_end']: date( 'Y' );
		$dates['m_start']    = isset( $_GET['m_start'] ) ? $_GET['m_start'] : 1;
		$dates['m_end']      = isset( $_GET['m_end'] )   ? $_GET['m_end']   : 12;
		$dates['day']        = isset( $_GET['day'] )     ? $_GET['day']     : 1;
		$dates['day_end']    = isset( $_GET['day_end'] ) ? $_GET['day_end'] : cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
	}

	// Modify dates based on predefined ranges
	switch ( $dates['range'] ) :

		case 'this_month' :
			$dates['m_start']  = date( 'n', $current_time );
			$dates['m_end']    = date( 'n', $current_time );
			$dates['day']      = 1;
			$dates['day_end']  = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
			$dates['year']     = date( 'Y' );
			$dates['year_end'] = date( 'Y' );
		break;

		case 'last_month' :
			if ( date( 'n' ) == 1 ) {
				$dates['m_start']  = 12;
				$dates['m_end']    = 12;
				$dates['year']     = date( 'Y', $current_time ) - 1;
				$dates['year_end'] = date( 'Y', $current_time ) - 1;
			} else {
				$dates['m_start']  = date( 'n' ) - 1;
				$dates['m_end']    = date( 'n' ) - 1;
				$dates['year_end'] = $dates['year'];
			}
			$dates['day']     = 1;
			$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
		break;

		case 'today' :
			$dates['day']     = date( 'd', $current_time );
			$dates['m_start'] = date( 'n', $current_time );
			$dates['m_end']   = date( 'n', $current_time );
			$dates['year']    = date( 'Y', $current_time );
		break;

		case 'yesterday' :

			$year  = date( 'Y', $current_time );
			$month = date( 'n', $current_time );
			$day   = date( 'd', $current_time );

			if ( $month == 1 && $day == 1 ) {
				$year  -= 1;
				$month = 12;
				$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );
			} elseif ( $month > 1 && $day == 1 ) {
				$month -= 1;
				$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );
			} else {
				$day -= 1;
			}

			$dates['day']       = $day;
			$dates['m_start']   = $month;
			$dates['m_end']     = $month;
			$dates['year']      = $year;
			$dates['year_end']  = $year;
			$dates['day_end']   = $day;
		break;

		case 'this_week' :
		case 'last_week' :
			$base_time = $dates['range'] === 'this_week' ? current_time( 'mysql' ) : date( 'Y-m-d h:i:s', current_time( 'timestamp' ) - WEEK_IN_SECONDS );
			$start_end = get_weekstartend( $base_time, get_option( 'start_of_week' ) );

			$dates['day']      = date( 'd', $start_end['start'] );
			$dates['m_start']  = date( 'n', $start_end['start'] );
			$dates['year']     = date( 'Y', $start_end['start'] );

			$dates['day_end']  = date( 'd', $start_end['end'] );
			$dates['m_end']    = date( 'n', $start_end['end'] );
			$dates['year_end'] = date( 'Y', $start_end['end'] );
		break;

		case 'last_30_days' :

			$date_start = strtotime( '-30 days' );

			$dates['day']      = date( 'd', $date_start );
			$dates['m_start']  = date( 'n', $date_start );
			$dates['year']     = date( 'Y', $date_start );

			$dates['day_end']  = date( 'd', $current_time );
			$dates['m_end']    = date( 'n', $current_time );
			$dates['year_end'] = date( 'Y', $current_time );

		break;

		case 'this_quarter' :
			$month_now = date( 'n', $current_time );
			$dates['year']     = date( 'Y', $current_time );
			$dates['year_end'] = $dates['year'];

			if ( $month_now <= 3 ) {
				$dates['m_start']  = 1;
				$dates['m_end']    = 3;
			} else if ( $month_now <= 6 ) {
				$dates['m_start'] = 4;
				$dates['m_end']   = 6;
			} else if ( $month_now <= 9 ) {
				$dates['m_start'] = 7;
				$dates['m_end']   = 9;
			} else {
				$dates['m_start']  = 10;
				$dates['m_end']    = 12;
			}

			$dates['day']     = 1;
			$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
		break;

		case 'last_quarter' :
			$month_now = date( 'n' );

			if ( $month_now <= 3 ) {
				$dates['m_start']  = 10;
				$dates['m_end']    = 12;
				$dates['year']     = date( 'Y', $current_time ) - 1; // Previous year
			} else if ( $month_now <= 6 ) {
				$dates['m_start'] = 1;
				$dates['m_end']   = 3;
				$dates['year']    = date( 'Y', $current_time );
			} else if ( $month_now <= 9 ) {
				$dates['m_start'] = 4;
				$dates['m_end']   = 6;
				$dates['year']    = date( 'Y', $current_time );
			} else {
				$dates['m_start'] = 7;
				$dates['m_end']   = 9;
				$dates['year']    = date( 'Y', $current_time );
			}

			$dates['day']      = 1;
			$dates['day_end']  = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'],  $dates['year'] );
			$dates['year_end'] = $dates['year'];
		break;

		case 'this_year' :
			$dates['day']      = 1;
			$dates['m_start']  = 1;
			$dates['m_end']    = 12;
			$dates['year']     = date( 'Y', $current_time );
			$dates['year_end'] = $dates['year'];
		break;

		case 'last_year' :
			$dates['day']      = 1;
			$dates['m_start']  = 1;
			$dates['m_end']    = 12;
			$dates['year']     = date( 'Y', $current_time ) - 1;
			$dates['year_end'] = date( 'Y', $current_time ) - 1;
		break;

	endswitch;

	return apply_filters( 'edd_report_dates', $dates );
}

/**
 * Grabs all of the selected date info and then redirects appropriately
 *
 * @since 1.3
 *
 * @param $data
 */
function edd_parse_report_dates( $data ) {
	$dates = edd_get_report_dates();

	$view          = edd_get_reporting_view();
	$id            = isset( $_GET['download-id'] ) ? $_GET['download-id'] : null;
	$exclude_taxes = isset( $_GET['exclude_taxes'] ) ? $_GET['exclude_taxes'] : null;

	wp_redirect( add_query_arg( $dates, admin_url( 'edit.php?post_type=download&page=edd-reports&view=' . esc_attr( $view ) . '&download-id=' . absint( $id ) . '&exclude_taxes=' . absint( $exclude_taxes ) ) ) ); edd_die();
}
add_action( 'edd_filter_reports', 'edd_parse_report_dates' );

/**
 * EDD Reports Refresh Button
 * @since 2.7
 * @description: Outputs a "Refresh Reports" button for graphs
 */
function edd_reports_refresh_button() {

	$url = wp_nonce_url( add_query_arg( array(
		'edd_action'  => 'refresh_reports_transients',
		'edd-message' => 'refreshed-reports'
	) ), 'edd-refresh-reports' );

	echo '<a href="' . $url . '" title="' . __( 'Clicking this will clear the reports cache', 'easy-digital-downloads' ) . '"  class="button edd-refresh-reports-button">' . __( 'Refresh Reports', 'easy-digital-downloads' ) . '</a>';

}

add_action( 'edd_reports_graph_after', 'edd_reports_refresh_button' );

/**
 * EDD trigger the refresh of reports transients
 *
 * @since 2.7
 *
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function edd_run_refresh_reports_transients( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-refresh-reports' ) ) {
		return;
	}

	// Delete transients
	delete_transient( 'edd_stats_earnings' );
	delete_transient( 'edd_stats_sales' );
	delete_transient( 'edd_estimated_monthly_stats' );
	delete_transient( 'edd_earnings_total' );
	delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
	delete_transient( md5( 'edd_earnings_todaytoday' ) );
}
add_action( 'edd_refresh_reports_transients', 'edd_run_refresh_reports_transients' );
