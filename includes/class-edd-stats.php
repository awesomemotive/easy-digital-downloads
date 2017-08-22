<?php
/**
 * Stats Base
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Stats Class
 *
 * Base class for other stats classes
 *
 * Primarily for setting up dates and ranges
 *
 * @since 1.8
 */
class EDD_Stats {


	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @access public
	 * @since 1.8
	 */
	public $start_date;


	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @access public
	 * @since 1.8
	 */
	public $end_date;

	/**
	 * Flag to determine if current query is based on timestamps
	 *
	 * @access public
	 * @since 1.9
	 */
	public $timestamp;

	/**
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct() { /* nothing here. Call get_sales() and get_earnings() directly */ }


	/**
	 * Get the predefined date periods permitted
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public function get_predefined_dates() {
		$predefined = array(
			'today'        => __( 'Today',        'easy-digital-downloads' ),
			'yesterday'    => __( 'Yesterday',    'easy-digital-downloads' ),
			'this_week'    => __( 'This Week',    'easy-digital-downloads' ),
			'last_week'    => __( 'Last Week',    'easy-digital-downloads' ),
			'this_month'   => __( 'This Month',   'easy-digital-downloads' ),
			'last_month'   => __( 'Last Month',   'easy-digital-downloads' ),
			'this_quarter' => __( 'This Quarter', 'easy-digital-downloads' ),
			'last_quarter' => __( 'Last Quarter',  'easy-digital-downloads' ),
			'this_year'    => __( 'This Year',    'easy-digital-downloads' ),
			'last_year'    => __( 'Last Year',    'easy-digital-downloads' )
		);
		return apply_filters( 'edd_stats_predefined_dates', $predefined );
	}

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function setup_dates( $_start_date = 'this_month', $_end_date = false ) {

		if( empty( $_start_date ) ) {
			$_start_date = 'this_month';
		}

		if( empty( $_end_date ) ) {
			$_end_date = $_start_date;
		}

		$this->start_date = $this->convert_date( $_start_date );
		$this->end_date   = $this->convert_date( $_end_date, true );

	}

	/**
	 * Converts a date to a timestamp
	 *
	 * @access public
	 * @since 1.8
	 * @return array|WP_Error If the date is invalid, a WP_Error object will be returned
	 */
	public function convert_date( $date, $end_date = false ) {

		$this->timestamp = false;
		$second          = $end_date ? 59 : 0;
		$minute          = $end_date ? 59 : 0;
		$hour            = $end_date ? 23 : 0;
		$day             = 1;
		$month           = date( 'n', current_time( 'timestamp' ) );
		$year            = date( 'Y', current_time( 'timestamp' ) );

		if ( array_key_exists( $date, $this->get_predefined_dates() ) ) {

			// This is a predefined date rate, such as last_week
			switch( $date ) {

				case 'this_month' :

					if( $end_date ) {

						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;

					}

					break;

				case 'last_month' :

					if( $month == 1 ) {

						$month = 12;
						$year--;

					} else {

						$month--;

					}

					if( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}

					break;

				case 'today' :

					$day = date( 'd', current_time( 'timestamp' ) );

					if( $end_date ) {
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'yesterday' :

					$day = date( 'd', current_time( 'timestamp' ) ) - 1;

					// Check if Today is the first day of the month (meaning subtracting one will get us 0)
					if( $day < 1 ) {

						// If current month is 1
						if( 1 == $month ) {

							$year -= 1; // Today is January 1, so skip back to last day of December
							$month = 12;
							$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						} else {

							// Go back one month and get the last day of the month
							$month -= 1;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						}
					}

					break;

				case 'this_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) ) *60*60*24;

				 	if( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if( ! $end_date ) {

					 	// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'last_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) ) *60*60*24;

				 	if( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if( ! $end_date ) {

					 	// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'this_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$month = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'last_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$year -= 1;
							$month = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'this_year' :

					if( ! $end_date ) {
						$month  = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_year' :

					$year -= 1;
					if( ! $end_date ) {
						$month = 1;
						$day   = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

				break;

			}


		} else if( is_numeric( $date ) ) {

			// return $date unchanged since it is a timestamp
			$this->timestamp = true;

		} else if( false !== strtotime( $date ) ) {

			$date  = strtotime( $date, current_time( 'timestamp' ) );
			$year  = date( 'Y', $date );
			$month = date( 'm', $date );
			$day   = date( 'd', $date );

		} else {

			return new WP_Error( 'invalid_date', __( 'Improper date provided.', 'easy-digital-downloads' ) );

		}

		if( false === $this->timestamp ) {
			// Create an exact timestamp
			$date = mktime( $hour, $minute, $second, $month, $day, $year );

		}

		return apply_filters( 'edd_stats_date', $date, $end_date, $this );

	}

	/**
	 * Modifies the WHERE flag for payment counts
	 *
	 * @access public
	 * @since 1.8
	 * @return string
	 */
	public function count_where( $where = '' ) {
		// Only get payments in our date range

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

	/**
	 * Modifies the WHERE flag for payment queries
	 *
	 * @access public
	 * @since 1.8
	 * @return string
	 */
	public function payments_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if( ! is_wp_error( $this->start_date ) ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( ! is_wp_error( $this->end_date ) ) {

			if( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date  = date( $format, $this->end_date );

			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

}
