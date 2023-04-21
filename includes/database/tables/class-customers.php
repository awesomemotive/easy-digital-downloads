<?php
/**
 * Customers Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Setup the global "edd_customers" database table
 *
 * @since 3.0
 */
final class Customers extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'customers';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202303220;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202006101' => 202006101,
		'202301021' => 202301021,
		'202303220' => 202303220,
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL default '0',
			email varchar(100) NOT NULL default '',
			name varchar(255) NOT NULL default '',
			status varchar(20) NOT NULL default '',
			purchase_value decimal(18,9) NOT NULL default '0',
			purchase_count bigint(20) unsigned NOT NULL default '0',
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY user (user_id),
			KEY status (status(20)),
			KEY date_created (date_created)";
	}

	/**
	 * Whether the initial upgrade from the 1.0 database needs to be run.
	 *
	 * @since 3.0.3
	 * @since 3.1.1 Explicitly checks each of these cases to ensure a non-fully updated table is updated, but not currently used.
	 * @return bool
	 */
	private function needs_initial_upgrade() {
		if ( $this->exists() && ! $this->column_exists( 'status' ) ) {
			return true;
		}

		if ( $this->exists() && ! $this->column_exists( 'uuid' ) ) {
			return true;
		}

		if ( $this->exists() && ! $this->column_exists( 'date_modified' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Upgrade to version 202002141
	 *  - Change default value to `CURRENT_TIMESTAMP` for columns `date_created` and `date_modified`.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202002141() {

		// Update `date_created`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_created` datetime NOT NULL default CURRENT_TIMESTAMP;
		" );

		// Update `date_modified`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_modified` datetime NOT NULL default CURRENT_TIMESTAMP;
		" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 202006101
	 * - Remove the payment_ids column if it still exists.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202006101() {

		$result = true;

		// Remove the column.
		if ( $this->column_exists( 'payment_ids' ) ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} DROP `payment_ids`
			" );
		}

		return $this->is_success( $result );
	}

	/**
	 * Checks the status of the edd_customers table for health and state and performs
	 * any necessary changes that haven't been prevoiusly made.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	protected function __202301021() {
		// Verify that columns that existed prior to 3.0 get the proper changes made.
		$columns                 = $this->get_db()->get_results( "SHOW COLUMNS FROM {$this->table_name}" );
		$existing_column_updates = array();

		// Set an array to hold the result of each query run.
		$query_results = array();

		foreach ( $columns as $column ) {
			switch ( $column->Field ) {
				case 'email':
					if ( 'varchar(100)' !== $column->Type ) {
						$existing_column_updates['alter-email-type'] = "ALTER TABLE {$this->table_name} MODIFY `email` varchar(100) NOT NULL default ''";
					}
					break;

				case 'name':
					if ( 'varchar(255)' !== $column->Type ) {
						$existing_column_updates['alter-name-type'] = "ALTER TABLE {$this->table_name} MODIFY `name` varchar(255) NOT NULL default ''";
					}
					break;

				case 'user_id':
					if ( 'bigint(20)' !== $column->Type ) {
						$existing_column_updates['alter-user_id-type'] = "ALTER TABLE {$this->table_name} MODIFY `user_id` bigint(20) unsigned NOT NULL default '0'";
					}
					break;

				case 'purchase_value':
					if ( 'decimal(18,9)' !== $column->Type ) {
						$existing_column_updates['alter-purchase_value-type'] = "ALTER TABLE {$this->table_name} MODIFY `purchase_value` decimal(18,9) NOT NULL default '0'";
					}
					break;

				case 'purchase_count':
					if ( 'bigint(20)' !== $column->Type ) {
						$existing_column_updates['alter-purchase_count-type'] = "ALTER TABLE {$this->table_name} MODIFY `purchase_count` bigint(20) unsigned NOT NULL default '0'";
					}
					break;

				case 'date_created':
					if ( 'datetime' !== $column->Type ) {
						$existing_column_updates['alter-date_created-type'] = "ALTER TABLE {$this->table_name} MODIFY `date_created` datetime NOT NULL default CURRENT_TIMESTAMP";
					}
					break;

				case 'payment_ids':
					$existing_column_updates['drop-payment_ids'] = "ALTER TABLE {$this->table_name} DROP COLUMN `payment_ids`";
					break;

				case 'notes':
					// Only remove the customer notes column if they've already run the customer notes migration.
					if ( edd_has_upgrade_completed( 'v30_legacy_data_removed' ) ) {
						$existing_column_updates['drop-notes'] = "ALTER TABLE {$this->table_name} DROP COLUMN `notes`";
					}
					break;
			}
		}

		if ( ! empty( $existing_column_updates ) ) {
			foreach ( $existing_column_updates as $query_key => $update_sql ) {
				$query_results[ $query_key ] = $this->is_success( $this->get_db()->query( $update_sql ) );
			}
		}

		// Now verify that new columns are created and exist.
		if ( ! $this->column_exists( 'status' ) ) {
			$query_results['add-status-column'] = $this->is_success( $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `status` varchar(20) NOT NULL default 'active' AFTER `name`;" ) );
			$query_results['index-status']      = $this->is_success( $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX status (status(20))" ) );
		}

		if ( ! $this->column_exists( 'date_modified' ) ) {
			$query_results['add-date_modified-column']     = $this->is_success( $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime DEFAULT CURRENT_TIMESTAMP AFTER `date_created`" ) );
			$query_results['update-modified-with-created'] = $this->is_success( $this->get_db()->query( "UPDATE {$this->table_name} SET `date_modified` = `date_created`" ) );
			$query_results['index-date_created']           = $this->is_success( $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX date_created (date_created)" ) );
		}

		if ( ! $this->column_exists( 'uuid' ) ) {
			$query_results['add-uuid-column'] = $this->is_success( $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;" ) );
		}

		$return_result = true;

		// Loop through each of the query results and force a debug log for any failures.
		foreach ( $query_results as $query_key => $query_result ) {
			if ( false === $query_result ) {
				$return_result = false;
				edd_debug_log( 'Customer\'s table version ' . $this->version . ' update failed for: ' . $query_key, true );
			}
		}

		delete_option( $this->table_prefix . 'edd_customers_db_version' );
		delete_option( 'wp_edd_customers_db_version' );

		return $this->is_success( $return_result );
	}

	/**
	 * Upgrades the customer database for sites which got into a bit of a snarl with the database versions.
	 *
	 * @since 3.1.1.3
	 * @return bool
	 */
	protected function __202303220() {
		if ( $this->needs_initial_upgrade() ) {
			return $this->__202301021();
		}

		return true;
	}
}
