<?php
/**
 * Graphing Functions
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2013, Pippin Williamson
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
	switch ( $dates['range'] ) :
		case 'today' :
			$day_by_day	= true;
			break;
		case 'last_year' :
			$day_by_day	= false;
			break;
		case 'this_year' :
			$day_by_day	= false;
			break;
		case 'last_quarter' :
			$day_by_day = false;
			break;
		case 'this_quarter' :
			$day_by_day = false;
			break;
		case 'other' :
			if( ( $dates['m_end'] - $dates['m_start'] ) >= 2 ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$earnings_totals = (float) 0.00; // Total earnings for time period shown
	$sales_totals    = 0;            // Total sales for time period shown

	$earnings_data = array();
	$sales_data    = array();

	if( $dates['range'] == 'today' ) {
		// Hour by hour
		$hour  = 1;
		$month = date( 'n' );
		while ( $hour <= 23 ) :

			$sales    = edd_get_sales_by_date( $dates['day'], $month, $dates['year'], $hour );
			$earnings = edd_get_earnings_by_date( $dates['day'], $month, $dates['year'], $hour );
			
			$sales_totals += $sales;
			$earnings_totals += $earnings;
			
			$date            = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
			$sales_data[]    = array( $date, $sales );
			$earnings_data[] = array( $date, $earnings );
			
			$hour++;
		endwhile;

	} elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {

		//Day by day
		$day     = $dates['day'];
		$day_end = $dates['day_end'];
			$month   = $dates['m_start'];
		while ( $day <= $day_end ) :
			$sales = edd_get_sales_by_date( $day, $month, $dates['year'] );
			$sales_totals += $sales;

			$earnings = edd_get_earnings_by_date( $day, $month, $dates['year'] );
			$earnings_totals += $earnings;

			$date = mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000;
			$sales_data[] = array( $date, $sales );
			$earnings_data[] = array( $date, $earnings );
			$day++;
		endwhile;

	} else {

		$y = $dates['year'];
		while( $y <= $dates['year_end'] ) :

			if( $dates['year'] == $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
			} elseif( $y == $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) :
				if ( $day_by_day ) :
					$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $y );
					$d 				= 1;
					while ( $d <= $num_of_days ) :
						$sales = edd_get_sales_by_date( $d, $i, $y );
						$sales_totals += $sales;

						$earnings = edd_get_earnings_by_date( $d, $i, $y );
						$earnings_totals += $earnings;

						$date = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
						$sales_data[] = array( $date, $sales );
						$earnings_data[] = array( $date, $earnings );
					$d++;
					endwhile;
				else :
					$sales = edd_get_sales_by_date( null, $i, $y );
					$sales_totals += $sales;

					$earnings = edd_get_earnings_by_date( null, $i, $y );
					$earnings_totals += $earnings;

					$date = mktime( 0, 0, 0, $i, 1, $y ) * 1000;
					$sales_data[] = array( $date, $sales );
					$earnings_data[] = array( $date, $earnings );
				endif;
				$i++;
			endwhile;

			$y++;
		endwhile;

	}

	$data = array(
		__( 'Earnings', 'edd' ) => $earnings_data,
		__( 'Sales', 'edd' )    => $sales_data
	);

	?>

	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php _e('Earnings Over Time', 'edd'); ?></span></h3>

			<div class="inside">
				<?php
				edd_reports_graph_controls();
				$graph = new EDD_Graph( $data );
				$graph->set( 'x_mode', 'time' );
				$graph->set( 'multiple_y_axes', true );
				$graph->display();
				
				$estimated = edd_estimated_monthly_stats();
				?>
				
				<p class="edd_graph_totals"><strong><?php _e( 'Total earnings for period shown: ', 'edd' ); echo edd_currency_filter( edd_format_amount( $earnings_totals ) ); ?></strong></p>
				<p class="edd_graph_totals"><strong><?php _e( 'Total sales for period shown: ', 'edd' ); echo $sales_totals; ?></strong></p>
				<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly earnings: ', 'edd' ); echo edd_currency_filter( edd_format_amount( $estimated['earnings'] ) ); ?></strong></p>
				<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly sales: ', 'edd' ); echo $estimated['sales']; ?></strong></p>
			</div>
		</div>
	</div>
	<?php
	echo ob_get_clean();
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
	switch ( $dates['range'] ) :
		case 'today' :
			$day_by_day	= true;
			break;
		case 'last_year' :
			$day_by_day	= false;
			break;
		case 'this_year' :
			$day_by_day	= false;
			break;
		case 'last_quarter' :
			$day_by_day = false;
			break;
		case 'this_quarter' :
			$day_by_day = false;
			break;
		case 'other' :
			if( ( $dates['m_end'] - $dates['m_start'] ) >= 2 ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$earnings_totals = (float) 0.00; // Total earnings for time period shown
	$sales_totals    = 0;            // Total sales for time period shown

	$earnings_data = array();
	$sales_data    = array();
	$stats          = new EDD_Payment_stats;

	if( $dates['range'] == 'today' ) {
		// Hour by hour
		$month  = date( 'n' );
		$hour   = 1;
		$minute = 0;
		$second = 0;
		while ( $hour <= 23 ) :
			
			if( $hour == 23 ) {
				$minute = $second = 59;
			}

			$date = mktime( $hour, $minute, $second, $month, $dates['day'], $dates['year'] );
			$date_end = mktime( $hour + 1, $minute, $second, $month, $dates['day'], $dates['year'] );
			
			//echo $date . ' ' . $date_end . '<br/>';

			$sales = $stats->get_sales( $download_id, $date, $date_end );
			$sales_totals += $sales;
			
			$earnings = $stats->get_earnings( $download_id, $date, $date_end );
			$earnings_totals += $earnings;
			
			$sales_data[] = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );
			
			$hour++;
		endwhile;

	} elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {

		//Day by day
		$day     = $dates['day'];
		$day_end = $dates['day_end'];
		$month   = $dates['m_start'];
		while ( $day <= $day_end ) :
			$date  = mktime( 0, 0, 0, $month, $day, $dates['year'] );
			
			$sales = $stats->get_sales( $download_id, $date );
			$sales_totals += $sales;
			
			$earnings = $stats->get_earnings( $download_id, $date );
			$earnings_totals += $earnings;
			
			$sales_data[] = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );

			$day++;
		endwhile;

	} else {

		$y = $dates['year'];
		while( $y <= $dates['year_end'] ) :

			if( $dates['year'] == $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
			} elseif( $y == $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) :
				if ( $day_by_day ) :
					$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $y );
					$d 				= 1;
					while ( $d <= $num_of_days ) :
						$date  = mktime( 0, 0, 0, $i, $d, $y );
			
						$sales = $stats->get_sales( $download_id, $date );
						$sales_totals += $sales;
			
						$earnings = $stats->get_earnings( $download_id, $date );
						$earnings_totals += $earnings;
			
						$sales_data[] = array( $date * 1000, $sales );
						$earnings_data[] = array( $date * 1000, $earnings );
					$d++;
					endwhile;
				else :
					$date  = mktime( 0, 0, 0, $i, 1, $y );
			
					$sales = $stats->get_sales( $download_id, $date );
					$sales_totals += $sales;
			
					$earnings = $stats->get_earnings( $download_id, $date );
					$earnings_totals += $earnings;
			
					$sales_data[] = array( $date * 1000, $sales );
					$earnings_data[] = array( $date * 1000, $earnings );
				endif;
				$i++;
			endwhile;

			$y++;
		endwhile;

	}

	$data = array(
		__( 'Earnings', 'edd' ) => $earnings_data,
		__( 'Sales', 'edd' )    => $sales_data
	);

   	?>
	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php printf( __('Earnings Over Time for %s', 'edd' ), get_the_title( $download_id ) ); ?></span></h3>

			<div class="inside">
				<?php 
				edd_reports_graph_controls(); 
				$graph = new EDD_Graph( $data );
				$graph->set( 'x_mode', 'time' );
				$graph->set( 'multiple_y_axes', true );
				$graph->display();
				?>
				<p class="edd_graph_totals"><strong><?php _e( 'Total earnings for period shown: ', 'edd' ); echo edd_currency_filter( edd_format_amount( $earnings_totals ) ); ?></strong></p>
				<p class="edd_graph_totals"><strong><?php _e( 'Total sales for period shown: ', 'edd' ); echo $sales_totals; ?></strong></p>
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
		'today' 	    => __( 'Today', 'edd' ),
		'this_week' 	=> __( 'This Week', 'edd' ),
		'last_week' 	=> __( 'Last Week', 'edd' ),
		'this_month' 	=> __( 'This Month', 'edd' ),
		'last_month' 	=> __( 'Last Month', 'edd' ),
		'this_quarter'	=> __( 'This Quarter', 'edd' ),
		'last_quarter'	=> __( 'Last Quarter', 'edd' ),
		'this_year'		=> __( 'This Year', 'edd' ),
		'last_year'		=> __( 'Last Year', 'edd' ),
		'other'			=> __( 'Custom', 'edd' )
	) );

	$dates = edd_get_report_dates();

	$display = $dates['range'] == 'other' ? '' : 'style="display:none;"';

	$view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';

	?>
	<form id="edd-graphs-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">

		       	<input type="hidden" name="post_type" value="download"/>
		       	<input type="hidden" name="page" value="edd-reports"/>
		       	<input type="hidden" name="view" value="<?php echo $view; ?>"/>

		       	<?php if( isset( $_GET['download-id'] ) ) : ?>
		       		<input type="hidden" name="download-id" value="<?php echo absint( $_GET['download-id'] ); ?>"/>
		       	<?php endif; ?>

		       	<select id="edd-graphs-date-options" name="range">
		       		<?php
		       		foreach ( $date_options as $key => $option ) {
		       			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $dates['range'] ) . '>' . esc_html( $option ) . '</option>';
		       		}
		       		?>
		       	</select>

		       	<div id="edd-date-range-options" <?php echo $display; ?>>
					<span><?php _e( 'From', 'edd' ); ?>&nbsp;</span>
			       	<select id="edd-graphs-month-start" name="m_start">
			       		<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<select id="edd-graphs-year" name="year">
			       		<?php for ( $i = 2007; $i <= $dates['year_end']; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<span><?php _e( 'To', 'edd' ); ?>&nbsp;</span>
			       	<select id="edd-graphs-month-start" name="m_end">
			       		<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<select id="edd-graphs-year" name="year_end">
			       		<?php for ( $i = 2007; $i <= $dates['year_end']; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year_end'] ); ?>><?php echo $i; ?></option>
				       	<?php endfor; ?>
			       	</select>
			    </div>

			    <input type="hidden" name="edd_action" value="filter_reports" />
		       	<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'edd' ); ?>"/>
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
 * @return void
*/
function edd_get_report_dates() {
	$dates = array();

	// Make sure the reports are based off of the correct timezone
	date_default_timezone_set( edd_get_timezone_id() );

	$dates['range']      = isset( $_GET['range'] )   ? $_GET['range']   : 'this_month';
	$dates['day']        = isset( $_GET['day'] )     ? $_GET['day']     : null;
	$dates['m_start']    = isset( $_GET['m_start'] ) ? $_GET['m_start'] : 1;
	$dates['m_end']      = isset( $_GET['m_end'] )   ? $_GET['m_end']   : 12;
	$dates['year']       = isset( $_GET['year'] )    ? $_GET['year']    : date( 'Y' );
	$dates['year_end']   = isset( $_GET['y_end'] )   ? $_GET['y_end']   : date( 'Y' );

	// Modify dates based on predefined ranges
	switch ( $dates['range'] ) :

		case 'this_month' :
			$dates['m_start'] 	= date( 'n' );
			$dates['m_end']		= date( 'n' );
			$dates['year']		= date( 'Y' );
		break;

		case 'last_month' :
			if( $dates['m_start'] == 1 ) {
				$dates['m_start'] = 12;
				$dates['m_end']	  = 12;
				$dates['year']    = date( 'Y' ) - 1;
				$dates['year_end']= date( 'Y' ) - 1;
			} else {
				$dates['m_start'] = date( 'n' ) - 1;
				$dates['m_end']	  = date( 'n' ) - 1;
				$dates['year']    = date( 'Y' );
			}
		break;

		case 'today' :
			$dates['day']		= date( 'd' );
			$dates['m_start'] 	= date( 'n' );
			$dates['m_end']		= date( 'n' );
			$dates['year']		= date( 'Y' );
		break;

		case 'this_week' :
			$dates['day']       = date( 'd', current_time( 'timestamp' ) - ( date( 'w' ) - 1 ) *60*60*24 ) - 1;
			$dates['day']      += get_option( 'start_of_week' );
			$dates['day_end']   = $dates['day'] + 6;
			$dates['m_start'] 	= date( 'n' );
			$dates['m_end']		= date( 'n' );
			$dates['year']		= date( 'Y' );
		break;

		case 'last_week' :
			$dates['day']       = date( 'd', current_time( 'timestamp' ) - ( date( 'w' ) - 1 ) *60*60*24 ) - 8;
			$dates['day']      += get_option( 'start_of_week' );
			$dates['day_end']   = $dates['day'] + 6;
			$dates['m_start'] 	= date( 'n' );
			$dates['m_end']		= date( 'n' );
			$dates['year']		= date( 'Y' );
		break;

		case 'this_quarter' :
			$month_now = date( 'n' );

			if ( $month_now <= 3 ) {

				$dates['m_start'] 	= 1;
				$dates['m_end']		= 3;
				$dates['year']		= date( 'Y' );

			} else if ( $month_now <= 6 ) {

				$dates['m_start'] 	= 4;
				$dates['m_end']		= 6;
				$dates['year']		= date( 'Y' );

			} else if ( $month_now <= 9 ) {

				$dates['m_start'] 	= 7;
				$dates['m_end']		= 9;
				$dates['year']		= date( 'Y' );

			} else {

				$dates['m_start'] 	= 10;
				$dates['m_end']		= 12;
				$dates['year']		= date( 'Y' );

			}
		break;

		case 'last_quarter' :
			$month_now = date( 'n' );

			if ( $month_now <= 3 ) {

				$dates['m_start'] 	= 10;
				$dates['m_end']		= 12;
				$dates['year']		= date( 'Y' ) - 1; // Previous year

			} else if ( $month_now <= 6 ) {

				$dates['m_start'] 	= 1;
				$dates['m_end']		= 3;
				$dates['year']		= date( 'Y' );

			} else if ( $month_now <= 9 ) {

				$dates['m_start'] 	= 4;
				$dates['m_end']		= 6;
				$dates['year']		= date( 'Y' );

			} else {

				$dates['m_start'] 	= 7;
				$dates['m_end']		= 9;
				$dates['year']		= date( 'Y' );

			}
		break;

		case 'this_year' :
			$dates['m_start'] 	= 1;
			$dates['m_end']		= 12;
			$dates['year']		= date( 'Y' );
		break;

		case 'last_year' :
			$dates['m_start'] 	= 1;
			$dates['m_end']		= 12;
			$dates['year']		= date( 'Y' ) - 1;
			$dates['year_end']  = date( 'Y' ) - 1;
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

	$view = isset( $_GET['view'] )        ? $_GET['view']        : 'earnings';
	$id   = isset( $_GET['download-id'] ) ? $_GET['download-id'] : null;

	wp_redirect( add_query_arg( $dates, admin_url( 'edit.php?post_type=download&page=edd-reports&view=' . $view . '&download-id=' . $id ) ) ); edd_die();
}
add_action( 'edd_filter_reports', 'edd_parse_report_dates' );
