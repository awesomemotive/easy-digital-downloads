<?php
/**
 * Log DB class
 *
 * This class is for interacting with the log table.
 *
 * @package     EDD
 * @subpackage  Classes/Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_DB_Logs Class.
 *
 * @since 3.0
 */
class EDD_DB_Logs extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @since  3.0
	 * @access public
	 * @var    string
	 */
	public $cache_group = 'logs';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_logs';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	/**
	 * Retrieve table columns and data types.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return array Array of table columns and data types.
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'object_id'    => '%d',
			'object_type'  => '%s',
			'type'         => '%s',
			'title'        => '%s',
			'message'      => '%s',
			'date_created' => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return array Array of the default values for each column in the table.
	 */
	public function get_column_defaults() {
		return array(
			'id'           => 0,
			'object_id'    => 0,
			'object_type'  => '',
			'type'         => '',
			'title'        => '',
			'message'      => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Insert a new log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::insert()
	 *
	 * @param array $data {
	 *      @type string $object_id    ID of the object the log is for.
	 *      @type string $object_type  Type of the object the log is for.
	 *      @type string $type         Log type.
	 *      @type string $title        Log title.
	 *      @type string $message      Log message.
	 *      @type float  $date_created Date log was created.
	 * }
	 *
	 * @return int ID of the inserted log.
	 */
	public function insert( $data, $type = '' ) {
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update a log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::update()
	 *
	 * @param int   $row_id Log ID.
	 * @param array $data {
	 *     Optional. Array of data to update the log with. Default empty.
	 *
	 *     @type int    $object_id    ID of the object the log is for.
	 *     @type string $object_type  Type of the object the log is for.
	 *     @type string $type         Log type.
	 *     @type string $title        Log title.
	 *     @type string $message      Log message.
	 *     @type float  $date_created Date log was created.
	 * }
	 * @param mixed string|array $where Where clause to filter update.
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Delete a log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param int $row_id ID of the log to delete.
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
	 * Retrieve log from the database
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *     Optional. Array of log query parameters. Default empty.
	 *
	 *     @type int    $number       Number of logs to retrieve. Default 20.
	 *     @type int    $offset       Number of logs to offset the query. Used to build LIMIT clause. Default 0.
	 *     @type string $search       Search term(s) to retrieve matching logs for. Default empty.
	 *     @type string $orderby      Order by a specific column. Default 'id'.
	 *     @type string $order        How to order retrieved logs. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type int    $object_id    ID of the object the log is for. Default empty.
	 *     @type string $object_type  Type of the object the log is for. Default empty.
	 *     @type string $type         Log type. Default empty.
	 *     @type string $title        Log title. Default empty.
	 *     @type string $message      Log message. Default empty.
	 *     @type array  $date_created Date query clauses to limit the logs by. Default null.
	 * }
	 *
	 * @return array $logs Array of `EDD_Log` objects.
	 */
	public function get_logs( $args = array() ) {
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

		// Account for 'log_type' in legacy args
		if ( isset( $args['log_type'] ) ) {
			$args['type'] = $args['log_type'];
			unset( $args['log_type'] );
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

		$cache_key = md5( 'edd_logs_' . serialize( $args ) );

		$logs = wp_cache_get( $cache_key, $this->cache_group );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		$join = '';

		// Join meta tables if meta query exists.
		if ( array_key_exists( 'meta_query', $args ) && ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
			$meta_query = new WP_Meta_Query( $args['meta_query'] );
			$clauses = $meta_query->get_sql( 'edd_log', EDD()->logs->table_name, 'id', $this );
			$join = $clauses['join'];
		}

		if ( false === $logs ) {
			$logs = $wpdb->get_col( $wpdb->prepare(
				"
					SELECT id
					FROM {$this->table_name}
					{$join}
					{$where}
					ORDER BY {$args['orderby']} {$args['order']}
					LIMIT %d,%d;
				", absint( $args['offset'] ), absint( $args['number'] ) ), 0 );

			if ( ! empty( $logs ) ) {
				foreach ( $logs as $key => $log ) {
					$logs[ $key ] = new EDD\Logs\Log( $log );
				}

				wp_cache_set( $cache_key, $logs, $this->cache_group, 3600 );
			}
		}

		return $logs;
	}

	/**
	 * Parse `WHERE` clause for the SQL query.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 * }
	 *
	 * @return string `WHERE` clause for the SQL query.
	 */
	private function parse_where( $args ) {
		$where = '';
		$table_name = EDD()->logs->table_name;

		// Build meta query.
		if ( array_key_exists( 'meta_query', $args ) && ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
			$meta_query = new WP_Meta_Query( $args['meta_query'] );
			$clauses = $meta_query->get_sql( 'edd_log', EDD()->logs->table_name, 'id', $this );
			$clauses['where'] = preg_replace( '/\'_edd_log_/', '\'', $clauses['where'] );
			$where .= $clauses['where'];
		}

		// Build date query.
		if ( array_key_exists( 'date_query', $args ) && ! empty( $args['date_query'] ) && is_array( $args['date_query'] ) ) {
			$date_query = new WP_Date_Query( $args['date_query'], EDD()->logs->table_name . '.date_created' );
			$where .= $date_query->get_sql();
		}

		// Legacy post parent argument.
		if ( array_key_exists( 'post_parent', $args ) && ! empty( $args['post_parent'] ) ) {
			if ( false !== $args['post_parent'] ) {
				$where .= " AND {$table_name}.object_id = " . absint( $args['post_parent'] );
			}
		}

		// Object ID.
		if ( array_key_exists( 'object_id', $args ) && ! empty( $args['object_id'] ) ) {
			if ( false !== $args['object_id'] ) {
				$where .= " AND {$table_name}.object_id = " . absint( $args['object_id'] );
			}
		}

		// Object type(s).
		if ( array_key_exists( 'object_type', $args ) && ! empty( $args['object_type'] ) ) {
			// Allow for an array of types to be passed
			if ( is_array( $args['object_type'] ) ) {
				$types = implode( "','", array_map( 'sanitize_text_field', $args['object_type'] ) );
			} else {
				$types = sanitize_text_field( $args['object_type'] );
			}

			$where .= " AND {$table_name}.object_type IN (' {$types} ')";
		}

		// Log type(s).
		if ( array_key_exists( 'type', $args ) && ! empty( $args['type'] ) ) {
			// Allow for an array of types to be passed
			if ( is_array( $args['type'] ) ) {
				$types = implode( "','", array_map( 'sanitize_text_field', $args['type'] ) );
			} else {
				$types = sanitize_text_field( $args['type'] );
			}

			$where .= " AND {$table_name}.type IN ( '{$types}' )";
		}

		// Log title.
		if ( array_key_exists( 'title', $args ) && ! empty( $args['title'] ) ) {
			$where .= " AND {$table_name}.title = " . sanitize_text_field( $args['title'] );
		}

		// Log message.
		if ( array_key_exists( 'message', $args ) && ! empty( $args['message'] ) ) {
			$where .= " AND {$table_name}.message = " . sanitize_text_field( $args['message'] );
		}

		// Created for a specific date or in a date range.
		if ( array_key_exists( 'date_created', $args ) && ! empty( $args['date_created'] ) ) {
			if ( is_array( $args['date_created'] ) ) {
				if ( ! empty( $args['date_created']['start'] ) ) {
					$start = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['start'] ) );
					$where .= " AND {$table_name}.date_created >= '{$start}'";
				}
				if ( ! empty( $args['date_created']['end'] ) ) {
					$end = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['end'] ) );
					$where .= " AND {$table_name}.date_created <= '{$end}'";
				}
			} else {
				$year  = date( 'Y', strtotime( $args['date_created'] ) );
				$month = date( 'm', strtotime( $args['date_created'] ) );
				$day   = date( 'd', strtotime( $args['date_created'] ) );
				$where .= " AND $year = YEAR ( {$table_name}.date_created ) AND $month = MONTH ( {$table_name}.date_created ) AND $day = DAY ( {$table_name}.date_created )";
			}
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Count the total number of logs in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
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
	 * Count the total number of logs in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array $count Number of logs in the database based on the type.
	 */
	public function counts_by_type() {
		global $wpdb;

		$counts = wp_cache_get( 'counts', $this->cache_group );

		if ( false === $counts ) {
			$sql = "SELECT type, COUNT( * ) AS num_logs
					FROM {$this->table_name}
					GROUP BY type";

			$results = (array) $wpdb->get_results( $sql, ARRAY_A );

			foreach ( $results as $row ) {
				$counts[ $row['type'] ] = $row['num_logs'];
			}

			$counts = (object) $counts;

			wp_cache_set( 'counts', $counts, $this->cache_group );

			return $counts;
		} else {
			return $counts;
		}
	}

	/**
	 * Sets the last_changed cache key for logs.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for discounts.
	 *
	 * @since 3.0
	 * @access public
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