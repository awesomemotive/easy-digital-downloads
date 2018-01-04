<?php
/**
 * Notes DB class
 *
 * This class is for interacting with the notes database table
 *
 * @package     EDD
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_DB_Notes Class.
 *
 * @since 3.0
 */
class EDD_DB_Notes extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @access public
	 * @since  3.0
	 * @var    string
	 */
	public $cache_group = 'notes';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_notes';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}
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
			'id'           => '%d',
			'object_id'    => '%s',
			'object_type'  => '%s',
			'note'         => '%s',
			'user_id'      => '%s',
			'date_created' => '%s',
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
			'id'           => 0,
			'object_id'    => '',
			'object_type'  => '',
			'note'         => '',
			'user_id'      => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);
	}
	/**
	 * Insert a new note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @see EDD_DB::insert()
	 *
	 * @param array $data {
	 *      Data for the note.
	 *
	 *      @type string $object_id    The ID of the object that the note links to.
	 *      @type string $object_type  The object type that the note is linked to (payment/discount).
	 *      @type string $note         Body of the note.
	 *      @type string $user_id      User ID of the user that created the note.
	 *      @type float  $date_created Date created (default blank and auto-filled on insert).
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
	 * Update a note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int   $row_id Note ID.
	 * @param array $data {
	 *      Data for the note.
	 *
	 *      @type string $object_id    The ID of the object that the note links to.
	 *      @type string $object_type  The object type that the note is linked to (payment/discount).
	 *      @type string $note         Body of the note.
	 *      @type string $user_id      User ID of the user that created the note.
	 *      @type float  $date_created Date created (default blank and auto-filled on insert).
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
	 * Delete a note.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int $row_id ID of the note to delete.
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
	 * Retrieve notes from the database.
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
	public function get_notes( $args = array() ) {
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

		$cache_key = md5( 'edd_notes_' . serialize( $args ) );

		$notes = wp_cache_get( $cache_key, 'notes' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( false === $notes ) {
			$notes = $wpdb->get_col( $wpdb->prepare(
				"
					SELECT id
					FROM $this->table_name
					$where
					ORDER BY {$args['orderby']} {$args['order']}
					LIMIT %d,%d;
				", absint( $args['offset'] ), absint( $args['number'] ) ), 0 );

			if ( ! empty( $notes ) ) {
				foreach ( $notes as $key => $discount ) {

				}

				wp_cache_set( $cache_key, $notes, 'notes', 3600 );
			}
		}

		return $notes;
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
	 *      @type mixed array|string $object_type  Object type.
	 *      @type int                $user_id      User ID.
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

		// Specific object types
		if ( ! empty( $args['object_type'] ) ) {
			if ( is_array( $args['object_type'] ) ) {
				$types = implode( "','", array_map( 'sanitize_text_field', $args['object_type'] ) );
			} else {
				$types = sanitize_text_field( $args['object_type'] );
			}

			$where .= " AND `object_type` IN( '{$types}' ) ";
		}

		// Specific user ID
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$user_id = implode( "','", array_map( 'absint', $args['user_id'] ) );
			} else {
				$user_id = absint( $args['user_id'] );
			}

			$where .= " AND `user_id` IN( '{$user_id}' ) ";
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

		// Specific end_date date
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

		// Specific start date or in a start/end date range
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
			$where .= " AND ( note LIKE '%%" . $args['search'] . "%%' )";
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Count the total number of notes in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 *
	 *      @type mixed array|string $object_type  Object type.
	 *      @type int                $user_id      User ID.
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

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "
			CREATE TABLE {$this->table_name} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				object_id bigint(20) UNSIGNED NOT NULL ,
				object_type varchar(100) NOT NULL,
				note longtext NOT NULL,
				user_id bigint(20) unsigned NOT NULL,
				date_created datetime NOT NULL,
				PRIMARY KEY (id),
				KEY object_type (object_type)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;
		";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}