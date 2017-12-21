<?php
/**
 * Discount Meta DB class
 *
 * This class is for interacting with the discount meta database table
 *
 * @package     EDD
 * @subpackage  Classes/DB Discount Meta
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_DB_Discount_Meta extends EDD_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_discountmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

	}

	/**
	 * Get table columns and data types
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function get_columns() {
		return array(
			'meta_id'         => '%d',
			'edd_discount_id' => '%d',
			'meta_key'        => '%s',
			'meta_value'      => '%s',
		);
	}

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function register_table() {
		global $wpdb;
		$wpdb->edd_discountmeta = $this->table_name;
	}

	/**
	 * Retrieve customer meta field for a customer.
	 *
	 * For internal use only. Use EDD_Discount->get_meta() for public usage.
	 *
	 * @param   int    $discount_id   Customer ID.
	 * @param   string $meta_key      The meta key to retrieve.
	 * @param   bool   $single        Whether to return a single value.
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access  private
	 * @since   3.0
	 */
	public function get_meta( $discount_id = 0, $meta_key = '', $single = false ) {
		$discount_id = $this->sanitize_discount_id( $discount_id );
		if ( false === $discount_id ) {
			return false;
		}

		return get_metadata( 'edd_discount', $discount_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a customer.
	 *
	 * For internal use only. Use EDD_Discount->add_meta() for public usage.
	 *
	 * @param   int    $discount_id   Customer ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  private
	 * @since   3.0
	 */
	public function add_meta( $discount_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$discount_id = $this->sanitize_discount_id( $discount_id );
		if ( false === $discount_id ) {
			return false;
		}

		return add_metadata( 'edd_discount', $discount_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update customer meta field based on Customer ID.
	 *
	 * For internal use only. Use EDD_Discount->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Discount ID.
	 *
	 * If the meta field for the customer does not exist, it will be added.
	 *
	 * @param   int    $discount_id   Discount ID.
	 * @param   string $meta_key      Metadata key.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   mixed  $prev_value    Optional. Previous value to check before removing.
	 * @return  bool                  False on failure, true if success.
	 *
	 * @access  private
	 * @since   3.0
	 */
	public function update_meta( $discount_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$discount_id = $this->sanitize_discount_id( $discount_id );
		if ( false === $discount_id ) {
			return false;
		}

		return update_metadata( 'edd_discount', $discount_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a customer.
	 *
	 * For internal use only. Use EDD_Discount->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param   int    $discount_id   Discount ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Optional. Metadata value.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  private
	 * @since   3.0
	 */
	public function delete_meta( $discount_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'edd_discount', $discount_id, $meta_key, $meta_value );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   3.0
	*/
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			edd_discount_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY discount_id (discount_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Given a discount ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since  3.0
	 * @param  int|stirng $discount_id A passed discount ID.
	 * @return int|bool                The normalized discount ID or false if it's found to not be valid.
	 */
	private function sanitize_discount_id( $discount_id ) {
		if ( ! is_numeric( $discount_id ) ) {
			return false;
		}

		$discount_id = (int) $discount_id;

		// We were given a non positive number
		if ( absint( $discount_id ) !== $discount_id ) {
			return false;
		}

		if ( empty( $discount_id ) ) {
			return false;
		}

		return absint( $discount_id );

	}

}
