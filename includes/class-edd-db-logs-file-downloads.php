<?php
/**
 * File Download Logs DB class.
 *
 * This class is for interacting with the file download logs table.
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
 * EDD_DB_Logs_File_Downloads Class.
 *
 * @since 3.0
 */
class EDD_DB_Logs_File_Downloads extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @since  3.0
	 * @access public
	 * @var    string
	 */
	public $cache_group = 'logs_file_downloads';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_logs_file_downloads';
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
			'download_id'  => '%d',
			'file_id'      => '%d',
			'payment_id'   => '%d',
			'price_id'     => '%d',
			'user_id'      => '%d',
			'ip'           => '%s',
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
			'download_id'  => 0,
			'file_id'      => 0,
			'payment_id'   => 0,
			'price_id'     => 0,
			'user_id'      => 0,
			'ip'           => '',
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
	 * @param array  $data {
	 *      API request log attributes.
	 * }
	 * @param string $type Data type to insert (forced to 'api_request_log'.
	 *
	 * @return int ID of the inserted log.
	 */
	public function insert( $data, $type = 'file_download_log' ) {
		// Forced to ensure the correct actions run.
		$type = 'file_download_log';

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
	 *      API request log attributes.
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
	 * Retrieve log from the database
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *      Query arguments.
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

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$args['search'] = $wpdb->esc_like( $args['search'] );
		}

		$where = $this->parse_where( $args );

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'edd_file_download_logs_' . serialize( $args ) );

		$logs = wp_cache_get( $cache_key, 'file_download_logs' );

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
					$logs[ $key ] = new EDD_File_Download_Log( $log );
				}

				wp_cache_set( $cache_key, $logs, 'file_download_logs', 3600 );
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