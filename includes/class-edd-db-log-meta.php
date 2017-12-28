<?php
/**
 * Log Meta DB class
 *
 * This class is for interacting with the log meta database table.
 *
 * @package     EDD
 * @subpackage  Classes/Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_DB_Logs Class.
 *
 * @since 3.0
 */
class EDD_DB_Log_Meta extends EDD_DB {

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_logmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );
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
			'meta_id'    => '%d',
			'edd_log_id' => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	}

	/**
	 * Register the table with $wpdb so the metadata API can find it.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function register_table() {
		global $wpdb;
		$wpdb->edd_logmeta = $this->table_name;
	}

	/**
	 * Retrieve meta field for a log.
	 *
	 * For internal use only. Use EDD_Log->get_meta() for public usage.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param int    $log_id    Log ID.
	 * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
	 *                          the specified object.
	 * @param bool   $single    Optional, default is false.
	 *                          If true, return only the first value of the specified meta_key.
	 *                          This parameter has no effect if meta_key is not specified.
	 *
	 * @return mixed array|false Single metadata value, or array of values.
	 */
	public function get_meta( $log_id = 0, $meta_key = '', $single = false ) {
		$log_id = $this->sanitize_log_id( $log_id );

		if ( false === $log_id ) {
			return false;
		}

		return get_metadata( 'edd_log', $log_id, $meta_key, $single );
	}
	/**
	 * Add meta data field to a log.
	 *
	 * For internal use only. Use EDD_Log->add_meta() for public usage.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int    $log_id Log ID.
	 * @param string $meta_key    Metadata key.
	 * @param mixed  $meta_value  Metadata value.
	 * @param bool   $unique      Optional, default is false.
	 *                            Whether the specified metadata key should be unique for the object.
	 *                            If true, and the object already has a value for the specified metadata key,
	 *                            no change will be made.
	 *
	 * @return int|false The meta ID on success, false on failure.
	 */
	public function add_meta( $log_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$log_id = $this->sanitize_log_id( $log_id );

		if ( false === $log_id ) {
			return false;
		}

		return add_metadata( 'edd_log', $log_id, $meta_key, $meta_value, $unique );
	}
	/**
	 * Update log meta field based on log ID.
	 *
	 * For internal use only. Use EDD_Log->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and log ID.
	 *
	 * If the meta field for the log does not exist, it will be added.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int    $log_id     Log ID.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value.
	 * @param mixed  $prev_value Optional. Previous value to check before removing.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $log_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$log_id = $this->sanitize_log_id( $log_id );

		if ( false === $log_id ) {
			return false;
		}

		return update_metadata( 'edd_log', $log_id, $meta_key, $meta_value, $prev_value );
	}
	/**
	 * Remove metadata matching criteria from a log.
	 *
	 * For internal use only. Use EDD_Log->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param int    $log_id     Log ID.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
	 *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
	 *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
	 *                           it is not possible to pass an empty string to delete those entries with an empty string
	 *                           for a value.)
	 *
	 * @return bool True on successful delete, false on failure.
	 */
	public function delete_meta( $log_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'edd_log', $log_id, $meta_key, $meta_value );
	}
	/**
	 * Create the table.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "
			CREATE TABLE {$this->table_name} (
				meta_id bigint(20) NOT NULL AUTO_INCREMENT,
				edd_log_id bigint(20) NOT NULL,
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext,
				PRIMARY KEY  (meta_id),
				KEY edd_log_id (edd_log_id),
				KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;
		";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
	/**
	 * Given a log ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param mixed int|string $log_id Log ID.
	 *
	 * @return mixed int|bool The normalized log ID or false if it's found to not be valid.
	 */
	private function sanitize_log_id( $log_id ) {
		if ( ! is_numeric( $log_id ) ) {
			return false;
		}

		$log_id = (int) $log_id;

		// We were given a non positive number
		if ( absint( $log_id ) !== $log_id ) {
			return false;
		}

		if ( empty( $log_id ) ) {
			return false;
		}

		return absint( $log_id );
	}
}