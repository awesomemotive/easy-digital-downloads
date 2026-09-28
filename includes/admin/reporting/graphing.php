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

	$dates['range']      = isset( $_GET['range'] )   ? sanitize_key( $_GET['range'] )    : 'this_month';
	$dates['year']       = isset( $_GET['year'] )    ? absint( $_GET['year'] )    : date( 'Y' );
	$dates['year_end']   = isset( $_GET['year_end'] )? absint( $_GET['year_end'] ): date( 'Y' );
	$dates['m_start']    = isset( $_GET['m_start'] ) ? absint( $_GET['m_start'] ) : 1;
	$dates['m_end']      = isset( $_GET['m_end'] )   ? absint( $_GET['m_end'] )   : 12;
	$dates['day']        = isset( $_GET['day'] )     ? absint( $_GET['day'] )     : 1;
	$dates['day_end']    = isset( $_GET['day_end'] ) ? absint( $_GET['day_end'] ) : cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );

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
			if( date( 'n' ) == 1 ) {
				$dates['m_start']  = 12;
				$dates['m_end']    = 12;
				$dates['year']     = date( 'Y', $current_time ) - 1;
				$dates['year_end'] = date( 'Y', $current_time ) - 1;
			} else {
				$dates['m_start']  = date( 'n' ) - 1;
				$dates['m_end']    = date( 'n' ) - 1;
				$dates['year_end'] = $dates['year'];
			}
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

		case 'this_quarter' :
			$month_now = date( 'n', $current_time );

			if ( $month_now <= 3 ) {

				$dates['m_start'] = 1;
				$dates['m_end']   = 4;
				$dates['year']    = date( 'Y', $current_time );

			} else if ( $month_now <= 6 ) {

				$dates['m_start'] = 4;
				$dates['m_end']   = 7;
				$dates['year']    = date( 'Y', $current_time );

			} else if ( $month_now <= 9 ) {

				$dates['m_start'] = 7;
				$dates['m_end']   = 10;
				$dates['year']    = date( 'Y', $current_time );

			} else {

				$dates['m_start']  = 10;
				$dates['m_end']    = 1;
				$dates['year']     = date( 'Y', $current_time );
				$dates['year_end'] = date( 'Y', $current_time ) + 1;

			}
		break;

		case 'last_quarter' :
			$month_now = date( 'n' );

			if ( $month_now <= 3 ) {

				$dates['m_start']  = 10;
				$dates['m_end']    = 12;
				$dates['year']     = date( 'Y', $current_time ) - 1; // Previous year
				$dates['year_end'] = date( 'Y', $current_time ) - 1; // Previous year

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
		break;

		case 'this_year' :
			$dates['m_start'] = 1;
			$dates['m_end']   = 12;
			$dates['year']    = date( 'Y', $current_time );
		break;

		case 'last_year' :
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
