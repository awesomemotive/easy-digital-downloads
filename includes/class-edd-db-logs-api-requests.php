<?php
/**
 * API Requests Logs DB class.
 *
 * This class is for interacting with the API request logs table.
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
 * EDD_DB_API_Request_Logs Class.
 *
 * @since 3.0
 */
class EDD_DB_Logs_API_Requests extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @since  3.0
	 * @access public
	 * @var    string
	 */
	public $cache_group = 'logs_api_requests';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_logs_api_requests';
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
			'user_id'      => '%d',
			'api_key'      => '%s',
			'token'        => '%s',
			'version'      => '%s',
			'request'      => '%s',
			'error'        => '%s',
			'ip'           => '%s',
			'time'         => '%f',
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
			'user_id'      => 0,
			'api_key'      => 'public',
			'token'        => '',
			'version'      => '',
			'request'      => '',
			'error'        => '',
			'ip'           => '',
			'time'         => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Insert a new API request log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::insert()
	 *
	 * @param array $data {
	 *     API request log attributes.
	 *
	 *     @type int    $user_id      ID of the user making the API request. Default 0.
	 *     @type string $api_key      API key being used to make the request. Default public.
	 *     @type string $token        Token being used in conjunction with the API key to make the request.
	 *                                Default public.
	 *     @type string $version      API version being used. Default empty.
	 *     @type string $request      Request path with parameters. Default empty.
	 *     @type string $error        Errors with the API request. Default empty.
	 *     @type string $ip           IP address of the client making the API request. Default empty.
	 *     @type float  $time         Time taken for the request to execute. Default empty.
	 *     @type string $date_created Optional. Time and date of the API request. If left empty, it will automatically
	 *                                be set upon insertion.
	 * }
	 * @param string $type Data type to insert (forced to 'api_request_log').
	 *
	 * @return int ID of the inserted log.
	 */
	public function insert( $data, $type = 'api_request_log' ) {
		// Forced to ensure the correct actions run.
		$type = 'api_request_log';

		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update an API request log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::update()
	 *
	 * @param int   $row_id API request log ID.
	 * @param array $data {
	 *     API request log attributes.
	 *
	 *     @type int    $user_id      ID of the user making the API request. Default 0.
	 *     @type string $api_key      API key being used to make the request. Default public.
	 *     @type string $token        Token being used in conjunction with the API key to make the request.
	 *                                Default public.
	 *     @type string $version      API version being used. Default empty.
	 *     @type string $request      Request path with parameters. Default empty.
	 *     @type string $error        Errors with the API request. Default empty.
	 *     @type string $ip           IP address of the client making the API request. Default empty.
	 *     @type float  $time         Time taken for the request to execute. Default empty.
	 *     @type string $date_created Optional. Time and date of the API request. If left empty, it will automatically
	 *                                be set upon insertion.
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
	 * @param int $row_id ID of the API request log to delete.
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
	 * Retrieve an API request log from the database.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *     Optional. Array of log query parameters. Default empty.
	 *
	 *     @type int          $number       Number of logs to retrieve. Default 20.
	 *     @type int          $offset       Number of logs to offset the query. Used to build LIMIT clause. Default 0.
	 *     @type string       $search       Search term(s) to retrieve matching logs for. Default empty.
	 *     @type string       $orderby      Order by a specific column. Default 'id'.
	 *     @type string       $order        How to order retrieved logs. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type array|int    $user_id      ID of the user making the API request. Default empty.
	 *     @type array|string $api_key      API key being used to make the request. Default empty.
	 *     @type array|string $token        Token being used in conjunction with the API key to make the request.
	 *                                      Default empty.
	 *     @type array|string $version      API version being used. Default empty.
	 *     @type array|string $request      Request path with parameters. Default empty.
	 *     @type array|string $error        Errors with the API request. Default empty.
	 *     @type array|string $ip           IP address of the client making the API request. Default empty.
	 *     @type array|float  $time         Time taken for the request to execute. Default empty.
	 *     @type array|string $date_created Date query clauses to limit the logs by. Default null.
	 *     @type array        $date_query   WP_Date_Query clauses.
	 * }
	 *
	 * @return array $logs Array of EDD\Logs\API_Request_Log objects.
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

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$args['search'] = $wpdb->esc_like( $args['search'] );
		}

		$where = $this->parse_where( $args );

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'edd_logs_api_requests_' . serialize( $args ) );

		$logs = wp_cache_get( $cache_key, $this->cache_group );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( false === $logs ) {
			$logs = $wpdb->get_col( $wpdb->prepare(
				"
					SELECT id
					FROM {$this->table_name}
					{$where}
					ORDER BY {$args['orderby']} {$args['order']}
					LIMIT %d,%d;
				", absint( $args['offset'] ), absint( $args['number'] ) ), 0 );

			if ( ! empty( $logs ) ) {
				foreach ( $logs as $key => $log ) {
					$logs[ $key ] = new EDD\Logs\API_Request_Log( $log );
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
		global $wpdb;

		$where = '';
		$table_name = EDD()->api_request_logs->table_name;

		// Adapted from WP_Meta_Query
		if ( isset( $args['meta_query'] ) ) {
			foreach ( $args['meta_query'] as $key => $clause ) {
				if ( 'relation' === $key ) {
					$relation = $args['meta_query']['relation'];
				} elseif ( is_array( $clause ) ) {
					if ( isset( $clause['compare'] ) ) {
						$clause['compare'] = strtoupper( $clause['compare'] );
					} else {
						$clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] ) ? 'IN' : '=';
					}

					if ( array_key_exists( 'key', $clause ) ) {
						// Convert $key to new correct column names
						switch ( $clause['key'] ) {
							case '_edd_log_request_ip':
							case '_edd_log_user':
							case '_edd_log_key':
							case '_edd_log_token':
							case '_edd_log_time':
							case '_edd_log_version':
								$clause['key'] = str_replace( '_edd_log_', '', $clause['key'] );

								if ( 'request_ip' === $clause['key'] ) {
									$clause['key'] = 'ip';
								}

								if ( 'key' === $clause['key'] ) {
									$clause['key'] = 'api_key';
								}

								if ( 'user' === $clause['key'] ) {
									$clause['key'] = 'user_id';
								}
								break;
						}

						$where .= ' AND ' . trim( $clause['key'] ) . ' = ';
					}

					if ( array_key_exists( 'value', $clause ) ) {
						$meta_value = $clause['value'];

						if ( in_array( $clause['compare'], array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
							if ( ! is_array( $meta_value ) ) {
								$meta_value = preg_split( '/[,\s]+/', $meta_value );
							}
						} else {
							$meta_value = trim( $meta_value );
						}

						switch ( $clause['compare'] ) {
							case 'IN':
							case 'NOT IN':
								$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
								$where .= $wpdb->prepare( $meta_compare_string, $meta_value );
								break;

							case 'BETWEEN':
							case 'NOT BETWEEN':
								$meta_value = array_slice( $meta_value, 0, 2 );
								$where .= $wpdb->prepare( '%s AND %s', $meta_value );
								break;

							case 'LIKE':
							case 'NOT LIKE':
								$meta_value = '%' . $wpdb->esc_like( $meta_value ) . '%';
								$where .= $wpdb->prepare( '%s', $meta_value );
								break;

							// EXISTS with a value is interpreted as '='.
							case 'EXISTS':
								$meta_compare = '=';
								$where .= $wpdb->prepare( '%s', $meta_value );
								break;

							// 'value' is ignored for NOT EXISTS.
							case 'NOT EXISTS':
								$where = '';
								break;

							default:
								$where .= $wpdb->prepare( '%s', $meta_value );
								break;
						}
					}
				}
			}
		}

		// Build date query.
		if ( array_key_exists( 'date_query', $args ) && ! empty( $args['date_query'] ) && is_array( $args['date_query'] ) ) {
			$date_query = new WP_Date_Query( $args['date_query'], EDD()->api_request_logs->table_name . '.date_created' );
			$where .= $date_query->get_sql();
		}

		// User ID.
		if ( array_key_exists( 'user_id', $args ) && ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$args['user_id'] = wp_parse_id_list( $args['user_id'] );
				$user_ids = implode( "','", array_map( 'sanitize_text_field', $args['user_id'] ) );
			} else {
				$user_ids = sanitize_text_field( absint( $args['user_id'] ) );
			}
			$where .= " AND {$table_name}.user_id IN ( '{$user_ids}' )";
		}

		// API Key.
		if ( array_key_exists( 'api_key', $args ) && ! empty( $args['api_key'] ) ) {
			if ( is_array( $args['api_key'] ) ) {
				$api_keys = implode( "','", array_map( 'sanitize_text_field', $args['api_key'] ) );
			} else {
				$api_keys = sanitize_text_field( $args['api_key'] );
			}
			$where .= " AND {$table_name}.api_key IN ( '{$api_keys}' )";
		}

		// Token.
		if ( array_key_exists( 'token', $args ) && ! empty( $args['token'] ) ) {
			if ( is_array( $args['token'] ) ) {
				$tokens = implode( "','", array_map( 'sanitize_text_field', $args['token'] ) );
			} else {
				$tokens = sanitize_text_field( $args['token'] );
			}
			$where .= " AND {$table_name}.token IN ( '{$tokens}' )";
		}

		// Version.
		if ( array_key_exists( 'version', $args ) && ! empty( $args['version'] ) ) {
			if ( is_array( $args['version'] ) ) {
				$versions = implode( "','", array_map( 'sanitize_text_field', $args['version'] ) );
			} else {
				$versions = sanitize_text_field( $args['version'] );
			}
			$where .= " AND {$table_name}.version IN ( '{$versions}' )";
		}

		// Request.
		if ( array_key_exists( 'request', $args ) && ! empty( $args['request'] ) ) {
			if ( is_array( $args['request'] ) ) {
				$requests = implode( "','", array_map( 'sanitize_text_field', $args['request'] ) );
			} else {
				$requests = sanitize_text_field( $args['request'] );
			}
			$where .= " AND {$table_name}.request IN ( '{$requests}' )";
		}

		// Error.
		if ( array_key_exists( 'error', $args ) && ! empty( $args['error'] ) ) {
			if ( is_array( $args['error'] ) ) {
				$errors = implode( "','", array_map( 'sanitize_text_field', $args['error'] ) );
			} else {
				$errors = sanitize_text_field( $args['error'] );
			}
			$where .= " AND {$table_name}.error IN ( '{$errors}' )";
		}

		// IP.
		if ( array_key_exists( 'ip', $args ) && ! empty( $args['ip'] ) ) {
			if ( is_array( $args['ip'] ) ) {
				$ips = implode( "','", array_map( 'sanitize_text_field', $args['ip'] ) );
			} else {
				$ips = sanitize_text_field( $args['ip'] );
			}
			$where .= " AND {$table_name}.ip IN ( '{$ips}' )";
		}

		// Time.
		if ( array_key_exists( 'time', $args ) && ! empty( $args['time'] ) ) {
			if ( is_array( $args['time'] ) ) {
				$times = implode( "','", array_map( 'sanitize_text_field', $args['time'] ) );
			} else {
				$times = sanitize_text_field( $args['time'] );
			}
			$where .= " AND {$table_name}.time IN ( '{$times}' )";
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

		// Search. If a search parameter is passed, we assume that we are searching in the request column.
		if ( array_key_exists( 'search', $args ) && ! empty( $args['search'] ) ) {
			$search = esc_sql( sanitize_text_field( $args['search'] ) );
			$where .= " AND request LIKE '%%" . $search . "%%'";
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Count the total number of API request logs in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 * }
	 *
	 * @return int $count Number of API request logs in the database.
	 */
	public function count( $args = array() ) {
		global $wpdb;

		$where = $this->parse_where( $args );
		$sql   = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};";
		$count = $wpdb->get_var( $sql );

		return absint( $count );
	}

	/**
	 * Sets the last_changed cache key for API request logs.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for API request logs.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return string Value of the last_changed cache key for API request logs.
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