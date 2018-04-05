<?php
/**
 * Admin Reports Page
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use EDD\Reports;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since 1.0
 * @return void
*/
function edd_reports_page() {
	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );

	wp_enqueue_script( 'postbox' );

	// Start the Reports API.
	new Reports\Init();

	$active_tab = Reports\get_active_tab();
	?>
	<style>
		#edd-item-wrapper {
			max-width: 100%;
		}
		#edd-item-tab-wrapper {
			width: 15%;
		}
		.edd-item-has-tabs #edd-item-card-wrapper {
			width: 85%;
		}
		#edd-item-card-wrapper h2 {
			font-size: 1.5em;
			text-align: center;
		}
		#edd-item-card-wrapper h3 {
			margin-bottom: 6px;
		}
		#edd-item-card-wrapper > div {
			margin-bottom: 10px;
			min-height: 50px;
			clear: both;
		}
	</style>
	<div class="wrap">
		<h1><?php _e( 'Easy Digital Downloads Reports', 'easy-digital-downloads' ); ?></h1>

		<div id="edd-item-wrapper" class="edd-item-has-tabs edd-clearfix">
			<div id="edd-item-tab-wrapper" class="report-tab-wrapper">
				<ul id="edd-item-tab-wrapper-list" class="report-tab-wrapper-list">
					<?php
					$tabs = Reports\get_tabs();

					if ( current_user_can( 'export_shop_reports' ) ) :
						$tabs['export'] = __( 'Export', 'easy-digital-downloads' );
					endif;

					foreach ( $tabs as $slug => $label ) :
						$active = $slug === $active_tab ? true : false;
						$class  = $active ? 'active' : 'inactive';
						?>

						<li class="<?php echo sanitize_html_class( $class ); ?>">

							<?php
							$link = add_query_arg( array(
								'tab'              => $slug,
								'settings-updated' => false ),
							$current_page );
							?>

							<?php if ( ! $active ) : ?>
								<a href="<?php echo esc_url( $link ); ?>">
							<?php endif; ?>

								<span class="edd-item-tab-label-wrap">
									<span class="edd-item-tab-label"><?php echo esc_attr( $label ); ?></span>
								</span>

							<?php if ( ! $active ) : ?>
								</a>
							<?php endif; ?>
						</li>

					<?php endforeach; ?>
				</ul>
			</div>

			<div id="edd-item-card-wrapper" class="edd-report-card-wrapper" style="float: left">
				<?php
				$report = Reports\get_report( $active_tab );
				$label  = is_wp_error( $report ) ? $tabs[ $active_tab ] : $report->get_label();
				?>

				<div id="edd-reports-card-header">
					<h2><?php echo esc_html( $label ); ?></h2>
				</div>

				<?php
				do_action( 'edd_reports_tabs' );

				/**
				 * Fires at the top of the content area of a Reports tab.
				 *
				 * @since 1.0
				 * @since 3.0 Added the `$report` parameter.
				 *
				 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object,
				 *                                                   or WP_Error if invalid.
				 */
				do_action( 'edd_reports_page_top', $report );

				if ( ! is_wp_error( $report ) ) :
					$report->display();
				else :
					Reports\default_display_report( $report );
				endif;

				/**
				 * Fires at the bottom of the content area of a Reports tab.
				 *
				 * @since 1.0
				 * @since 3.0 Added the `$report` parameter.
				 *
				 * @param \EDD\Reports\Data\Report|\WP_Error $report The current report object,
				 *                                                   or WP_Error if invalid.
				 */
				do_action( 'edd_reports_page_bottom', $report );
				?>
			</div>
		</div>

	</div><!-- .wrap -->
	<?php
}

/**
 * Placeholder code to re-register former reports views as report tabs.
 *
 * This code will be replaced before release with individual tab registration logic.
 *
 * @since 3.0
 *
 * @param \EDD\Reports\Data\Reports_Registry $reports Reports registry.
 */
function edd_register_core_reports( $reports ) {

	try {

		$reports->register_endpoint( 'test_tile', array(
			'label' => 'Test Tile',
			'views' => array(
				'tile' => array(
					'data_callback' => function() {
						return 'Some Tile Data';
					}
				)
			)
		) );

		$reports->register_endpoint( 'another_test_tile', array(
			'label'   => 'Another Test Tile',
			'views'   => array(
				'tile' => array(
					'data_callback' => function() {
						return 'Some Tile Data';
					},
					'display_args' => array(
						'context' => 'tertiary',
					),
				),
			),
		) );

		$reports->add_report( 'earnings', array(
			'label'     => __( 'Earnings', 'easy-digital-downloads' ),
			'priority'  => 5,
			'endpoints' => array(
				'tiles' => array( 'test_tile', 'another_test_tile' )
			),
		) );

		$reports->add_report( 'categories', array(
			'label'     => __( 'Earnings by Category', 'easy-digital-downloads' ),
			'priority'  => 10,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'downloads', array(
			'label'     => edd_get_label_plural(),
			'priority'  => 15,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'gateways', array(
			'label'     => __( 'Payment Methods', 'easy-digital-downloads' ),
			'priority'  => 20,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );

		$reports->add_report( 'taxes', array(
			'label'     => __( 'Taxes', 'easy-digital-downloads' ),
			'priority'  => 25,
			'endpoints' => array(
				'tiles' => array( 'test_tile' )
			),
		) );


	} catch ( \EDD_Exception $exception ) {

		edd_debug_log_exception( $exception );

	}
}
add_action( 'edd_reports_init', 'edd_register_core_reports' );

/**
 * Renders the Reports Downloads Table
 *
 * @since 1.3
 * @uses EDD_Download_Reports_Table::prepare_items()
 * @uses EDD_Download_Reports_Table::display()
 * @return void
 */
function edd_reports_downloads_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( isset( $_GET['download-id'] ) )
		return;

	include( dirname( __FILE__ ) . '/class-download-reports-table.php' );

	$downloads_table = new EDD_Download_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_downloads', 'edd_reports_downloads_table' );

/**
 * Renders the detailed report for a specific product
 *
 * @since 1.9
 * @return void
 */
function edd_reports_download_details() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( ! isset( $_GET['download-id'] ) )
		return;

	edd_reports_graph_of_download( absint( $_GET['download-id'] ) );
}
add_action( 'edd_reports_view_downloads', 'edd_reports_download_details' );


/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @uses EDD_Gateawy_Reports_Table::prepare_items()
 * @uses EDD_Gateawy_Reports_Table::display()
 * @return void
 */
function edd_reports_gateways_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateways-reports-table.php' );

	$downloads_table = new EDD_Gateawy_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_gateways', 'edd_reports_gateways_table' );


/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.3
 * @return void
 */
function edd_reports_earnings() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	edd_reports_graph();
	$data = edd_get_earnings_report_data();

	$earnings = $sales = array();

	for ( $i = 0; $i <= 20; $i++ ) {
		$earnings[ $i ][] = current_time( 'timestamp' ) + ( DAY_IN_SECONDS * $i );
		$earnings[ $i ][] = $i + rand( 1, 5 );
	}

	for ( $i = 0; $i <= 20; $i++ ) {
		$sales[ $i ][] = current_time( 'timestamp' ) + 3600 + ( DAY_IN_SECONDS * $i );
		$sales[ $i ][] = $i + rand( 1, 5 );
	}

	?>
	<canvas id="edd-reports-graph"></canvas>

	<script type="application/javascript">

		var date = moment( 'today', 'MMMM DD YYYY' );

		var lineChartData = {
			datasets: [{
				label: "Earnings",
				borderColor: 'rgb(237,194,64)',
				backgroundColor: 'rgb(237,194,64)',
				fill: false,
				data: [
					<?php foreach ( $earnings as $index => $values ) : ?>
					{
						x: moment( <?php echo $values[0] * 1000; ?> ),
						y: <?php echo $values[1]; ?>
					},
					<?php endforeach; ?>
				],
			}, {
				label: "Sales",
				borderColor: 'rgb(175,216,248)',
				backgroundColor: 'rgb(175,216,248)',
				fill: false,
				data: [
					<?php foreach ( $sales as $index => $values ) : ?>
					{
						x: moment( <?php echo $values[0] * 1000; ?> ),
						y: <?php echo $values[1]; ?>
					},
					<?php endforeach; ?>
				],
			}]
		};

		lineChartOptions = {
			responsive: true,
			hoverMode: 'index',
			stacked: false,
			title:{
				display: true,
				text: 'Earnings Over Time'
			},
			scales: {
				yAxes: [{
					type: 'linear',
					display: true,
					position: "left",
				} ],
				xAxes: [{
					type: 'time',
					display: true,
					time: {
						min: moment().startOf( 'month' ),
						max: moment().endOf( 'month' ),
						unit: 'day',
						displayFormats: {
							day: 'MMM D',
						},
						tooltipFormat: 'MMMM Do, YYYY',
					},
				} ],
			}
		};

		lineChartConfig = {
			data: lineChartData,
			options: lineChartOptions
		};

		console.log( lineChartOptions );
		// console.log( lineChartConfig );

		myLine = Chart.Line( $( '#edd-reports-graph' ), lineChartConfig );

		// console.log( myLine );
	</script>

	<?php
}
add_action( 'edd_reports_view_earnings', 'edd_reports_earnings' );


function edd_get_earnings_report_data() {
	// Retrieve the queried dates
	$dates = Reports\get_dates_filter( 'objects' );

	// Determine graph options
	switch ( $dates['range'] ) {
		case 'today' :
		case 'yesterday' :
			$day_by_day = true;
			break;
		case 'last_quarter' :
		case 'this_quarter' :
			$day_by_day = true;
			break;
		case 'other' :
			$difference = ( $dates['start']->getTimestamp() - $dates['end']->getTimestamp() );

			if ( in_array( $dates['range'], array( 'this_year', 'last_year' ), true )
			     || $difference >= YEAR_IN_SECONDS
			) {
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
		$month = $dates['start']->month;

		$i = 0;
		$j = 0;

		$start = $dates['start']->format( 'Y-m-d' );
		$end   = $dates['end']->format( 'Y-m-d' );

		$sales = EDD()->payment_stats->get_sales_by_range( $dates['range'], true, $start, $end );
		$earnings = EDD()->payment_stats->get_earnings_by_range( $dates['range'], true, $start, $end, $include_taxes );

		while ( $hour <= 23 ) {
			$date = mktime( $hour, 0, 0, $month, $dates['start']->day, $dates['start']->year ) * 1000;

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
			if ( ( $dates['start']->day + $i ) <= $dates['end']->day ) {
				$report_dates[ $i ] = array(
					'day'   => (string) $dates['start']->day + $i,
					'month' => $dates['start']->month,
					'year'  => $dates['start']->year,
				);
			} else {
				$report_dates[ $i ] = array(
					'day'   => (string) $i,
					'month' => $dates['end']->month,
					'year'  => $dates['end']->year,
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
		$date_start = $dates['start']->format( 'Y-m-d' );
		$date_end   = $dates['end']->format( 'Y-m-d' );

		$sales = EDD()->payment_stats->get_sales_by_range( $dates['range'], $day_by_day, $date_start, $date_end );
		$earnings = EDD()->payment_stats->get_earnings_by_range( $dates['range'], $day_by_day, $date_start, $date_end, $include_taxes );

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
			$d = $dates['start']->day;
			$m = $dates['start']->month;
			$y = $dates['start']->year;

			if ( ! isset( $temp_data['sales'][ $y ][ $m ][ $d ] ) ) {
				$temp_data['sales'][ $y ][ $m ][ $d ] = 0;
			}

			if ( ! isset( $temp_data['earnings'][ $y ][ $m ][ $d ] ) ) {
				$temp_data['earnings'][ $y ][ $m ][ $d ] = 0;
			}

			$date_start = $dates['start']->addDays( 1 )->format( 'Y-m-d' );
		}

		while ( ! $day_by_day && ( strtotime( $date_start ) <= strtotime( $date_end ) ) ) {
			$m = $dates['start']->month;
			$y = $dates['start']->year;

			if ( ! isset( $temp_data['sales'][ $y ][ $m ] ) ) {
				$temp_data['sales'][ $y ][ $m ] = 0;
			}

			if ( ! isset( $temp_data['earnings'][ $y ][ $m ] ) ) {
				$temp_data['earnings'][ $y ][ $m ] = 0;
			}

			$date_start = $dates['start']->addMonths( 1 )->format( 'Y-m' );
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

	return $data;

}

/**
 * Renders the Reports Earnings By Category Table & Graphs
 *
 * @since  2.4
 */
function edd_reports_categories() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-categories-reports-table.php' );
	?>
			<div class="inside">
				<?php

				$categories_table = new EDD_Categories_Reports_Table();
				$categories_table->prepare_items();
				$categories_table->display();
				?>

				<?php echo $categories_table->load_scripts(); ?>

				<div class="edd-mix-totals">
					<div class="edd-mix-chart">
						<strong><?php _e( 'Category Sales Mix: ', 'easy-digital-downloads' ); ?></strong>
						<?php $categories_table->output_sales_graph(); ?>
					</div>
					<div class="edd-mix-chart">
						<strong><?php _e( 'Category Earnings Mix: ', 'easy-digital-downloads' ); ?></strong>
						<?php $categories_table->output_earnings_graph(); ?>
					</div>
				</div>

				<?php do_action( 'edd_reports_graph_additional_stats' ); ?>

				<p class="edd-graph-notes">
					<span>
						<em><sup>&dagger;</sup> <?php _e( 'All Parent categories include sales and earnings stats from child categories.', 'easy-digital-downloads' ); ?></em>
					</span>
					<span>
						<em><?php _e( 'Stats include all sales and earnings for the lifetime of the store.', 'easy-digital-downloads' ); ?></em>
					</span>
				</p>

			</div>
	<?php
}
add_action( 'edd_reports_view_categories', 'edd_reports_categories' );

/**
 * Renders the Tax Reports
 *
 * @since 1.3.3
 * @return void
 */
function edd_reports_taxes() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );
	?>
	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php _e('Tax Report','easy-digital-downloads' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'easy-digital-downloads' ); ?></p>
				<form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
					<span><?php echo $year; ?></span>: <strong><?php edd_sales_tax_for_year( $year ); ?></strong>&nbsp;&mdash;&nbsp;
					<select name="year">
						<?php for ( $i = 2009; $i <= date( 'Y' ); $i++ ) : ?>
						<option value="<?php echo $i; ?>"<?php selected( $year, $i ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<input type="hidden" name="view" value="taxes" />
					<input type="hidden" name="post_type" value="download" />
					<input type="hidden" name="page" value="edd-reports" />
					<?php submit_button( __( 'Submit', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .metabox-holder -->
	<?php
}
add_action( 'edd_reports_view_taxes', 'edd_reports_taxes' );

/**
 * Renders the 'Export' tab on the Reports Page
 *
 * @since 1.3
 * @return void
 */
function edd_reports_tab_export() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
	<div id="edd-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">

					<?php do_action( 'edd_reports_tab_export_content_top' ); ?>

					<div class="postbox edd-export-earnings-report">
						<h3><span><?php _e( 'Export Earnings Report', 'easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV giving a detailed look into earnings over time.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-earnings" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
								<?php echo _x( 'to', 'Date one to date two', 'easy-digital-downloads' ); ?>
								<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
								<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_Earnings_Report_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-payment-history">
						<h3><span><?php _e('Export Payment History','easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all payments recorded.', 'easy-digital-downloads' ); ?></p>

							<form id="edd-export-payments" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<select name="status">
									<option value="any"><?php _e( 'All Statuses', 'easy-digital-downloads' ); ?></option>
									<?php
									$statuses = edd_get_payment_statuses();
									foreach( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-customers">
						<h3><span><?php _e('Export Customers in CSV','easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of customers.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-customers" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download', 'id' => 'edd_customer_export_download', 'chosen' => true ) ); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_Customers_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-downloads">
						<h3><span><?php _e('Export Download Products in CSV','easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of download products.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_Downloads_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-download-history">
						<h3><span><?php _e('Export Download History in CSV','easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of file downloads. To download a CSV for all file downloads, leave "Choose a Download" as it is.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-file-downloads" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_file_download_export_download', 'chosen' => true ) ); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-file-download-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-file-download-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_File_Downloads_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-api-requests">
						<h3><span><?php _e('Export API Requests in CSV','easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of API request logs.', 'easy-digital-downloads' ); ?></p>
							<form id="edd-export-api-requests" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-api-requests-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-api-requests-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_API_Requests_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox edd-export-payment-history">
						<h3><span><?php _e('Export Sales', 'easy-digital-downloads' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all sales.', 'easy-digital-downloads' ); ?></p>

							<form id="edd-export-sales" class="edd-export-form edd-import-export-form" method="post">
								<?php echo EDD()->html->product_dropdown( array( 'name' => 'download_id', 'id' => 'edd_sales_export_download', 'chosen' => true ) ); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-sales-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
								<?php echo EDD()->html->date_field( array( 'id' => 'edd-sales-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
								<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
								<input type="hidden" name="edd-export-class" value="EDD_Batch_Sales_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php do_action( 'edd_reports_tab_export_content_bottom' ); ?>

				</div><!-- .post-body-content -->
			</div><!-- .post-body -->
		</div><!-- .metabox-holder -->
	</div><!-- #edd-dashboard-widgets-wrap -->
	<?php
}
add_action( 'edd_reports_tab_export', 'edd_reports_tab_export' );

/**
 * Retrieves estimated monthly earnings and sales
 *
 * @since 1.5
 *
 * @param bool  $include_taxes If the estimated earnings should include taxes
 * @return array
 */
function edd_estimated_monthly_stats( $include_taxes = true ) {

	$estimated = get_transient( 'edd_estimated_monthly_stats' . $include_taxes );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0
		);

		$stats = new EDD_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month', null, $include_taxes );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'edd_estimated_monthly_stats' . $include_taxes, $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}

/**
 * Adds postbox nonces, which are used to save the position of tile endpoint meta boxes.
 *
 * @since 3.0
 */
function edd_add_screen_options_nonces() {
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce' , false );
	wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce' , false );
}
add_action( 'admin_footer', 'edd_add_screen_options_nonces' );
