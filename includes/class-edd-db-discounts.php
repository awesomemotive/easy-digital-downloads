<?php
/**
 * Discounts DB class
 *
 * This class is for interacting with the discounts database table
 *
 * @package     EDD
 * @subpackage  Classes/Discounts
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_DB_Discounts Class
 *
 * @since 3.0
 */
class EDD_DB_Discounts extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @access public
	 * @since  3.0
	 * @var    string
	 */
	public $cache_group = 'discounts';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_discounts';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	/**
	 * Retrieve table columns and data types.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array Array of table columns and data types.
	 */
	public function get_columns() {
		return array(
			'id'                => '%d',
			'name'              => '%s',
			'code'              => '%s',
			'status'            => '%s',
			'type'              => '%s',
			'amount'            => '%s',
			'description'       => '%s',
			'date_created'      => '%s',
			'start_date'        => '%s',
			'end_date'          => '%s',
			'max_uses'          => '%d',
			'use_count'         => '%d',
			'min_cart_price'    => '%f',
			'once_per_customer' => '%d',
			'product_condition' => '%s',
			'scope'             => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array Array of the default values for each column in the table.
	 */
	public function get_column_defaults() {
		return array(
			'id'                => 0,
			'name'              => '',
			'code'              => '',
			'status'            => '',
			'type'              => '',
			'amount'            => '',
			'description'       => '',
			'max_uses'          => 0,
			'use_count'         => 0,
			'min_cart_price'    => 0.00,
			'once_per_customer' => 0,
			'product_condition' => '',
			'scope'             => 'global',
			'date_created'      => date( 'Y-m-d H:i:s' ),
			'start_date'        => '0000-00-00 00:00:00',
			'end_date'          => '0000-00-00 00:00:00',
		);
	}

	/**
	 * Insert a new discount.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @see EDD_DB::insert()
	 *
	 * @param array $data {
	 *      Data for the discount.
	 *
	 *      @type string $name              Discount name.
	 *      @type string $code              Discount code.
	 *      @type string $status            Discount status (active/inactive/expired).
	 *      @type string $type              Discount type (flat/percent).
	 *      @type float  $amount            Discount amount.
	 *      @type string $description       Discount description.
	 *      @type int    $max_uses          Maximum amount of uses.
	 *      @type int    $use_count         Number of times discount has been used.
	 *      @type float  $min_cart_price    Minimum amount in cart for discount to hold.
	 *      @type int    $once_per_customer Can it only be used once by a customer?
	 *      @type string $product_condition Works in conjunction with product requirements.
	 *      @type string $scope             Whether the discount applies globally.
	 *      @type string $date_created      Date created (default blank and auto-filled on insert).
	 *      @type string $start_date        Start date of the discount (YYYY-mm-dd HH:mm:ss).
	 *      @type string $end_date          End date of the discount (YYYY-mm-dd HH:mm:ss).
	 * }
	 *
	 * @return int ID of the inserted discount.
	 */
	public function insert( $data, $type = '' ) {
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update a discount.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int   $row_id Discount ID.
	 * @param array $data {
	 *      Data for the discount.
	 *
	 *      @type string $name              Discount name.
	 *      @type string $code              Discount code.
	 *      @type string $status            Discount status (active/inactive/expired).
	 *      @type string $type              Discount type (flat/percent).
	 *      @type float  $amount            Discount amount.
	 *      @type string $description       Discount description.
	 *      @type int    $max_uses          Maximum amount of uses.
	 *      @type int    $use_count         Number of times discount has been used.
	 *      @type float  $min_cart_price    Minimum amount in cart for discount to hold.
	 *      @type int    $once_per_customer Can it only be used once by a customer?
	 *      @type string $product_condition Works in conjunction with product requirements.
	 *      @type string $scope             Whether the discount applies globally.
	 *      @type string $start_date        Start date of the discount (YYYY-mm-dd HH:mm:ss).
	 *      @type string $end_date          End date of the discount (YYYY-mm-dd HH:mm:ss).
	 * }
	 * @param mixed string|array $where Where clause to filter update.
	 *
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Delete a discount.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int $row_id ID of the discount to delete.
	 *
	 * @return bool True if deletion was successful, false otherwise.
	 */
	public function delete( $row_id = 0 ) {
		if ( empty( $row_id ) ) {
			return false;
		}

		$result = parent::delete( $row_id );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Retrieve discounts from the database
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Query arguments.
	 * }
	 *
	 * @return array $discounts Array of `EDD_Discount` objects.
	 */
	public function get_discounts( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'search'  => '',
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		// Account for 'paged' in legacy $args
		if ( isset( $args['paged'] ) && $args['paged'] > 1 ) {
			$number         = isset( $args['number'] ) ? $args['number'] : $defaults['number'];
			$args['offset'] = ( ( $args['paged'] - 1 ) * $number );
			unset( $args['paged'] );
		}

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$args['search'] = $wpdb->esc_like( $args['search'] );
		}

		$where = $this->parse_where( $args );

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if ( 'amount' === $args['orderby'] ) {
			$args['orderby'] = 'amount+0';
		}

		$cache_key = md5( 'edd_discounts_' . serialize( $args ) );

		$discounts = wp_cache_get( $cache_key, 'discounts' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( false === $discounts ) {
			$discounts = $wpdb->get_col( $wpdb->prepare(
				"
					SELECT id
					FROM $this->table_name
					$where
					ORDER BY {$args['orderby']} {$args['order']}
					LIMIT %d,%d;
				", absint( $args['offset'] ), absint( $args['number'] ) ), 0 );

			if ( ! empty( $discounts ) ) {
				foreach ( $discounts as $key => $discount ) {
					$discounts[ $key ] = new EDD_Discount( $discount );
				}

				wp_cache_set( $cache_key, $discounts, 'discounts', 3600 );
			}
		}

		return $discounts;
	}

	/**
	 * Parse `WHERE` clause for the SQL query.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 *
	 *      @type mixed array|string $type         Discount types (flat/percent).
	 *      @type mixed array|string $status       Discount status (active/inactive/expired).
	 *      @type mixed array|string $date_created Date created. Can pass a `start` and `end` key in the array to allow
	 *                                             for further filtering.
	 *      @type mixed array|string $end_date     Discount expiration date. Can pass a `start` and `end` key in the
	 *                                             array to allow for further filtering.
	 *      @type mixed array|string $start_date   Discount start date. Can pass a `start` and `end` key in the array to
	 *                                             allow for further filtering.
	 *      @type string             $search       Search parameters.
	 * }
	 *
	 * @return string `WHERE` clause for the SQL query.
	 */
	private function parse_where( $args ) {
		$where = '';

		// Specific types
		if ( ! empty( $args['type'] ) ) {

			if ( is_array( $args['type'] ) ) {
				$types = implode( "','", array_map( 'sanitize_text_field', $args['type'] ) );
			} else {
				$types = sanitize_text_field( $args['type'] );
			}

			$where .= " AND `type` IN( '{$types}' ) ";

		}

		// Specific statuses
		if ( ! empty( $args['status'] ) ) {

			if ( is_array( $args['status'] ) ) {
				$statuses = implode( "','", array_map( 'sanitize_text_field', $args['status'] ) );
			} else {
				$statuses = sanitize_text_field( $args['status'] );
			}

			$where .= " AND `status` IN( '{$statuses}' ) ";

		}

		// Created for a specific date or in a date range
		if ( ! empty( $args['date_created'] ) ) {

			if ( is_array( $args['date_created'] ) ) {

				if ( ! empty( $args['date_created']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['start'] ) );

					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date_created']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['end'] ) );

					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date_created'] ) );
				$month = date( 'm', strtotime( $args['date_created'] ) );
				$day   = date( 'd', strtotime( $args['date_created'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		// Specific pend_date date
		if ( ! empty( $args['end_date'] ) ) {

			if ( is_array( $args['end_date'] ) ) {

				if ( ! empty( $args['end_date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['end_date']['start'] ) );

					$where .= " AND `end_date` >= '{$start}'";

				}

				if ( ! empty( $args['end_date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['end_date']['end'] ) );

					$where .= " AND `end_date` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['end_date'] ) );
				$month = date( 'm', strtotime( $args['end_date'] ) );
				$day   = date( 'd', strtotime( $args['end_date'] ) );

				$where .= " AND $year = YEAR ( end_date ) AND $month = MONTH ( end_date ) AND $day = DAY ( end_date )";
			}

		}

		// Specific paid date or in a paid date range
		if ( ! empty( $args['start_date'] ) ) {

			if ( is_array( $args['start_date'] ) ) {

				if ( ! empty( $args['start_date']['start_date'] ) ) {

					$start_date = date( 'Y-m-d H:i:s', strtotime( $args['start_date']['start_date'] ) );

					$where .= " AND `start_date` >= '{$start_date}'";

				}

				if ( ! empty( $args['start_date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['start_date']['end'] ) );

					$where .= " AND `start_date` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['start_date'] ) );
				$month = date( 'm', strtotime( $args['start_date'] ) );
				$day   = date( 'd', strtotime( $args['start_date'] ) );

				$where .= " AND $year = YEAR ( start_date ) AND $month = MONTH ( start_date ) AND $day = DAY ( start_date )";
			}

		}

		// Specific search query
		if ( ! empty( $args['search'] ) ) {
			$where .= " AND ( name LIKE '%%" . $args['search'] . "%%' OR code LIKE '%%" . $args['search'] . "%%' )";
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Count the total number of discounts in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 *
	 *      @type mixed array|string $type         Discount types (flat/percent).
	 *      @type mixed array|string $status       Discount status (active/inactive/expired).
	 *      @type mixed array|string $date_created Date created. Can pass a `start` and `end` key in the array to allow
	 *                                             for further filtering.
	 *      @type mixed array|string $end_date     Discount expiration date. Can pass a `start` and `end` key in the
	 *                                             array to allow for further filtering.
	 *      @type mixed array|string $start_date   Discount start date. Can pass a `start` and `end` key in the array to
	 *                                             allow for further filtering.
	 *      @type string             $search       Search parameters.
	 * }
	 *
	 * @return int $count Number of discounts in the database.
	 */
	public function count( $args = array() ) {
		global $wpdb;

		$where = $this->parse_where( $args );
		$sql   = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};";
		$count = $wpdb->get_var( $sql );

		return absint( $count );
	}

	/**
	 * Count the number of discounts in the table and group by status.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return object Counts by status.
	 */
	public function count_by_status() {
		global $wpdb;

		$counts = wp_cache_get( 'counts', $this->cache_group );

		if ( false === $counts ) {
			$sql = "
				SELECT status, COUNT( * ) AS num_discounts
				FROM {$this->table_name}
				GROUP BY status
			";
			$results = (array) $wpdb->get_results( $sql, ARRAY_A );

			$counts = array_fill_keys( array( 'active', 'inactive', 'expired' ), 0 );

			foreach ( $results as $row ) {
				$counts[ $row['status'] ] = $row['num_discounts'];
			}

			$counts = (object) $counts;
			wp_cache_set( 'counts', $counts, $this->cache_group );

			return $counts;
		} else {
			return $counts;
		}
	}

	/**
	 * Sets the last_changed cache key for discounts.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for discounts.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return string Value of the last_changed cache key for discounts.
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		return $last_changed;
	}
}
