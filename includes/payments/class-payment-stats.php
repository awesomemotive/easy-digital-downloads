<?php
/**
 * Earnings / Sales Stats
 *
 * @package     EDD
 * @subpackage  Classes/Stats
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


	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @access private
	 * @since 1.8
	 */
	private $start_date;


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
	 * @access private
	 * @since 1.8
	 */
	private $end_date;

	/**
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct() { /* nothing here. Call get_sales() and get_earnings() directly */ }


	/**
	 * Retrieve sale stats
	 *
	 * @access public
	 * @since 1.8
	 * @param $download_id INT The download product to retrieve stats for. If false, gets stats for all products
	 * @param $status string The sale status to count. Only valid when retrieving global stats
	 * @return float|int
	 */
	public function get_sales( $download_id = 0, $start_date = false, $end_date = false, $status = 'publish' ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )
			return $this->start_date;

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )
			return $this->end_date;

		if( empty( $download_id ) ) {

			// Global sale stats
			add_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );

			$count = edd_count_payments()->$status;

			remove_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );

		} else {

			// Product specific stats
			global $edd_logs;

			add_filter( 'posts_where', array( $this, 'payments_where' ) );

			$count = $edd_logs->get_log_count( $download_id, 'sale' );

			remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		}

		return $count;

	}


	/**
	 * Retrieve earning stats
	 *
	 * @access public
	 * @since 1.8
	 * @param $download_id INT The download product to retrieve stats for. If false, gets stats for all products
	 * @return float|int
	 */
	public function get_earnings( $download_id = 0, $start_date = false, $end_date = false ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )
			return $this->start_date;

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )
			return $this->end_date;

		$earnings = 0;

		add_filter( 'posts_where', array( $this, 'payments_where' ) );

		if( empty( $download_id ) ) {

			// Global earning stats

			$args = array(
				'post_type'              => 'edd_payment',
				'nopaging'               => true,
				'meta_key'               => '_edd_payment_mode',
				'meta_value'             => 'live',
				'post_status'            => array( 'publish', 'revoked' ),
				'fields'                 => 'ids',
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
				'start_date'             => $this->start_date, // These dates are not valid query args, but they are used for cache keys
				'end_date'               => $this->end_date
			);

			$args = apply_filters( 'edd_stats_earnings_args', $args );
			$key  = md5( serialize( $args ) );

			$earnings = get_transient( $key );
			if( false === $earnings ) {
				$sales = get_posts( $args );
				$earnings = 0;
				if ( $sales ) {
					foreach ( $sales as $sale ) {
						$amount    = edd_get_payment_amount( $sale );
						$earnings  = $earnings + $amount;
					}
				}
				// Cache the results for one hour
				set_transient( $key, $earnings, 60*60 );
			}

		} else {

			// Download specific earning stats

			global $edd_logs, $wpdb;

			$args = array(
				'post_parent'      => $download_id,
				'nopaging'         => true,
				'log_type'         => 'sale',
				'fields'           => 'ids',
				'suppress_filters' => false,
				'start_date'       => $this->start_date, // These dates are not valid query args, but they are used for cache keys
				'end_date'         => $this->end_date
			);

			$args = apply_filters( 'edd_stats_earnings_args', $args );
			$key  = md5( serialize( $args ) );

			$earnings = get_transient( $key );
			if( false === $earnings ) {

				$log_ids  = $edd_logs->get_connected_logs( $args, 'sale' );
				$earnings = 0;

				if( $log_ids ) {
					$log_ids     = implode( ',', $log_ids );
					$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_edd_log_payment_id' AND post_id IN ($log_ids);" );

					foreach( $payment_ids as $payment_id ) {
						$items = edd_get_payment_meta_cart_details( $payment_id );
						foreach( $items as $item ) {
							$earnings += $item['price'];
						}
					}
				}

				// Cache the results for one hour
				set_transient( $key, $earnings, 60*60 );
			}
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		return round( $earnings, 2 );

	}

	/**
	 * Get the best selling products
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public function get_best_selling( $number = 10 ) {

		global $wpdb;

		$downloads = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id as download_id, max(meta_value) as sales
				FROM $wpdb->postmeta WHERE meta_key='_edd_download_sales' AND meta_value > 0
				GROUP BY meta_value
				DESC LIMIT %d;", $number
		) );

		return $downloads;
	}


	/**
	 * Get the predefined date periods permitted
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public function get_predefined_dates() {
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

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly
	 *
	 * @access private
	 * @since 1.8
	 * @return void
	 */
	private function setup_dates( $_start_date = 'this_month', $_end_date = false ) {

		if( empty( $_start_date ) ) {
			$this->start_date = 'this_month';
		}

		$this->start_date = $_start_date;

		if( ! empty( $_end_date ) ) {
			$this->end_date = $_end_date;
		}

		if( ! $this->end_date )
			$this->end_date = $this->start_date;

		$this->start_date = $this->convert_date( $this->start_date );
		$this->end_date   = $this->convert_date( $this->end_date, true );

	}

	/**
	 * Converts a date to a timestamp
	 *
	 * @access private
	 * @since 1.8
	 * @return array|WP_Error If the date is invalid, a WP_Error object will be returned
	 */
	private function convert_date( $date, $end_date = false ) {

		$timestamp   = false;
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
			$timestamp = true;

		} else if( false !== strtotime( $date ) ) {

			$timestamp = true;
			$date      = strtotime( $date, current_time( 'timestamp' ) );

		} else {

			return WP_Error( 'invalid_date', __( 'Improper date provided.', 'edd' ) );

		}

		if( ! is_wp_error( $date ) && ! $timestamp ) {

			// Create an exact timestamp
			$date = mktime( $hour, $minute, 0, $month, $day, $year );

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
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
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

		if( $this->start_date ) {
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

}