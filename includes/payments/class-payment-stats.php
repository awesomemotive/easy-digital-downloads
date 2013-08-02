<?php
/**
 * Earnings / Sales Stats
 *
 * @package     EDD
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/

/**
 * EDD_Stats Class
 *
 * This class is for retrieving stats for earnings and sales
 *
 * Stats can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.8
 */
class EDD_Stats {


	public $type; // 'sales' or 'earnings'

	public $start_date;

	public $end_date;

	/**
	 * Get things going
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct( $_type = 'earnings', $_start_date = 'this_month', $_end_date = false ) {

		$this->type = $_type;

		if( ! empty( $_start_date ) ) {
			$this->start_date = $_start_date;
		}

		if( ! empty( $_date1 ) ) {
			$this->end_date = $_end_date;
		}

		$this->setup_dates();

	}

	public function get_stats( $download_id = 0 ) {

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )
			return $this->start_date;

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )
			return $this->end_date;

		if( 'sales' == $this->type ) {
			$stats = $this->get_sales( $download_id );
		} else {
			$stats = $this->get_earnings( $download_id );
		}

		return apply_filters( 'edd_stats', $stats, $this->type, &$this );
	}

	public function get_sales( $download_id = 0, $status = 'publish' ) {

		// if download Id, get stats for specific download

		add_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );

		$count = edd_count_payments()->$status;

		remove_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );

		return $count;

	}

	public function get_earnings( $download_id = 0 ) {

		// if download Id, get stats for specific download

		$earnings = 0;

		add_filter( 'posts_where', array( $this, 'payments_where' ) );

		$args = array(
			'post_type'   => 'edd_payment',
			'nopaging'    => true,
			'meta_key'    => '_edd_payment_mode',
			'meta_value'  => 'live',
			'post_status' => array( 'publish', 'revoked' ),
			'fields'      => 'ids',
			'update_post_term_cache' => false,
			'suppress_filters' => false,
			'start_date'  => $this->start_date, // These dates are not valid query args, but they are used for cache keys
			'end_date'    => $this->end_date
		);

		$args     = apply_filters( 'edd_stats_earnings_args', $args );
		$key      = md5( serialize( $args ) );

		//$earnings = get_transient( $key );
		//if( false === $earnings ) {
			$sales = get_posts( $args );
			$earnings = 0;
			if ( $sales ) {
				foreach ( $sales as $sale ) {
					$amount    = edd_get_payment_amount( $sale );
					$earnings  = $earnings + $amount;
				}
			}
			// Cache the results for one hour
			//set_transient( $key, $earnings, 60*60 );
		//}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		return round( $earnings, 2 );

	}

	private function get_predefined_dates() {
		$predefined = array(
			'today'        => __( 'Today',        'edd' ),
			'yesterday'    => __( 'Yesterday',    'edd' ),
			'this_week'    => __( 'This Week',    'edd' ),
			'last_week'    => __( 'Last Week',    'edd' ),
			'this_month'   => __( 'This Month',   'edd' ),
			'last_month'   => __( 'Last Month',   'edd' ),
			'this_quarter' => __( 'This Quarter', 'edd' ),
			'last_quarter' => __( 'Last Quater',  'edd' ),
			'this_year'    => __( 'This Year',    'edd' ),
			'last_year'    => __( 'Last Year',    'edd' )
		);
		return apply_filters( 'edd_stats_predefined_dates', $predefined );
	}


	private function setup_dates() {

		if( ! $this->end_date )
			$this->end_date = $this->start_date;

		$this->start_date = $this->convert_date( $this->start_date );
		$this->end_date   = $this->convert_date( $this->end_date, true );

	}


	private function convert_date( $date, $end_date = false ) {

		// Not even remotely finished

		$is_string   = true;
		$is_time     = false;
		$is_date     = false;

		$minute      = 0;
		$hour        = 0;
		$day         = 1;
		$month       = date( 'n', current_time( 'timestamp' ) );
		$year        = date( 'Y', current_time( 'timestamp' ) );

		if ( array_key_exists( $date, $this->get_predefined_dates() ) ) {
			// This is a predefined date rate, such as last_week

			switch( $date ) {

				case 'this_month' :
					if( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}
				break;

				case 'last_month' :

					if( $month == 1 && ! $end_date ) {
						$month = 12;
					} else {
						$month = date( 'n', current_time( 'timestamp' ) ) - 1;
					}

					if( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}

				break;

				case 'today' :

					$day = date( 'd', current_time( 'timestamp' ) );

				break;

				case 'yesterday' :

					$day = date( 'd' ) - 1;
					if( $day < 1 ) {
						// Today is the first day of the month
						if( 1 == $month ) {
							$year -= 1; // Today is January 1, so skip back to December
							$month -= 1;
							$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						} else {
							$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						}
					}

				break;

				case 'this_week' :

					$days_to_week_start = ( date( 'w' ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) );
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

					$days_to_week_start = ( date( 'w' ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) );
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
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
						}

					} else {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$month = 12;
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
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
						}

					} else {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
						}

					}

				break;

				case 'this_year' :

					if( ! $end_date ) {
						$month = 1;
					} else {
						$month = 12;
					}

				break;

				case 'last_year' :

					$year -= 1;
					if( ! $end_date ) {
						$month = 1;
					} else {
						$month = 12;
					}

				break;

			}


		} else if( is_int( $date ) ) {

			// return $date unchanged since it is a timestamp

		} else if( false !== strtotime( $date ) ) {

			// This is a date provided as a string
			$date = strtotime( $date, current_time( 'timestamp' ) );

		} else {

			$date = new WP_Error( 'invalid_date', __( 'Improper date provided.', 'edd' ) );

		}

		if( ! is_wp_error( $date ) ) {

			return mktime( $hour, $minute, 0, $month, $day, $year );

		} else {

			return $date; // Return the error

		}

	}


	public function count_where( $where = '' ) {
		// Only get payments in our date range

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";
		//echo $where; exit;
		return $where;
	}

	public function payments_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		//echo $where; exit;
		return $where;
	}

}