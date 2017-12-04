<?php
/**
 * Discounts DB class
 *
 * This class is for interacting with the discounts database table
 *
 * @package     EDD
 * @subpackage  Classes/DB Customers
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
class EDD_DB_Discounts extends EDD_DB  {

	/**
	 * The metadata type.
	 *
	 * @access public
	 * @since  3.0
	 * @var string
	 */
	public $meta_type = 'discount';

	/**
	 * The name of the date column.
	 *
	 * @access public
	 * @since  3.0
	 * @var string
	 */
	public $date_key = 'date_created';

	/**
	 * The name of the cache group.
	 *
	 * @access public
	 * @since  3.0
	 * @var string
	 */
	public $cache_group = 'discounts';

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_discounts';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function get_columns() {
		return array(
			'id'                  => '%d',
			'name'                => '%s',
			'code'                => '%s',
			'status'              => '%s',
			'type'                => '%s',
			'amount'              => '%s',
			'description'         => '%s',
			'use_count'           => '%d',
			'max_uses'            => '%d',
			'min_price'           => '%f',
			'notes'               => '%s',
			'date_created'        => '%s',
			'start_date'          => '%s',
			'expiration'          => '%s',
			'once_per_customer'   => '%d',
			'valid_products'      => '%s',
			'excluded_products'   => '%s',
			'applies_globally'    => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function get_column_defaults() {
		return array(
			'id'                  => 0,
			'name'                => '',
			'code'                => '',
			'status'              => '',
			'type'                => '',
			'amount'              => '',
			'description'         => '',
			'use_count'           => 0,
			'max_uses'            => 0,
			'min_price'           => 0.00,
			'notes'               => '',
			'date_created'        => date( 'Y-m-d H:i:s' ),
			'start_date'          => '',
			'expiration'          => '',
			'once_per_customer'   => 0,
			'valid_products'      => '',
			'excluded_products'   => '',
			'applies_globally'    => 0,
		);
	}

	/**
	 * Insert a new customer
	 *
	 * @access  public
	 * @since   3.0
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update a customer
	 *
	 * @access  public
	 * @since   3.0
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
	 * Delete a discount
	 *
	 * @access  public
	 * @since   2.3.1
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
	 * @access  public
	 * @since   3.0
	*/
	public function get_discounts( $args = array() ) {

	}


	/**
	 * Count the total number of customers in the database
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function count( $args = array() ) {


		return $results;
	}

	/**
	 * Sets the last_changed cache key for customers.
	 *
	 * @access public
	 * @since  3.0
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for customers.
	 *
	 * @access public
	 * @since  3.0
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
	 * Create the table
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name mediumtext NOT NULL,
		code varchar(50) NOT NULL,
		status varchar(20) NOT NULL,
		type varchar(20) NOT NULL,
		amount mediumtext NOT NULL,
		description longtext NOT NULL,
		use_count bigint(20) longtext NOT NULL,
		max_uses bigint(20) longtext NOT NULL,
		min_price mediumtext longtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		start_date datetime NOT NULL,
		expiration datetime NOT NULL,
		once_per_customer tinyint NOT NULL,
		valid_products mediumtext NOT NULL,
		excluded_products mediumtext NOT NULL,
		applies_globally tinyint NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY code (code),
		KEY name (name)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
