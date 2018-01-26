<?php

/**
 * Orders: EDD_Order_Query class
 *
 * @package Plugins/EDD/Database/Queries/Orders
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying orders.
 *
 * @since 3.0.0
 *
 * @see EDD_Order_Query::__construct() for accepted arguments.
 */
class EDD_Order_Query {

	/**
	 * SQL for database query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $request;

	/**
	 * SQL query clauses.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var array
	 */
	protected $sql_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => '',
	);

	/**
	 * Date query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $date_created_query = false;

	/**
	 * Date query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $date_completed_query = false;

	/**
	 * Meta query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $meta_query = false;

	/**
	 * Query vars set by the user.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Default values for query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $query_var_defaults = array();

	/**
	 * List of orders located by the query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $orders = array();

	/**
	 * The amount of found orders for the current query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $found_orders = 0;

	/**
	 * The number of pages.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type int          $ID                   An order ID to only return that order. Default empty.
	 *     @type array        $order__in            Array of order IDs to include. Default empty.
	 *     @type array        $order__not_in        Array of order IDs to exclude. Default empty.
	 *     @type string       $number_id            An order number to only return that order. Default empty.
	 *     @type array        $number__in           Array of numbers to include. Default empty.
	 *     @type array        $number__not_in       Array of numbers to exclude. Default empty.
	 *     @type int          $user_id              A user ID to only return that object. Default empty.
	 *     @type array        $user__in             Array of user IDs to include. Default empty.
	 *     @type array        $user__not_in         Array of user IDs to exclude. Default empty.
	 *     @type string       $email                Limit results to those affiliated with a given object type. Default empty.
	 *     @type array        $email__in            Array of object types to include affiliated orders for. Default empty.
	 *     @type array        $email__not_in        Array of object types to exclude affiliated orders for. Default empty.
	 *     @type array        $date_created_query   Date query clauses to limit orders by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_completed_query Date query clauses to limit orders by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a order count (true) or array of order objects.
	 *                                              Default false.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of order IDs)
	 *                                              or empty (returns an array of complete order objects). Default empty.
	 *     @type int          $limit                Limit number of orders to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of orders to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Site status or array of statuses. Accepts 'id', 'user_id', 'customer_id',
	 *                                              'date_created', 'date_completed', 'user__in', 'customer__in'. Also accepts false,
	 *                                              an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order retrieved orders. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type bool         $all_day              Limit results to those that are all day.
	 *                                              Default empty.
	 *     @type string       $search               Search term(s) to retrieve matching orders for. Default empty.
	 *     @type array        $search_columns       Array of column names to be searched. Accepts 'email', 'date_created', 'date_completed'.
	 *                                              Default empty array.
	 *
	 *     @type bool         $update_order_cache   Whether to prime the cache for found orders. Default false.
	 * }
	 */
	public function __construct( $query = '' ) {
		$this->query_var_defaults = array(
			'fields'               => '',
			'id'                   => '',
			'order__in'            => '',
			'order__not_in'        => '',
			'number'               => '',
			'number__in'           => '',
			'number__not_in'       => '',
			'status'               => '',
			'status__in'           => '',
			'status__not_in'       => '',
			'user_id'              => '',
			'user__in'             => '',
			'user__not_in'         => '',
			'customer_id'          => '',
			'customer__in'         => '',
			'customer__not_in'     => '',
			'email'                => '',
			'email__in'            => '',
			'email__not_in'        => '',
			'gateway'              => '',
			'gateway__in'          => '',
			'gateway__not_in'      => '',
			'payment_key'          => '',
			'payment_key__in'      => '',
			'payment_key__not_in'  => '',

			'limit'                => 100,
			'offset'               => '',
			'orderby'              => 'id',
			'order'                => 'ASC',
			'search'               => '',
			'search_columns'       => array(),
			'count'                => false,
			'date_created_query'   => null, // See WP_Date_Query
			'date_completed_query' => null, // See WP_Date_Query
			'meta_query'           => null, // See WP_Meta_Query
			'no_found_rows'        => true,

			// Caching
			'update_order_cache'      => true,
			'update_order_meta_cache' => true,
		);

		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Return the global database interface
	 *
	 * @since 3.0.0
	 *
	 * @return wpdb
	 */
	public function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new stdClass();
	}

	/**
	 * Parses arguments passed to the order query with default query parameters.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @see EDD_Order_Query::__construct()
	 *
	 * @param string|array $query Array or string of EDD_Order_Query arguments. See EDD_Order_Query::__construct().
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		/**
		 * Fires after the order query vars have been parsed.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_Order_Query &$this The EDD_Order_Query instance (passed by reference).
		 */
		do_action_ref_array( 'parse_orders_query', array( &$this ) );
	}

	/**
	 * Sets up the WordPress query for retrieving orders.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of orders, or number of orders when 'count' is passed as a query var.
	 */
	public function query( $query ) {
		$this->query_vars = wp_parse_args( $query );

		return $this->get_orders();
	}

	/**
	 * Retrieves a list of orders matching the query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return array|int List of orders, or number of orders when 'count' is passed as a query var.
	 */
	public function get_orders() {
		$this->parse_query();

		/**
		 * Fires before object orders are retrieved.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_Order_Query &$this Current instance of EDD_Order_Query, passed by reference.
		 */
		do_action_ref_array( 'pre_get_orders', array( &$this ) );

		// $args can include anything. Only use the args defined in the query_var_defaults to compute the key.
		$key          = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
		$last_changed = wp_cache_get_last_changed( 'orders' );

		// Check the cache
		$cache_key   = "get_orders:{$key}:{$last_changed}";
		$cache_value = wp_cache_get( $cache_key, 'orders' );

		// No cache value
		if ( false === $cache_value ) {
			$order_ids = $this->get_order_ids();

			// Set the number of found orders (make sure it's not a count)
			if ( ! empty( $order_ids ) && is_array( $order_ids ) ) {
				$this->set_found_orders( $order_ids );
			}

			// Format the cached value
			$cache_value = array(
				'order_ids'    => $order_ids,
				'found_orders' => intval( $this->found_orders ),
			);

			// Add value to the cache
			wp_cache_add( $cache_key, $cache_value, 'orders' );

		// Value exists in cache
		} else {
			$order_ids          = $cache_value['order_ids'];
			$this->found_orders = intval( $cache_value['found_orders'] );
		}

		// Pagination
		if ( $this->found_orders && $this->query_vars['limit'] ) {
			$this->max_num_pages = ceil( $this->found_orders / $this->query_vars['limit'] );
		}

		// Return an int of the count
		if ( $this->query_vars['count'] ) {
			return intval( $order_ids );
		}

		// Cast to integers
		$order_ids = array_map( 'intval', $order_ids );

		// Prime order caches.
		if ( $this->query_vars['update_order_cache'] ) {
			_prime_order_caches( $order_ids, $this->query_vars['update_order_meta_cache'] );
		}

		// Return the IDs
		if ( 'ids' === $this->query_vars['fields'] ) {
			$this->orders = $order_ids;

			return $this->orders;
		}

		// Get order instances from IDs.
		$_orders = array_map( 'edd_get_order', $order_ids );

		/**
		 * Filters the object query results.
		 *
		 * @since 3.0.0
		 *
		 * @param array                  $results An array of orders.
		 * @param EDD_Order_Query &$this  Current instance of EDD_Order_Query, passed by reference.
		 */
		$_orders = apply_filters_ref_array( 'the_orders', array( $_orders, &$this ) );

		// Make sure orders are still order instances.
		$this->orders = array_map( 'edd_get_order', $_orders );

		// Return array of orders
		return $this->orders;
	}

	/**
	 * Used internally to get a list of order IDs matching the query vars.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @return int|array A single count of order IDs if a count query. An array of order IDs if a full query.
	 */
	protected function get_order_ids() {
		$order = $this->parse_order( $this->query_vars['order'] );

		// Disable ORDER BY with 'none', an empty array, or boolean false.
		if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) {
			$orderby = '';
		} elseif ( ! empty( $this->query_vars['orderby'] ) ) {
			$ordersby = is_array( $this->query_vars['orderby'] ) ?
				$this->query_vars['orderby'] :
				preg_split( '/[,\s]/', $this->query_vars['orderby'] );

			$orderby_array = array();
			foreach ( $ordersby as $_key => $_value ) {
				if ( ! $_value ) {
					continue;
				}

				if ( is_int( $_key ) ) {
					$_orderby = $_value;
					$_order   = $order;
				} else {
					$_orderby = $_key;
					$_order   = $_value;
				}

				$parsed = $this->parse_orderby( $_orderby );

				if ( empty( $parsed ) ) {
					continue;
				}

				if ( 'order__in' === $_orderby || 'user__in' === $_orderby || 'customer__in' === $_orderby ) {
					$orderby_array[] = $parsed;
					continue;
				}

				$orderby_array[] = $parsed . ' ' . $this->parse_order( $_order );
			}

			$orderby = implode( ', ', $orderby_array );
		} else {
			$orderby = "o.id {$order}";
		}

		$limit  = absint( $this->query_vars['limit']  );
		$offset = absint( $this->query_vars['offset'] );

		if ( ! empty( $limit ) ) {
			if ( $offset ) {
				$limits = 'LIMIT ' . $offset . ',' . $limit;
			} else {
				$limits = 'LIMIT ' . $limit;
			}
		}

		if ( $this->query_vars['count'] ) {
			$fields = 'COUNT(*)';
		} else {
			$fields = 'o.id';
		}

		/** Order *************************************************************/

		// Parse order IDs for an IN clause.
		$order_id = absint( $this->query_vars['id'] );
		if ( ! empty( $order_id ) ) {
			$this->sql_clauses['where']['id'] = $this->get_db()->prepare( 'o.id = %d', $order_id );
		}

		// Parse order IDs for an IN clause.
		if ( ! empty( $this->query_vars['order__in'] ) ) {
			if ( 1 === count( $this->query_vars['order__in'] ) ) {
				$this->sql_clauses['where']['id'] = $this->get_db()->prepare( 'e.id = %d', reset( $this->query_vars['order__in'] ) );
			} else {
				$this->sql_clauses['where']['order__in'] = "o.id IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['order__in'] ) ) . ' )';
			}
		}

		// Parse order IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['order__not_in'] ) ) {
			$this->sql_clauses['where']['order__not_in'] = "o.id NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['user__not_in'] ) ) . ' )';
		}

		/** User **************************************************************/

		$user_id = absint( $this->query_vars['user_id'] );
		if ( ! empty( $user_id ) ) {
			$this->sql_clauses['where']['user_id'] = $this->get_db()->prepare( 'e.user_id = %d', $user_id );
		}

		// Parse object IDs for an IN clause.
		if ( ! empty( $this->query_vars['user__in'] ) ) {
			if ( 1 === count( $this->query_vars['user__in'] ) ) {
				$this->sql_clauses['where']['user_id'] = $this->get_db()->prepare( 'e.user_id = %d', reset( $this->query_vars['user__in'] ) );
			} else {
				$this->sql_clauses['where']['user__in'] = "o.user_id IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['user__in'] ) ) . ' )';
			}
		}

		// Parse object IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['user__not_in'] ) ) {
			$this->sql_clauses['where']['user__not_in'] = "o.user_id NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['user__not_in'] ) ) . ' )';
		}

		/** Email *************************************************************/

		if ( ! empty( $this->query_vars['email'] ) ) {
			$this->sql_clauses['where']['email'] = $this->get_db()->prepare( 'e.email = %s', $this->query_vars['email'] );
		}

		// Parse order email for an IN clause.
		if ( is_array( $this->query_vars['email__in'] ) ) {
			if ( 1 === count( $this->query_vars['email__in'] ) ) {
				$this->sql_clauses['where']['email'] = $this->get_db()->prepare( 'o.email = %s', reset( $this->query_vars['email__in'] ) );
			} else {
				$this->sql_clauses['where']['email__in'] = "o.email IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars['email__in'] ) ) . "' )";
			}
		}

		// Parse order email for a NOT IN clause.
		if ( is_array( $this->query_vars['email__not_in'] ) ) {
			$this->sql_clauses['where']['email__not_in'] = "o.email NOT IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars['email__not_in'] ) ) . "' )";
		}

		/** Search ************************************************************/

		// Falsey search strings are ignored.
		if ( strlen( $this->query_vars['search'] ) ) {
			$search_columns = array();

			if ( $this->query_vars['search_columns'] ) {
				$search_columns = array_intersect( $this->query_vars['search_columns'], array( 'number', 'email', 'ip',	'payment_key', 'total' ) );
			}

			if ( empty( $search_columns ) ) {
				$search_columns = array( 'number', 'email', 'ip',	'payment_key', 'total' );
			}

			/**
			 * Filters the columns to search in a EDD_Order_Query search.
			 *
			 * The default columns include 'email' and 'path.
			 *
			 * @since 3.0.0
			 *
			 * @param array         $search_columns Array of column names to be searched.
			 * @param string        $search         Text being searched.
			 * @param EDD_Order_Query $this           The current EDD_Order_Query instance.
			 */
			$search_columns = apply_filters( 'order_search_columns', $search_columns, $this->query_vars['search'], $this );

			$this->sql_clauses['where']['search'] = $this->get_search_sql( $this->query_vars['search'], $search_columns );
		}

		/** Created ***********************************************************/

		$date_created_query = $this->query_vars['date_created_query'];
		if ( ! empty( $date_created_query ) && is_array( $date_created_query ) ) {
			$this->date_created_query = new WP_Date_Query( $date_created_query, 'o.date_created' );

			$this->sql_clauses['where']['date_created_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_created_query->get_sql() );
		}

		/** Completed *********************************************************/

		$date_completed_query = $this->query_vars['date_completed_query'];
		if ( ! empty( $date_completed_query ) && is_array( $date_completed_query ) ) {
			$this->date_completed_query = new WP_Date_Query( $date_completed_query, 'o.date_completed' );

			$this->sql_clauses['where']['date_completed_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_completed_query->get_sql() );
		}

		/** Meta **************************************************************/

		$meta_query = $this->query_vars['meta_query'];
		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {
			$this->meta_query = new WP_Meta_Query( $meta_query );
			$clauses          = $this->meta_query->get_sql( 'order', 'e', 'id', $this );
			$join             = $clauses['join'];

			$this->sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $clauses['where'] );
		} else {
			$join = '';
		}

		/** Combine ***********************************************************/

		$where = implode( ' AND ', $this->sql_clauses['where'] );

		$pieces = array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' );

		/**
		 * Filters the order query clauses.
		 *
		 * @since 3.0.0
		 *
		 * @param array $pieces A compacted array of order query clauses.
		 * @param EDD_Order_Query &$this Current instance of EDD_Order_Query, passed by reference.
		 */
		$clauses = apply_filters_ref_array( 'order_clauses', array( compact( $pieces ), &$this ) );

		// Default clauses
		$fields  = isset( $clauses['fields']  ) ? $clauses['fields']  : '';
		$join    = isset( $clauses['join']    ) ? $clauses['join']    : '';
		$where   = isset( $clauses['where']   ) ? $clauses['where']   : '';
		$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
		$limits  = isset( $clauses['limits']  ) ? $clauses['limits']  : '';
		$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';

		if ( ! empty( $where ) ) {
			$where = "WHERE {$where}";
		}

		if ( ! empty( $groupby ) ) {
			$groupby = "GROUP BY {$groupby}";
		}

		if ( ! empty( $orderby ) ) {
			$orderby = "ORDER BY {$orderby}";
		}

		$found_rows = empty( $this->query_vars['no_found_rows'] )
			? 'SQL_CALC_FOUND_ROWS'
			: '';

		// Put query into clauses array
		$this->sql_clauses['select']  = "SELECT {$found_rows} {$fields}";
		$this->sql_clauses['from']    = "FROM {$this->get_db()->orders} e {$join}";
		$this->sql_clauses['groupby'] = $groupby;
		$this->sql_clauses['orderby'] = $orderby;
		$this->sql_clauses['limits']  = $limits;

		// Assemble the query
		$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['groupby']} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";

		// Return count
		if ( $this->query_vars['count'] ) {
			return intval( $this->get_db()->get_var( $this->request ) );
		}

		// Get IDs
		$order_ids = $this->get_db()->get_col( $this->request );

		// Return parsed IDs
		return wp_parse_id_list( $order_ids );
	}

	/**
	 * Populates found_orders and max_num_pages properties for the current query
	 * if the limit clause was used.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param  array $order_ids Optional array of order IDs
	 */
	private function set_found_orders( $order_ids = array() ) {

		if ( ! empty( $this->query_vars['limit'] ) && ! empty( $this->query_vars['no_found_rows'] ) ) {
			/**
			 * Filters the query used to retrieve found order count.
			 *
			 * @since 3.0.0
			 *
			 * @param string         $found_orders_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param EDD_Order_Query $order_query        The `EDD_Order_Query` instance.
			 */
			$found_orders_query = apply_filters( 'found_orders_query', 'SELECT FOUND_ROWS()', $this );
			$this->found_orders = (int) $this->get_db()->get_var( $found_orders_query );
		} elseif ( ! empty( $order_ids ) ) {
			$this->found_orders = count( $order_ids );
		}
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @param string $string  Search string.
	 * @param array  $columns Columns to search.
	 * @return string Search SQL.
	 */
	protected function get_search_sql( $string = '', $columns = array() ) {

		if ( false !== strpos( $string, '*' ) ) {
			$like = '%' . implode( '%', array_map( array( $this->get_db(), 'esc_like' ), explode( '*', $string ) ) ) . '%';
		} else {
			$like = '%' . $this->get_db()->esc_like( $string ) . '%';
		}

		$searches = array();
		foreach ( $columns as $column ) {
			$searches[] = $this->get_db()->prepare( "{$column} LIKE %s", $like );
		}

		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/**
	 * Parses and sanitizes 'orderby' keys passed to the order query.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @param string $orderby Field for the orders to be ordered by.
	 * @return string|false Value to used in the ORDER clause. False otherwise.
	 */
	protected function parse_orderby( $orderby = 'id' ) {

		$parsed = false;

		switch ( $orderby ) {
			case 'order__in' :
				$order__in = implode( ',', array_map( 'absint', $this->query_vars['order__in'] ) );
				$parsed    = "FIELD( o.id, {$order__in} )";
				break;
			case 'user__in' :
				$user__in = implode( ',', array_map( 'absint', $this->query_vars['user__in'] ) );
				$parsed     = "FIELD( o.user_id, {$user__in} )";
				break;
			case 'customer__in' :
				$customer__in = implode( ',', array_map( 'absint', $this->query_vars['customer__in'] ) );
				$parsed       = "FIELD( o.customer_id, {$customer__in} )";
				break;
			case 'email__in' :
				$email__in = implode( ',', array_map( 'sanitize_email', $this->query_vars['email__in'] ) );
				$parsed    = "FIELD( o.email, {$email__in} )";
				break;

			case 'id' :
			case 'number' :
			case 'status' :
			case 'date_created' :
			case 'date_completed' :
			case 'user_id' :
			case 'customer_id' :
			case 'email' :
			case 'gateway' :
			case 'payment_key' :
			case 'subtotal' :
			case 'tax' :
			case 'discounts' :
			case 'total' :
			default :
				$parsed = "o.{$orderby}";
				break;
		}

		return $parsed;
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	protected function parse_order( $order  = '') {

		// Bail if malformed
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'ASC';
		}

		// Ascending or Descending
		return ( 'ASC' === strtoupper( $order ) )
			? 'ASC'
			: 'DESC';
	}
}
