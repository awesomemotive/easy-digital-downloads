<?php
/**
 * Earnings / Sales Stats
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Payment_Stats Class.
 *
 * This class is for retrieving stats for earnings and sales.
 *
 * Stats can be retrieved for date ranges and pre-defined periods.
 *
 * This class remains here for backwards compatibility purposes. The EDD\Stats class should be used instead.
 *
 * @since 1.8
 * @since 3.0 Refactored to work with custom tables.
 */
class EDD_Payment_Stats extends EDD_Stats {

	/**
	 * Retrieve sale stats.
	 *
	 * @since 1.8
	 * @since 3.0 Refactored to work with custom tables.
	 *
	 * @param int          $download_id The download product to retrieve stats for. If false, gets stats for all products
	 * @param string|bool  $start_date  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool  $end_date    The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param string|array $status      The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int Total amount of sales based on the passed arguments.
	 */
	public function get_sales( $download_id = 0, $start_date = false, $end_date = false, $status = 'complete' ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		if ( empty( $download_id ) ) {
			// Global sale stats
			add_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );

			$count        = 0;
			$total_counts = edd_count_payments();

			foreach ( (array) $status as $payment_status ) {
				if ( isset( $total_counts->$payment_status ) ) {
					$count += absint( $total_counts->$payment_status );
				}
			}

			remove_filter( 'edd_count_payments_where', array( $this, 'count_where' ) );
		} else {
			$this->timestamp = false;

			$date_created_query = array(
				array(
					'after'     => array(
						'year'  => date( 'Y', $this->start_date ),
						'month' => date( 'm', $this->start_date ),
						'day'   => date( 'd', $this->start_date ),
					),
					'before'    => array(
						'year'  => date( 'Y', $this->end_date ),
						'month' => date( 'm', $this->end_date ),
						'day'   => date( 'd', $this->end_date ),
					),
					'inclusive' => true,
				),
			);

			add_filter( 'date_query_valid_columns', array( $this, '__filter_valid_date_columns' ), 2 );

			$date_query         = new \WP_Date_Query( $date_created_query, 'edd_o.date_created' );
			$date_query->column = 'edd_o.date_created';
			$date_query_sql     = $date_query->get_sql();

			remove_filter( 'date_query_valid_columns', array( $this, '__filter_valid_date_columns' ), 2 );

			$statuses = edd_get_net_order_statuses();

			/**
			 * Filters Order statuses that should be included when calculating stats.
			 *
			 * @since 2.7
			 *
			 * @param array $statuses Order statuses to include when generating stats.
			 */
			$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
			$statuses = "'" . implode( "', '", $statuses ) . "'";

			$result = $wpdb->get_row( $wpdb->prepare(
				"SELECT COUNT(edd_oi.id) AS sales
				 FROM {$wpdb->edd_order_items} edd_oi
				 INNER JOIN {$wpdb->edd_orders} edd_o ON edd_oi.order_id = edd_o.id
				 WHERE edd_o.status IN ($statuses) AND edd_oi.product_id = %d {$date_query_sql}",
			$download_id ) );

			$count = null === $result
				? 0
				: absint( $result->sales );
		}

		return $count;
	}

	/**
	 * Retrieve earning stats.
	 *
	 * @since 1.8
	 * @since 3.0 Refactored to work with custom tables.
	 *
	 * @param int         $download_id   The download product to retrieve stats for. If false, gets stats for all products
	 * @param string|bool $start_date    The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool $end_date      The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param bool        $include_taxes If taxes should be included in the earnings graphs
	 *
	 * @return float|int Total amount of sales based on the passed arguments.
	 */
	public function get_earnings( $download_id = 0, $start_date = false, $end_date = false, $include_taxes = true ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		if ( empty( $download_id ) ) {
			/**
			 * Filters Order statuses that should be included when calculating stats.
			 *
			 * @since 2.7
			 *
			 * @param array $statuses Order statuses to include when generating stats.
			 */
			$statuses = apply_filters( 'edd_payment_stats_post_statuses', edd_get_net_order_statuses() );

			// Global earning stats
			$args = array(
				'post_type'              => 'edd_payment',
				'nopaging'               => true,
				'post_status'            => $statuses,
				'fields'                 => 'ids',
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
				'start_date'             => $this->start_date, // These dates are not valid query args, but they are used for cache keys
				'end_date'               => $this->end_date,
				'edd_transient_type'     => 'edd_earnings', // This is not a valid query arg, but is used for cache keying
				'include_taxes'          => $include_taxes,
			);

			$args   = apply_filters( 'edd_stats_earnings_args', $args );
			$cached = get_transient( 'edd_stats_earnings' );
			$key    = md5( wp_json_encode( $args ) );

			if ( ! isset( $cached[ $key ] ) ) {
				if ( empty( $cached ) ) {
					$cached = array();
				}
				$orders = edd_get_orders( array(
					'type'          => 'sale',
					'status__in'    => $args['post_status'],
					'date_query'    => array(
						array(
							'after'     => array(
								'year'  => date( 'Y', $this->start_date ),
								'month' => date( 'm', $this->start_date ),
								'day'   => date( 'd', $this->start_date ),
							),
							'before'    => array(
								'year'  => date( 'Y', $this->end_date ),
								'month' => date( 'm', $this->end_date ),
								'day'   => date( 'd', $this->end_date ),
							),
							'inclusive' => true,
						),
					),
					'no_found_rows' => true,
				) );

				$earnings = 0;

				if ( $orders ) {
					$total_earnings = 0.00;
					$total_tax      = 0.00;


					foreach ( $orders as $order ) {
						$total_earnings += $order->total;
						$total_tax      += $order->tax;
					}

					$earnings = apply_filters( 'edd_payment_stats_earnings_total', $total_earnings, $orders, $args );

					if ( false === $include_taxes ) {
						$earnings -= $total_tax;
					}
				}

				// Cache the results for one hour
				$cached[ $key ] = $earnings;
				set_transient( 'edd_stats_earnings', $cached, HOUR_IN_SECONDS );
			}

		// Download specific earning stats
		} else {
			$args = array(
				'object_id'          => $download_id,
				'object_type'        => 'download',
				'type'               => 'sale',
				'log_type'           => false,
				'date_created_query' => array(
					'after'     => array(
						'year'  => date( 'Y', $this->start_date ),
						'month' => date( 'm', $this->start_date ),
						'day'   => date( 'd', $this->start_date ),
					),
					'before'    => array(
						'year'  => date( 'Y', $this->end_date ),
						'month' => date( 'm', $this->end_date ),
						'day'   => date( 'd', $this->end_date ),
					),
					'inclusive' => true,
				),
				'start_date'         => $this->start_date,
				'end_date'           => $this->end_date,
				'include_taxes'      => $include_taxes,
			);

			$args   = apply_filters( 'edd_stats_earnings_args', $args );
			$cached = get_transient( 'edd_stats_earnings' );
			$key    = md5( wp_json_encode( $args ) );

			if ( false === $cached ) {
				$cached = array();
			}

			if ( ! isset( $cached[ $key ] ) ) {
				$this->timestamp = false;

				add_filter( 'date_query_valid_columns', array( $this, '__filter_valid_date_columns' ), 2 );

				$date_query         = new \WP_Date_Query( $args['date_created_query'], 'edd_o.date_created' );
				$date_query->column = 'edd_o.date_created';
				$date_query_sql     = $date_query->get_sql();

				remove_filter( 'date_query_valid_columns', array( $this, '__filter_valid_date_columns' ), 2 );

				$statuses = edd_get_net_order_statuses();

				/**
				 * Filters Order statuses that should be included when calculating stats.
				 *
				 * @since 2.7
				 *
				 * @param array $statuses Order statuses to include when generating stats.
				 */
				$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
				$statuses = "'" . implode( "', '", $statuses ) . "'";

				$result = $wpdb->get_row( $wpdb->prepare(
					"SELECT SUM(edd_oi.tax) as tax, SUM(edd_oi.total) as total
					 FROM {$wpdb->edd_order_items} edd_oi
					 INNER JOIN {$wpdb->edd_orders} edd_o ON edd_oi.order_id = edd_o.id
					 WHERE edd_o.status IN ($statuses) AND edd_oi.product_id = %d {$date_query_sql}",
				$download_id ) );

				$earnings = 0;

				if ( $result ) {
					$earnings += floatval( $result->total );

					if ( ! $include_taxes ) {
						$earnings -= floatval( $result->tax );
					}

					$earnings = apply_filters_deprecated( 'edd_payment_stats_item_earnings', array( $earnings ), 'EDD 3.0' );
				}

				// Cache the results for one hour
				$cached[ $key ] = $earnings;
				set_transient( 'edd_stats_earnings', $cached, HOUR_IN_SECONDS );
			}
		}

		$result = $cached[ $key ];

		return round( $result, edd_currency_decimal_filter() );
	}

	/**
	 * Get the best selling products
	 *
	 * @since 1.8
	 *
	 * @param int $number The number of results to retrieve with the default set to 10.
	 *
	 * @return array List of download IDs that are best selling
	 */
	public function get_best_selling( $number = 10 ) {
		global $wpdb;

		$downloads = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id as download_id, max(meta_value) as sales
			 FROM $wpdb->postmeta
			 WHERE meta_key='_edd_download_sales' AND meta_value > 0
			 GROUP BY meta_value+0
			 DESC LIMIT %d;", $number
		) );

		return $downloads;
	}

	/**
	 * Retrieve sales stats based on range provided.
	 *
	 * @since 2.6.11
	 * @since 3.0 Refactored to work with custom tables.
	 *
	 * @param string       $range      Date range.
	 * @param string|bool  $start_date The starting date for which we'd like to filter our sale stats.
	 *                                 If false, we'll use the default start date of `this_month`.
	 * @param string|bool  $end_date   The end date for which we'd like to filter our sale stats.
	 *                                 If false, we'll use the default end date of `this_month`.
	 * @param string|array $status     The sale status(es) to count. Only valid when retrieving global stats.
	 *
	 * @return array|false Total amount of sales based on the passed arguments.
	 */
	public function get_sales_by_range( $range = 'today', $day_by_day = false, $start_date = false, $end_date = false, $status = 'complete' ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		$this->end_date = strtotime( 'midnight', $this->end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$cached = get_transient( 'edd_stats_sales' );
		$key    = md5( $range . '_' . date( 'Y-m-d', $this->start_date ) . '_' . date( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) );
		$sales  = isset( $cached[ $key ] ) ? $cached[ $key ] : false;

		if ( false === $sales || ! $this->is_cacheable( $range ) ) {
			if ( false === $cached ) {
				$cached = array();
			}
			if ( ! $day_by_day ) {
				$select   = "DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, COUNT(DISTINCT edd_o.id) as count";
				$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created)";
			} else {
				if ( 'today' === $range || 'yesterday' === $range ) {
					$select   = "DATE_FORMAT(edd_o.date_created, '%%d') AS d, DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, HOUR(edd_o.date_created) AS h, COUNT(DISTINCT edd_o.id) as count";
					$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created), HOUR(edd_o.date_created)";
				} else {
					$select   = "DATE_FORMAT(edd_o.date_created, '%%d') AS d, DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, COUNT(DISTINCT edd_o.id) as count";
					$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created)";
				}
			}

			if ( 'today' === $range || 'yesterday' === $range ) {
				$grouping = 'YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created), HOUR(edd_o.date_created)';
			}

			$statuses = edd_get_net_order_statuses();

			/**
			 * Filters Order statuses that should be included when calculating stats.
			 *
			 * @since 2.7
			 *
			 * @param array $statuses Order statuses to include when generating stats.
			 */
			$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
			$statuses = "'" . implode( "', '", $statuses ) . "'";

			$sales = $wpdb->get_results( $wpdb->prepare(
				"SELECT {$select}
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE edd_o.status IN ({$statuses}) AND edd_o.date_created >= %s AND edd_o.date_created < %s
				 GROUP BY {$grouping}
				 ORDER by edd_o.date_created ASC",
			date( 'Y-m-d', $this->start_date ), date( 'Y-m-d', strtotime( '+1 day', $this->end_date ) ) ), ARRAY_A );

			if ( $this->is_cacheable( $range ) ) {
				$cached[ $key ] = $sales;
				set_transient( 'edd_stats_sales', $cached, HOUR_IN_SECONDS );
			}
		}

		return $sales;
	}

	/**
	 * Retrieve sales stats based on range provided (used for Reporting)
	 *
	 * @since  2.7
	 *
	 * @param string|bool  $start_date The starting date for which we'd like to filter our earnings stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool  $end_date The end date for which we'd like to filter our earnings stats. If false, we'll use the default end date of `this_month`
	 * @param bool         $include_taxes If taxes should be included in the earnings graphs
	 *
	 * @return array Total amount of earnings based on the passed arguments.
	 */
	public function get_earnings_by_range( $range = 'today', $day_by_day = false, $start_date = false, $end_date = false, $include_taxes = true ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		$this->end_date = strtotime( 'midnight', $this->end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$earnings = array();

		$cached = get_transient( 'edd_stats_earnings' );
		$key    = md5( $range . '_' . date( 'Y-m-d', $this->start_date ) . '_' . date( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) );
		$sales  = isset( $cached[ $key ] ) ? $cached[ $key ] : false;

		if ( false === $sales || ! $this->is_cacheable( $range ) ) {
			if ( ! $day_by_day ) {
				$select   = "DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, COUNT(DISTINCT edd_o.id) as count";
				$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created)";
			} else {
				if ( 'today' === $range || 'yesterday' === $range ) {
					$select   = "DATE_FORMAT(edd_o.date_created, '%%d') AS d, DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, HOUR(edd_o.date_created) AS h, COUNT(DISTINCT edd_o.id) as count";
					$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created), HOUR(edd_o.date_created)";
				} else {
					$select   = "DATE_FORMAT(edd_o.date_created, '%%d') AS d, DATE_FORMAT(edd_o.date_created, '%%m') AS m, YEAR(edd_o.date_created) AS y, COUNT(DISTINCT edd_o.id) as count";
					$grouping = "YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created)";
				}
			}

			if ( 'today' === $range || 'yesterday' === $range ) {
				$grouping = 'YEAR(edd_o.date_created), MONTH(edd_o.date_created), DAY(edd_o.date_created), HOUR(edd_o.date_created)';
			}

			$statuses = edd_get_net_order_statuses();

			/**
			 * Filters Order statuses that should be included when calculating stats.
			 *
			 * @since 2.7
			 *
			 * @param array $statuses Order statuses to include when generating stats.
			 */
			$statuses = apply_filters( 'edd_payment_stats_post_statuses', $statuses );
			$statuses = "'" . implode( "', '", $statuses ) . "'";

			$earnings = $wpdb->get_results( $wpdb->prepare(
				"SELECT SUM(total) AS total, SUM(tax) AS tax, $select
				 FROM {$wpdb->edd_orders} edd_o
				 WHERE edd_o.status IN ({$statuses}) AND edd_o.date_created >= %s AND edd_o.date_created < %s
				 GROUP BY {$grouping}
				 ORDER by edd_o.date_created ASC",
			date( 'Y-m-d', $this->start_date ), date( 'Y-m-d', strtotime( '+1 day', $this->end_date ) ) ), ARRAY_A );

			if ( ! $include_taxes ) {
				foreach ( $earnings as $key => $value ) {
					$earnings[ $key ]['total'] -= $earnings[ $key ]['tax'];
					unset( $earnings[ $key ]['tax'] );
				}
			}
		}

		return $earnings;
	}

	/**
	 * Is the date range cachable.
	 *
	 * @since  2.6.11
	 *
	 * @param string $date_range Date range of the report.
	 * @return bool Whether the date range is allowed to be cached or not.
	 */
	public function is_cacheable( $date_range = '' ) {
		if ( empty( $date_range ) ) {
			return false;
		}

		$cacheable_ranges = array(
			'today',
			'yesterday',
			'this_week',
			'last_week',
			'this_month',
			'last_month',
			'this_quarter',
			'last_quarter',
		);

		return in_array( $date_range, $cacheable_ranges, true );
	}

	/**
	 * This public method should not be called directly ever.
	 *
	 * It only exists to hack around a WordPress core issue with WP_Date_Query
	 * column stubbornness.
	 *
	 * @since 3.0
	 *
	 * @access private
	 * @param array $columns
	 * @return array
	 */
	public function __filter_valid_date_columns( $columns = array() ) {
		$columns = array_merge( array( 'date_created' ), $columns );
		return $columns;
	}
}
