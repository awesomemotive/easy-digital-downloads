<?php

/**
 * Orders: EDD_DB_Query class
 *
 * @package Plugins/EDD/Database/Queries
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EDD_DB_Query' ) ) :
/**
 * Base class used for querying custom database tables.
 *
 * @since 3.0.0
 *
 * @see EDD_DB_Query::__construct() for accepted arguments.
 */
class EDD_DB_Query extends EDD_DB_Base {

	/** Global Properties *****************************************************/

	/**
	 * Global prefix used for tables/hooks/cache-groups/etc...
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public $prefix = 'edd';

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_name = '';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "oi" for order items.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_alias = '';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_schema = 'EDD_DB_Schema';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name = '';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name_plural = '';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * I.E. `EDD_DB_Object` or `EDD_Customer`
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed
	 */
	public $item_shape = 'EDD_DB_Object';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * Use underscores between words. I.E. "some_items"
	 *
	 * Do not use colons: ":". These are reserved for internal use only.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $cache_group = '';

	/** Columns ***************************************************************/

	/**
	 * Primary database table column.
	 *
	 * This is set based on the column that has primary=true
	 *
	 * @since 3.0.0
	 * @access public
	 * @var WP_DB_Column
	 */
	protected $primary_column = false;

	/**
	 * Array of database column objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $columns = array();

	/** Clauses ***************************************************************/

	/**
	 * SQL query clauses.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var array
	 */
	protected $query_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => ''
	);

	/**
	 * Request clauses.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var array
	 */
	protected $request_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => '',
		'groupby' => '',
		'orderby' => '',
		'limits'  => ''
	);

	/**
	 * Meta query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Meta_Query
	 */
	protected $meta_query = false;

	/**
	 * Date query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	protected $date_query = false;

	/**
	 * Query vars set by the user.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Default values for query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $query_var_defaults = array();

	/** Items *****************************************************************/

	/**
	 * List of items located by the query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $items = array();

	/**
	 * The amount of found items for the current query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $found_items = 0;

	/**
	 * The number of pages.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * SQL for database query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $request = '';

	/** Methods ***************************************************************/

	/**
	 * Sets up the item query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of item query parameters.
	 *     Default empty.
	 *
	 *     @type string       $fields         Site fields to return. Accepts 'ids' (returns an array of item IDs)
	 *                                        or empty (returns an array of complete item objects). Default empty.
	 *     @type boolean      $count          Whether to return a item count (true) or array of item objects.
	 *                                        Default false.
	 *     @type integer      $number         Limit number of items to retrieve. Use 0 for no limit.
	 *                                        Default 100.
	 *     @type integer      $offset         Number of items to offset the query. Used to build LIMIT clause.
	 *                                        Default 0.
	 *     @type boolean      $no_found_rows  Whether to disable the `SQL_CALC_FOUND_ROWS` query.
	 *                                        Default true.
	 *     @type string|array $orderby        Accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                        Default 'id'.
	 *     @type string       $item           How to item retrieved items. Accepts 'ASC', 'DESC'.
	 *                                        Default 'DESC'.
	 *     @type string       $search         Search term(s) to retrieve matching items for.
	 *                                        Default empty.
	 *     @type array        $search_columns Array of column names to be searched. Accepts 'email', 'date_created', 'date_completed'.
	 *                                        Default empty array.
	 *     @type boolean      $update_cache   Whether to prime the cache for found items.
	 *                                        Default false.
	 * }
	 */
	public function __construct( $query = '' ) {

		// Setup
		$this->set_prefix();
		$this->set_columns();
		$this->set_primary_column();
		$this->set_query_var_defaults();

		$this->query( $query );
	}

	/**
	 * Parses arguments passed to the item query with default query parameters.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @see EDD_DB_Query::__construct()
	 *
	 * @param string|array $query Array or string of EDD_DB_Query arguments. See EDD_DB_Query::__construct().
	 */
	public function parse_query( $query = '' ) {

		// Fallback query vars
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_DB_Query &$this The EDD_DB_Query instance (passed by reference).
		 */
		do_action_ref_array( $this->apply_prefix( "parse_{$this->item_name_plural}_query" ), array( &$this ) );
	}

	/**
	 * Sets up the WordPress query for retrieving items.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function query( $query = array() ) {
		$this->query_vars = wp_parse_args( $query );

		return $this->get_items();
	}

	/**
	 * Retrieves a list of items matching the query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function get_items() {
		$this->parse_query();

		/**
		 * Fires before object items are retrieved.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_DB_Query &$this Current instance of EDD_DB_Query, passed by reference.
		 */
		do_action_ref_array( $this->apply_prefix( "pre_get_{$this->item_name_plural}" ), array( &$this ) );

		// $args can include anything. Only use the args defined in the query_var_defaults to compute the key.
		$slice        = wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) );
		$key          = md5( serialize( $slice ) );
		$last_changed = wp_cache_get_last_changed( $this->cache_group );

		// Check the cache
		$cache_key   = "get_{$this->item_name_plural}:{$key}:{$last_changed}";
		$cache_value = wp_cache_get( $cache_key, $this->cache_group );

		// No cache value
		if ( false === $cache_value ) {
			$item_ids = $this->get_item_ids();

			// Set the number of found items
			$this->set_found_items( $item_ids );

			// Format the cached value
			$cache_value = array(
				'item_ids'    => $item_ids,
				'found_items' => intval( $this->found_items ),
			);

			// Add value to the cache
			wp_cache_add( $cache_key, $cache_value, $this->cache_group );

		// Value exists in cache
		} else {
			$item_ids          = $cache_value['item_ids'];
			$this->found_items = intval( $cache_value['found_items'] );
		}

		// Pagination
		if ( ! empty( $this->found_items ) && ! empty( $this->query_vars['number'] ) ) {
			$this->max_num_pages = ceil( $this->found_items / $this->query_vars['number'] );
		}

		// Return an int of the count
		if ( ! empty( $this->query_vars['count'] ) ) {
			if ( empty( $this->query_vars['groupby'] ) ) {
				$item_ids = intval( $item_ids );
			}
		}

		// Set items from IDs
		$this->set_items( $item_ids );

		// Return array of items
		return $this->items;
	}

	/** Private Setters *******************************************************/

	/**
	 * Prefix table names, cache groups, and other things, to avoid conflicts
	 * with other plugins or themes that might be doing their own things.
	 *
	 * @since 3.0.0
	 *
	 * @return
	 */
	private function set_prefix() {

		// Bail if no prefix
		if ( empty( $this->prefix ) ) {
			return;
		}

		// Add prefixes to class properties
		$this->table_name  = $this->apply_prefix( $this->table_name       );
		$this->table_alias = $this->apply_prefix( $this->table_alias      );
		$this->cache_group = $this->apply_prefix( $this->cache_group, '-' );
	}

	/**
	 * Set columns objects
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_columns() {

		// Bail if no table schema
		if ( ! class_exists( $this->table_schema ) ) {
			return;
		}

		// Invoke a new table schema class
		$schema  = new $this->table_schema;
		$columns = ! empty( $schema->columns )
			? $schema->columns
			: $this->columns;

		// Default array
		$new_columns = array();

		// Loop through columns array
		foreach ( $columns as $column ) {
			if ( is_array( $column ) ) {
				$new_columns[] = new EDD_DB_Column( $column );
			} elseif ( $column instanceof EDD_DB_Column ) {
				$new_columns[] = $column;
			}
		}

		// Set columns
		$this->columns = $new_columns;
	}

	/**
	 * Set the primary column
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_primary_column() {
		$this->primary_column = $this->get_column_by( array(
			'primary' => true
		) );
	}

	/**
	 * Set default query vars based on columns
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_query_var_defaults() {

		// Default query variables
		$this->query_var_defaults = array(
			'fields'            => '',
			'number'            => 100,
			'offset'            => '',
			'orderby'           => 'id',
			'order'             => 'DESC',
			'groupby'           => '',
			'search'            => '',
			'search_columns'    => array(),
			'count'             => false,
			'meta_query'        => null, // See WP_Meta_Query
			'date_query'        => null, // See WP_Date_Query
			'no_found_rows'     => true,

			// Caching
			'update_cache'      => true,
			'update_meta_cache' => true
		);

		// Bail if no columns
		if ( empty( $this->columns ) ) {
			return;
		}

		// Direct column names
		$names = wp_list_pluck( $this->columns, 'name' );
		foreach ( $names as $name ) {
			$this->query_var_defaults[ $name ] = '';
		}

		// Possible ins
		$possible_ins = $this->get_columns( array( 'in' => true ), 'and', 'name' );
		foreach ( $possible_ins as $in ) {
			$key = "{$in}__in";
			$this->query_var_defaults[ $key ] = false;
		}

		// Possible not ins
		$possible_not_ins = $this->get_columns( array( 'not_in' => true ), 'and', 'name' );
		foreach ( $possible_not_ins as $in ) {
			$key = "{$in}__not_in";
			$this->query_var_defaults[ $key ] = false;
		}

		// Possible dates
		$possible_dates = $this->get_columns( array( 'date_query' => true ), 'and', 'name' );
		foreach ( $possible_dates as $date ) {
			$key = "{$date}_query";
			$this->query_var_defaults[ $key ] = false;
		}
	}

	/**
	 * Set the request clauses
	 *
	 * @since 3.0.0
	 * @access private
	 * @param array $clauses
	 */
	private function set_request_clauses( $clauses = array() ) {

		// Found rows
		$found_rows = empty( $this->query_vars['no_found_rows'] )
			? 'SQL_CALC_FOUND_ROWS'
			: '';

		// Fields
		$fields    = ! empty( $clauses['fields'] )
			? $clauses['fields']
			: '';

		// Join
		$join      = ! empty( $clauses['join'] )
			? $clauses['join']
			: '';

		// Where
		$where     = ! empty( $clauses['where'] )
			? "WHERE {$clauses['where']}"
			: '';

		// Group by
		$groupby   = ! empty( $clauses['groupby'] )
			? "GROUP BY {$clauses['groupby']}"
			: '';

		// Order by
		$orderby   = ! empty( $clauses['orderby'] )
			? "ORDER BY {$clauses['orderby']}"
			: '';

		// Limits
		$limits   = ! empty( $clauses['limits']  )
			? $clauses['limits']
			: '';

		// Select & From
		$table  = $this->get_table_name();
		$select = "SELECT {$found_rows} {$fields}";
		$from   = "FROM {$table} {$this->table_alias} {$join}";

		// Put query into clauses array
		$this->request_clauses['select']  = $select;
		$this->request_clauses['from']    = $from;
		$this->request_clauses['where']   = $where;
		$this->request_clauses['groupby'] = $groupby;
		$this->request_clauses['orderby'] = $orderby;
		$this->request_clauses['limits']  = $limits;
	}

	/**
	 * Set the request
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_request() {
		$this->request = "{$this->request_clauses['select']} {$this->request_clauses['from']} {$this->request_clauses['where']} {$this->request_clauses['groupby']} {$this->request_clauses['orderby']} {$this->request_clauses['limits']}";
	}

	/**
	 * Set items by mapping them through the single item callback.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param array $item_ids
	 */
	private function set_items( $item_ids = array() ) {

		// Bail if counting, to avoid shaping items
		if ( ! empty( $this->query_vars['count'] ) ) {
			$this->items = $item_ids;
			return;
		}

		// Cast to integers
		$item_ids = array_map( 'intval', $item_ids );

		// Prime item caches.
		$this->prime_item_caches( $item_ids );

		// Return the IDs
		if ( 'ids' === $this->query_vars['fields'] ) {
			$this->items = $item_ids;

			return $this->items;
		}

		// Get item instances from IDs.
		$_items = $this->shape_items( $item_ids );

		/**
		 * Filters the object query results.
		 *
		 * @since 3.0.0
		 *
		 * @param array        $results An array of items.
		 * @param EDD_DB_Query &$this   Current instance of EDD_DB_Query, passed by reference.
		 */
		$_items = apply_filters_ref_array( $this->apply_prefix( "the_{$this->item_name_plural}" ), array( $_items, &$this ) );

		// Make sure items are still item instances.
		$this->items = $this->shape_items( $_items );

		// Force lean up these items
		unset( $_items );
	}

	/**
	 * Populates found_items and max_num_pages properties for the current query
	 * if the limit clause was used.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param  array $item_ids Optional array of item IDs
	 */
	private function set_found_items( $item_ids = array() ) {

		// Items were found
		if ( ! empty( $item_ids ) ) {

			// Not a count query
			if ( is_array( $item_ids ) ) {
				if ( ! empty( $this->query_vars['number'] ) && ! empty( $this->query_vars['no_found_rows'] ) ) {
					/**
					 * Filters the query used to retrieve found item count.
					 *
					 * @since 3.0.0
					 *
					 * @param string $found_items_query SQL query. Default 'SELECT FOUND_ROWS()'.
					 * @param object $item_query        The object instance.
					 */
					$found_items_query = apply_filters( $this->apply_prefix( "found_{$this->item_name_plural}_query" ), 'SELECT FOUND_ROWS()', $this );
					$this->found_items = (int) $this->get_db()->get_var( $found_items_query );
				} elseif ( ! empty( $item_ids ) ) {
					$this->found_items = count( $item_ids );
				}

			// Count query
			} elseif ( ! empty( $this->query_vars['count'] ) ) {
				if ( is_numeric( $item_ids ) && ! empty( $this->query_vars['groupby'] ) ) {
					$this->found_items = intval( $item_ids );
				} else {
					$this->found_items = $item_ids;
				}
			}
		}
	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface
	 *
	 * @since 3.0.0
	 *
	 * @return wpdb
	 */
	private static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new stdClass();
	}

	/**
	 * Return the literal table name (with prefix) from $wpdb
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function get_table_name() {
		return $this->get_db()->{$this->table_name};
	}

	/**
	 * Return array of column names
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_column_names() {
		return array_flip( wp_list_pluck( $this->columns, 'name' ) );
	}

	/**
	 * Return the primary database column name
	 *
	 * @since 3.0.0
	 *
	 * @return string Default "id", Primary column name if not empty
	 */
	private function get_primary_column_name() {
		return ! empty( $this->primary_column->name )
			? $this->primary_column->name
			: 'id';
	}

	/**
	 * Get a column from an array of arguments
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function get_column_by( $args = array() ) {

		// Filter columns
		$filter = $this->get_columns( $args );

		// Return column or false
		return ! empty( $filter )
			? reset( $filter )
			: false;
	}

	/**
	 * Get columns from an array of arguments
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function get_columns( $args = array(), $operator = 'and', $field = false ) {

		// Filter columns
		$filter = wp_filter_object_list( $this->columns, $args, $operator, $field );

		// Return column or false
		return ! empty( $filter )
			? $filter
			: array();
	}

	/**
	 * Get a single database row by any column and value, skipping cache.
	 *
	 * @since 3.0.0
	 *
	 * @param string $column_name  Name of database column
	 * @param string $column_value Value to query for
	 *
	 * @return mixed False if empty/error, Object if successful
	 */
	private function get_item_raw( $column_name = '', $column_value = '' ) {

		// @todo get from EDD_DB_Column
		$pattern = $this->get_column_by( array( 'name' => $column_name ) )->is_numeric()
			? '%d'
			: '%s';

		// Query database for row
		$table  = $this->get_table_name();
		$select = $this->get_db()->prepare( "SELECT * FROM {$table} WHERE {$column_name} = {$pattern}", $column_value );
		$result = $this->get_db()->get_row( $select );

		// Bail if an error occurred
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Bail if no row exists
		if ( empty( $result ) ) {
			return false;
		}

		// Return row
		return $result;
	}

	/**
	 * Used internally to get a list of item IDs matching the query vars.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return int|array A single count of item IDs if a count query. An array of item IDs if a full query.
	 */
	private function get_item_ids() {

		// Setup primary column, and parse the where clause
		$this->parse_where();

		// Order & Order By
		$order   = $this->parse_order( $this->query_vars['order'] );
		$orderby = $this->get_order_by( $order );

		// Limit & Offset
		$limit   = absint( $this->query_vars['number'] );
		$offset  = absint( $this->query_vars['offset'] );

		// Limits
		if ( ! empty( $limit ) && ( '-1' != $limit ) ) {
			$limits = ! empty( $offset )
				? "LIMIT {$offset}, {$limit}"
				: "LIMIT {$limit}";
		} else {
			$limits = '';
		}

		// Where & Join
		$where = implode( ' AND ', $this->query_clauses['where'] );
		$join  = $this->query_clauses['join'];

		// Group by
		$groupby = $this->parse_groupby( $this->query_vars['groupby'] );

		// Fields
		$fields  = $this->parse_fields( $this->query_vars['fields'] );

		// Setup the query array (compact() is too opaque here)
		$query = array(
			'fields'  => $fields,
			'join'    => $join,
			'where'   => $where,
			'orderby' => $orderby,
			'limits'  => $limits,
			'groupby' => $groupby
		);

		/**
		 * Filters the item query clauses.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $pieces A compacted array of item query clauses.
		 * @param object &$this  Current instance passed by reference.
		 */
		$clauses = apply_filters_ref_array( $this->apply_prefix( 'item_clauses' ), array( $query, &$this ) );

		// Setup request
		$this->set_request_clauses( $clauses );
		$this->set_request();

		// Return count
		if ( ! empty( $this->query_vars['count'] ) ) {
			return empty( $this->query_vars['groupby'] )
				? $this->get_db()->get_var( $this->request )
				: $this->get_db()->get_results( $this->request, ARRAY_A );
		}

		// Get IDs
		$item_ids = $this->get_db()->get_col( $this->request );

		// Return parsed IDs
		return wp_parse_id_list( $item_ids );
	}

	/**
	 * Get the ORDERBY clause.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param string $order
	 * @return string
	 */
	private function get_order_by( $order = '' ) {

		// Default orderby primary column
		$orderby = "{$this->parse_orderby()} {$order}";

		// Disable ORDER BY if counting, or: 'none', an empty array, or false.
		if ( ! empty( $this->query_vars['count'] ) || in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) {
			$orderby = '';

		// Ordering by something, so figure it out
		} elseif ( ! empty( $this->query_vars['orderby'] ) ) {

			// Array of keys, or comma separated
			$ordersby = is_array( $this->query_vars['orderby'] )
				? $this->query_vars['orderby']
				: preg_split( '/[,\s]/', $this->query_vars['orderby'] );

			$orderby_array = array();
			$possible_ins  = $this->get_columns( array( 'in'       => true ), 'and', 'name' );
			$sortables     = $this->get_columns( array( 'sortable' => true ), 'and', 'name' );

			// Loop through possible order by's
			foreach ( $ordersby as $_key => $_value ) {

				// Skip if empty
				if ( empty( $_value ) ) {
					continue;
				}

				// Key is numeric
				if ( is_int( $_key ) ) {
					$_orderby = $_value;
					$_item    = $order;

				// Key is string
				} else {
					$_orderby = $_key;
					$_item    = $_value;
				}

				// Skip if not sortable
				if ( ! in_array( $_value, $sortables, true ) ) {
					continue;
				}

				// Parse orderby
				$parsed = $this->parse_orderby( $_orderby );

				// Skip if empty
				if ( empty( $parsed ) ) {
					continue;
				}

				// Set if __in
				if ( in_array( $_orderby, $possible_ins, true ) ) {
					$orderby_array[] = "{$parsed} {$order}";
					continue;
				}

				// Append parsed orderby to array
				$orderby_array[] = $parsed . ' ' . $this->parse_order( $_item );
			}

			// Only set if valid orderby
			if ( ! empty( $orderby_array ) ) {
				$orderby = implode( ', ', $orderby_array );
			}
		}

		// Return parsed orderby
		return $orderby;
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param string $string  Search string.
	 * @param array  $columns Columns to search.
	 * @return string Search SQL.
	 */
	private function get_search_sql( $string = '', $columns = array() ) {

		// Array or String
		$like = ( false !== strpos( $string, '*' ) )
			? '%' . implode( '%', array_map( array( $this->get_db(), 'esc_like' ), explode( '*', $string ) ) ) . '%'
			: '%' . $this->get_db()->esc_like( $string ) . '%';

		// Default array
		$searches = array();

		// Build search SQL
		foreach ( $columns as $column ) {
			$searches[] = $this->get_db()->prepare( "{$column} LIKE %s", $like );
		}

		// Return the clause
		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/** Private Parsers *******************************************************/

	/**
	 * Parse the where clauses for all known columns
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function parse_where() {

		// Defaults
		$where = $searchable = $date_query = array();
		$join  = '';
		$and   = '/^\s*AND\s*/';

		// Loop through columns
		foreach ( $this->columns as $column ) {

			// Maybe add name to searchable array
			if ( true === $column->searchable ) {
				$searchable[] = $column->name;
			}

			// Literal column comparison
			if ( ! empty( $this->query_vars[ $column->name ] ) ) {

				// Array (unprepared)
				if ( is_array( $this->query_vars[ $column->name ] ) ) {
					$where_id  = "'" . implode( "', '", $this->get_db()->_escape( $this->query_vars[ $column->name ] ) ) . "'";
					$statement = "{$this->table_alias}.{$column->name} IN ({$where_id})";

					// Add to where array
					$where[ $column->name ] = $statement;

				// Numeric/String (prepared)
				} else {

					// Numeric
					if ( $column->is_numeric() ) {
						$statement = "{$this->table_alias}.{$column->name} = %d";
						$where_id  = absint( $this->query_vars[ $column->name ] );

					// String
					} else {
						$statement = "{$this->table_alias}.{$column->name} = %s";
						$where_id  = $this->query_vars[ $column->name ];
					}

					// Add to where array
					$where[ $column->name ] = $this->get_db()->prepare( $statement, $where_id );
				}
			}

			// __in
			if ( true === $column->in ) {
				$where_id = "{$column->name}__in";

				// Parse item for an IN clause.
				if ( isset( $this->query_vars[ $where_id ] ) && is_array( $this->query_vars[ $where_id ] ) ) {

					// Convert single item arrays to literal column comparisons
					if ( 1 === count( $this->query_vars[ $where_id ] ) ) {
						$column_value = reset( $this->query_vars[ $where_id ] );
						$statement    = "{$this->table_alias}.{$column->name} = %s";

						$where[ $column->name ] = $this->get_db()->prepare( $statement, $column_value );

					// Implode
					} else {
						$where[ $where_id ] = "{$this->table_alias}.{$column->name} IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars[ $where_id ] ) ) . "' )";
					}
				}
			}

			// __not_in
			if ( true === $column->not_in ) {
				$where_id = "{$column->name}__not_in";

				// Parse item for a NOT IN clause.
				if ( isset( $this->query_vars[ $where_id ] ) && is_array( $this->query_vars[ $where_id ] ) ) {

					// Convert single item arrays to literal column comparisons
					if ( 1 === count( $this->query_vars[ $where_id ] ) ) {
						$column_value = reset( $this->query_vars[ $where_id ] );
						$statement    = "{$this->table_alias}.{$column->name} != %s";

						$where[ $column->name ] = $this->get_db()->prepare( $statement, $column_value );

					// Implode
					} else {
						$where[ $where_id ] = "{$this->table_alias}.{$column->name} NOT IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars[ $where_id ] ) ) . "' )";
					}
				}
			}

			// date_query
			if ( true === $column->date_query ) {
				$where_id    = "{$column->name}_query";
				$column_date = $this->query_vars[ $where_id ];

				// Parse item
				if ( ! empty( $column_date ) ) {

					// Default arguments
					$defaults = array(
						'column'    => "{$this->table_alias}.{$column->name}",
						'before'    => $column_date,
						'inclusive' => true
					);

					// Default date query
					if ( is_string( $column_date ) ) {
						$date_query[] = $defaults;
					} elseif ( is_array( $column_date ) ) {

						// Maybe auto-fill column
						if ( ! isset( $column_date['column'] ) ) {
							$column_date['column'] = $defaults['column'];
						}

						// Add clause to date query
						$date_query[] = $column_date;
					}
				}
			}
		}

		// Falsey search strings are ignored.
		if ( strlen( $this->query_vars['search'] ) ) {
			$search_columns = array();

			// Intersect against known searchable columns
			if ( ! empty( $this->query_vars['search_columns'] ) ) {
				$search_columns = array_intersect(
					$this->query_vars['search_columns'],
					$searchable
				);
			}

			// Default to all searchable columns
			if ( empty( $search_columns ) ) {
				$search_columns = $searchable;
			}

			/**
			 * Filters the columns to search in a EDD_DB_Query search.
			 *
			 * The default columns include 'email' and 'path.
			 *
			 * @since 3.0.0
			 *
			 * @param array        $search_columns Array of column names to be searched.
			 * @param string       $search         Text being searched.
			 * @param EDD_DB_Query $this           The current EDD_DB_Query instance.
			 */
			$search_columns = apply_filters( $this->apply_prefix( 'item_search_columns' ), $search_columns, $this->query_vars['search'], $this );

			// Add search query clause
			$where['search'] = $this->get_search_sql( $this->query_vars['search'], $search_columns );
		}

		// Maybe perform a meta-query
		$meta_query = $this->query_vars['meta_query'];
		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {
			$this->meta_query = new WP_Meta_Query( $meta_query );
			$table            = $this->apply_prefix( $this->item_name );
			$clauses          = $this->meta_query->get_sql( $table, $this->table_alias, $this->get_primary_column_name(), $this );

			// Not all objects have meta, so make sure this one exists
			if ( false !== $clauses ) {

				// Set join
				$join = $clauses['join'];

				// Remove " AND " from meta_query query where clause
				$where['meta_query'] = preg_replace( $and, '', $clauses['where'] );
			}
		}

		// Only do a date query with an array
		$date_query = ! empty( $date_query )
			? $date_query
			: $this->query_vars['date_query'];

		if ( ! empty( $date_query ) && is_array( $date_query ) ) {
			$this->date_query    = new WP_Date_Query( $date_query, '.' );
			$where['date_query'] = preg_replace( $and, '', $this->date_query->get_sql() );
		}

		// Set where and join clauses
		$this->query_clauses['where'] = $where;
		$this->query_clauses['join']  = $join;
	}

	/**
	 * Parse which fields to query for
	 *
	 * @since 3.0.0
	 *
	 * @param string  $fields
	 * @param boolean $alias
	 *
	 * @return string
	 */
	private function parse_fields( $fields = '', $alias = true ) {

		// No fields
		if ( empty( $fields ) ) {

			// Doing count?
			if ( ! empty( $this->query_vars['count'] ) ) {

				// Possible fields to group by
				$groupby_names = $this->parse_groupby( $this->query_vars['groupby'], false );
				$groupby_names = ! empty( $groupby_names )
					? "{$groupby_names}"
					: '';

				// Group by or total count
				$fields = ! empty( $groupby_names )
					? "{$groupby_names}, COUNT(*) as count"
					: 'COUNT(*)';

			// Not doing a count, so use primary column
			} else {
				$primary = $this->get_primary_column_name();
				$fields  = ( true === $alias )
					? "{$this->table_alias}.{$primary}"
					: $primary;
			}

		// Specific fields are being requested
		} else {
			$fields  = (array) $this->query_vars['fields'];
			$results = array();

			// Maybe alias keys
			foreach ( $fields as $field ) {
				$results[] = ( true === $alias )
					? "{$this->table_alias}.{$field}"
					: $field;
			}

			// Implode
			$fields = implode( ', ', $fields );
		}

		// Return string of fields
		return $fields;
	}

	/**
	 * Parses and sanitizes the 'groupby' keys passed into the item query
	 *
	 * @since 3.0.0
	 *
	 * @param string $groupby
	 * @return string
	 */
	private function parse_groupby( $groupby = '', $alias = true ) {

		// Bail if empty
		if ( empty( $groupby ) ) {
			return '';
		}

		// Sanitize keys
		$groupby = (array) array_map( 'sanitize_key', (array) $groupby );

		// Orderby is a literal column name
		$columns   = array_flip( $this->get_column_names() );
		$intersect = array_intersect( $columns, $groupby );

		// Bail if invalid column
		if ( empty( $intersect ) ) {
			return '';
		}

		// Default return value
		$retval = array();

		// Prepend table alias to key
		foreach ( $intersect as $key ) {
			$retval[] = ( true === $alias )
				? "{$this->table_alias}.{$key}"
				: $key;
		}

		// Separate sanitized columns
		return implode( ',', array_values( $retval ) );
	}

	/**
	 * Parses and sanitizes 'orderby' keys passed to the item query.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param string $orderby Field for the items to be ordered by.
	 * @return string|false Value to used in the ORDER clause. False otherwise.
	 */
	private function parse_orderby( $orderby = 'id' ) {

		// Default value
		$parsed = "{$this->table_alias}.{$this->get_primary_column_name()}";

		// __in
		if ( false !== strstr( $orderby, '__in' ) ) {
			$column_name = str_replace( '__in', '', $orderby );
			$column      = $this->get_column_by( array( 'name' => $column_name ) );
			$item_in     = $column->is_numeric()
				? implode( ',', array_map( 'absint', $this->query_vars[ $orderby ] ) )
				: implode( ',', $this->query_vars[ $orderby ] );

			$parsed = "FIELD( {$this->table_alias}.{$column->name}, {$item_in} )";

		// Specific column
		} else {

			// Orderby is a literal, sortable column name
			$sortables = $this->get_columns( array( 'sortable' => true ), 'and', 'name' );
			if ( in_array( $orderby, $sortables, true ) ) {
				$parsed = "{$this->table_alias}.{$orderby}";
			}
		}

		// Return parsed value
		return $parsed;
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	private function parse_order( $order  = '' ) {

		// Bail if malformed
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'DESC';
		}

		// Ascending or Descending
		return ( 'ASC' === strtoupper( $order ) )
			? 'ASC'
			: 'DESC';
	}

	/** Private Shapers *******************************************************/

	/**
	 * Maybe append the prefix to string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $string
	 * @param string $sep
	 *
	 * @return string
	 */
	private function apply_prefix( $string = '', $sep = '_' ) {
		return ! empty( $this->prefix )
			? "{$this->prefix}{$sep}{$string}"
			: $string;
	}

	/**
	 * Shape items into their most relevant objects.
	 *
	 * This will try to use item_shape, but will fallback to a private
	 * method for querying and caching items.
	 *
	 * @since 3.0.0
	 *
	 * @param array $items
	 * @return array
	 */
	private function shape_items( $items = array() ) {

		// Default var
		$results = array();

		// Use foreach because it's faster locally than array_map()
		foreach ( $items as $item ) {
			$results[] = $this->shape_item( $item );
		}

		// Return shaped results
		return $results;
	}

	/**
	 * Shape an item ID from an object, array, or numeric value
	 *
	 * @since 3.0.0
	 * @param  mixed $item
	 *
	 * @return int
	 */
	private function shape_item_id( $item = 0 ) {
		$retval  = 0;
		$primary = $this->get_primary_column_name();

		// Item ID
		if ( is_numeric( $item ) ) {
			$retval = $item;
		} elseif ( is_object( $item ) && isset( $item->{$primary} ) ) {
			$retval = $item->{$primary};
		} elseif ( is_array( $item ) && isset( $item[ $primary ] ) ) {
			$retval = $item[ $primary ];
		}

		// Return the item ID
		return absint( $retval );
	}

	/** Queries ***************************************************************/

	/**
	 * Get a single database row by the primary column ID, possibly from cache
	 *
	 * @since 3.0.0
	 *
	 * @param int $item_id
	 *
	 * @return mixed False if empty/error, Object if successful
	 */
	public function get_item( $item_id = 0 ) {

		// Bail if no item to get by
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) ) {
			return false;
		}

		// Get item by ID
		return $this->get_item_by( $this->get_primary_column_name(), $item_id );
	}

	/**
	 * Get a single database row by any column and value, possibly from cache.
	 *
	 * @since 3.0.0
	 *
	 * @param string $column_name  Name of database column
	 * @param string $column_value Value to query for
	 *
	 * @return mixed False if empty/error, Object if successful
	 */
	public function get_item_by( $column_name = '', $column_value = '' ) {

		// Default return value
		$retval = false;

		// Bail if no key or value
		if ( empty( $column_name ) || empty( $column_value ) ) {
			return $retval;
		}

		// Get column names
		$columns = $this->get_column_names();

		// Bail if column does not exist
		if ( ! isset( $columns[ $column_name ] ) ) {
			return $retval;
		}

		// Table
		$groups = $this->get_cache_groups();

		// Check cache
		if ( ! empty( $groups[ $column_name ] ) ) {
			$retval = wp_cache_get( $column_value, $groups[ $column_name ] );
		}

		// Item not cached
		if ( false === $retval ) {

			// Try to get item directly from DB
			$retval = $this->get_item_raw( $column_name, $column_value );

			// Bail because item does not exist
			if ( empty( $retval ) || is_wp_error( $retval ) ) {
				return false;
			}

			// Cache
			$this->update_item_cache( $retval );
		}

		// Return result
		return $this->shape_item( $retval );
	}

	/**
	 * Add an item to the database
	 *
	 * @since 3.0.0
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function add_item( $data = array() ) {
		$primary = $this->get_primary_column_name();
		$table   = $this->get_table_name();
		$columns = $this->get_column_names();

		// Bail if trying to update an existing item
		if ( isset( $data[ $primary ] ) ) {
			return $this->update_item( $data[ $primary ], $data );
		}

		// Cut out non-keys for meta
		$meta = array_diff_key( $data, $columns );
		$data = array_intersect_key( $data, $columns );

		// If `date_created` is empty, use the current time
		if ( ! isset( $data['date_created'] ) || empty( $data['date_created'] ) ) {
			$data['date_created'] = current_time( 'mysql' );
		}

		// Attempt to add
		$result = $this->get_db()->insert( $table, $data );

		// Bail if an error occurred
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Bail if no insert occurred
		if ( empty( $result ) ) {
			return false;
		}

		// Maybe save meta keys
		if ( ! empty( $meta ) ) {
			$this->save_extra_item_meta( $result, $meta );
		}

		// Return result
		return $this->get_db()->insert_id;
	}

	/**
	 * Update an item in the database
	 *
	 * @since 3.0.0
	 *
	 * @param int $item_id
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function update_item( $item_id = 0, $data = array() ) {

		// Bail if no item ID
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) ) {
			return false;
		}

		$where   = array( $this->get_primary_column_name() => $item_id );
		$table   = $this->get_table_name();
		$columns = $this->get_column_names();

		// Get item to update
		$item = $this->get_item( $item_id );

		// Item does not exist to update, so try to add instead
		if ( empty( $item ) ) {
			return $this->add_item( $data );
		}

		// Cast as an array for easier manipulation
		$item = (array) $item;

		// Splice new data into item, and cut out non-keys for meta
		$data = array_merge( $item, $data );
		$meta = array_diff_key( $data, $columns );
		$save = array_intersect_key( $data, $columns );

		// Bail if no change
		if ( (array) $save === (array) $item ) {
			return true;
		}

		// Never update the primary key value
		unset( $save[ $this->get_primary_column_name() ] );

		// Attempt to update
		$result = $this->get_db()->update( $table, $save, $where );

		// Bail if an error occurred
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Bail if no update occurred
		if ( empty( $result ) ) {
			return false;
		}

		// Maybe save meta keys
		if ( ! empty( $meta ) ) {
			$this->save_extra_item_meta( $item_id, $meta );
		}

		// Cast to stdClass for caching
		$save = (object) $save;

		// Prime the item cache
		$this->update_item_cache( $save );

		// Return result
		return $result;
	}

	/**
	 * Delete an item from the database
	 *
	 * @since 3.0.0
	 *
	 * @param int $item_id
	 *
	 * @return boolean
	 */
	public function delete_item( $item_id = 0 ) {

		// Bail if no item ID
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) ) {
			return false;
		}

		$where  = array( $this->get_primary_column_name() => $item_id );
		$table  = $this->get_table_name();
		$item   = $this->get_item( $item_id );
		$result = $this->get_db()->delete( $table, $where );

		// Maybe clean caches on successful delete
		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			$this->delete_all_item_meta( $item_id );
			$this->clean_item_cache( $item );
		}

		// Return result
		return $result;
	}

	/**
	 * Shape an item from the database into the type of object it always wanted
	 * to be when it grew up (EDD_Customer, EDD_Discount, EDD_Payment, etc...)
	 *
	 * @since 3.0.0
	 *
	 * @param mixed ID of item, or row from database
	 *
	 * @return mixed False on error, Object of single-object class type on success
	 */
	private function shape_item( $item = 0 ) {

		// Callback exists
		if ( empty( $this->item_shape ) || ! class_exists( $this->item_shape ) ) {
			$this->item_shape = 'EDD_DB_Object';
		}

		// Bail early if item is already an object of the correct shape
		if ( $item instanceof $this->item_shape ) {
			return $item;
		}

		// Get the item from an ID
		if ( is_numeric( $item ) ) {
			$item = $this->get_item( $item );
		}

		// Return the newly shaped item
		return new $this->item_shape( $item );
	}

	/** Meta ******************************************************************/

	/**
	 * Add meta data to an item
	 *
	 * @since 3.0.0
	 *
	 * @param int    $item_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $unique
	 *
	 * @return mixed
	 */
	protected function add_item_meta( $item_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {

		// Bail if no meta was returned
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) || empty( $meta_key ) ) {
			return false;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return false;
		}

		// Return results of get meta data
		return add_metadata( $table, $item_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Get meta data for an item
	 *
	 * @since 3.0.0
	 *
	 * @param int     $item_id
	 * @param string  $meta_key
	 * @param boolean $single
	 *
	 * @return mixed
	 */
	protected function get_item_meta( $item_id = 0, $meta_key = '', $single = false ) {

		// Bail if no meta was returned
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) || empty( $meta_key ) ) {
			return false;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return false;
		}

		// Return results of get meta data
		return get_metadata( $table, $item_id, $meta_key, $single );
	}

	/**
	 * Update meta data for an item
	 *
	 * @since 3.0.0
	 *
	 * @param int    $item_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $prev_value
	 *
	 * @return mixed
	 */
	protected function update_item_meta( $item_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {

		// Bail if no meta was returned
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) || empty( $meta_key ) ) {
			return false;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return false;
		}

		// Return results of get meta data
		return update_metadata( $table, $item_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete meta data for an item
	 *
	 * @since 3.0.0
	 *
	 * @param int    $item_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $delete_all
	 *
	 * @return mixed
	 */
	protected function delete_item_meta( $item_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {

		// Bail if no meta was returned
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) || empty( $meta_key ) ) {
			return false;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return false;
		}

		// Return results of get meta data
		return delete_metadata( $table, $item_id, $meta_key, $meta_value, $delete_all );
	}

	/**
	 * Maybe update meta values on item update/save
	 *
	 * @since 3.0.0
	 *
	 * @param array $meta
	 */
	private function save_extra_item_meta( $item_id = 0, $meta = array() ) {

		// Bail if there is no bulk meta to save
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) || empty( $meta ) ) {
			return;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return;
		}

		// Save or delete meta data
		foreach ( $meta as $key => $value ) {
			! empty( $value )
				? $this->update_item_meta( $item_id, $key, $value )
				: $this->delete_item_meta( $item_id, $key );
		}
	}

	/**
	 * Delete all meta data for an item
	 *
	 * @since 3.0.0
	 *
	 * @param int $item_id
	 *
	 * @return type
	 */
	private function delete_all_item_meta( $item_id = 0 ) {

		// Bail if no meta was returned
		$item_id = $this->shape_item_id( $item_id );
		if ( empty( $item_id ) ) {
			return;
		}

		// Get meta table name
		$table = $this->get_meta_table_name();

		// Bail if no meta table exists
		if ( empty( $table ) ) {
			return;
		}

		// Add the site prefix to meta table name
		$table = "{$this->get_db()->prefix}{$table}";

		// Get meta IDs
		$sql      = "SELECT meta_id FROM {$table} WHERE {$this->get_primary_column_name()} = %d";
		$prepared = $this->get_db()->prepare( $sql, $item_id );
		$meta_ids = $this->get_db()->get_col( $prepared );

		// Delete all meta data for this item ID
		foreach ( $meta_ids as $mid ) {
			delete_metadata_by_mid( $table, $mid );
		}
	}

	/**
	 * Return meta table
	 *
	 * @since 3.0.0
	 *
	 * @return mixed Table name if exists, False if not
	 */
	private function get_meta_table_name() {

		// Maybe apply table prefix
		$table = $this->apply_prefix( $this->item_name );

		// Return table if exists, or false if not
		return _get_meta_table( $table )
			? $table
			: false;
	}

	/** Cache *****************************************************************/

	/**
	 * Get array of which database columns have uniquely cached groups
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_cache_groups() {

		// Return value
		$cache_groups = array();

		// Get cache groups
		$groups = $this->get_columns( array( 'cache_key' => true ), 'and', 'name' );

		// Setup return values
		foreach ( $groups as $name ) {
			$cache_groups[ $name ] = "{$this->cache_group}-by-{$name}";
		}

		// Return cache groups array
		return $cache_groups;
	}

	/**
	 * Maybe prime item & item-meta caches by querying 1 time for all un-cached
	 * items.
	 *
	 * Accepts a single ID, or an array of IDs.
	 *
	 * The reason this accepts only IDs is because it gets called immediately
	 * after an item is inserted in the database, but before items have been
	 * "shaped" into proper objects, so object properties may not be set yet.
	 *
	 * @since 3.0.0
	 *
	 * @param array $item_ids
	 *
	 * @return boolean False if empty
	 */
	private function prime_item_caches( $item_ids = array() ) {

		// Bail if no items to cache
		if ( empty( $item_ids ) ) {
			return false;
		}

		// Accepts single values, so cast to array
		$item_ids = (array) $item_ids;

		// Update item caches
		if ( empty( $this->query_vars['update_cache'] ) ) {

			// Look for non-cached IDs
			$ids = _get_non_cached_ids( $item_ids, $this->cache_group );

			// Bail if IDs are cached
			if ( empty( $ids ) ) {
				return false;
			}

			// Query
			$table   = $this->get_table_name();
			$primary = $this->get_primary_column_name();
			$query   = "SELECT * FROM {$table} WHERE {$primary} IN (%s)";
			$ids     = join( ',', array_map( 'absint', $ids ) );
			$prepare = sprintf( $query , $ids );
			$results = $this->get_db()->get_results( $prepare );

			// Update item caches
			$this->update_item_cache( $results );
		}

		// Update meta data caches
		if ( ! empty( $this->query_vars['update_meta_cache'] ) ) {
			$singular = rtrim( $this->table_name, 's' ); // sic
			update_meta_cache( $singular, $item_ids );
		}
	}

	/**
	 * Update the cache for an item. Does not update item-meta cache.
	 *
	 * Accepts a single object, or an array of objects.
	 *
	 * The reason this does not accept ID's is because this gets called
	 * after an item is already updated in the database, so we want to avoid
	 * querying for it again. It's just safer this way.
	 *
	 * @since 3.0.0
	 *
	 * @param array $items
	 */
	private function update_item_cache( $items = array() ) {

		// Bail if no items to cache
		if ( empty( $items ) ) {
			return false;
		}

		// Make sure items is an array
		$items  = (array) $items;
		$groups = $this->get_cache_groups();

		// Loop through all items and cache them
		foreach ( $items as $item ) {
			if ( is_object( $item ) ) {
				foreach ( $groups as $key => $group ) {
					wp_cache_set( $item->{$key}, $item, $group );
				}
			}
		}

		// Update last changed
		$this->update_last_changed();
	}

	/**
	 * Clean the cache for an item. Does not clean item-meta.
	 *
	 * Accepts a single object, or an array of objects.
	 *
	 * The reason this does not accept ID's is because this gets called
	 * after an item is already deleted from the database, so it cannot be
	 * queried and may not exist in the cache. It's just safer this way.
	 *
	 * @since 3.0.0
	 *
	 * @param array $items
	 *
	 * @return boolean
	 */
	private function clean_item_cache( $items = array() ) {

		// Bail if no items to cache
		if ( empty( $items ) ) {
			return false;
		}

		// Make sure items is an array
		$items = (array) $items;
		$groups = $this->get_cache_groups();

		// Loop through all items and cache them
		foreach ( $items as $item ) {
			if ( is_object( $item ) ) {
				foreach ( $groups as $key => $group ) {
					wp_cache_delete( $item->{$key}, $group );
				}
			}
		}

		// Update last changed
		$this->update_last_changed();
	}

	/**
	 * Update the last_changed key for the cache group
	 *
	 * @since 3.0.0
	 */
	private function update_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/** Back Compat ***********************************************************/

	/**
	 * Return a single column from an item, or null
	 *
	 * @since 3.0.0
	 *
	 * @param string $name
	 * @param int    $item_id
	 *
	 * @return mixed
	 */
	public function get_column( $name = '', $item_id = 0 ) {
		$item = $this->get_item( $item_id );

		// Return a single column from an item, or null
		return isset( $item->{$name} )
			? $item->{$name}
			: null;
	}

	public function insert( $data = array() ) {
		return $this->add_item( $data );
	}

	public function add( $data = array() ) {
		return $this->add_item( $data );
	}

	public function update( $item_id, $data = array(), $where = '' ) {
		return $this->update_item( $item_id, $data );
	}

	public function delete( $item_id = 0 ) {
		return $this->delete_item( $item_id );
	}

	public function add_meta( $item_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
		return $this->add_item_meta( $item_id, $meta_key, $meta_value, $unique );
	}

    public function __call( $method = '', $args = array() ) {
		switch ( $method ) {
			case 'add_meta' :
				die;
		}
    }
}
endif;
