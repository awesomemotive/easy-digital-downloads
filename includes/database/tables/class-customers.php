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
	protected $version = 202006101;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202002141' => 202002141,
		'202006101' => 202006101,
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
	 * Override the Base class `maybe_upgrade()` routine to do a very unique and
	 * special check against the old option.
	 *
	 * Maybe upgrades the database table from 2.x to 3.x standards. This method
	 * should be kept up-to-date with schema changes in `set_schema()` above.
	 *
	 * - Hooked to the "admin_init" action.
	 * - Calls the parent class `maybe_upgrade()` method
	 *
	 * @since 3.0
	 */
	public function maybe_upgrade() {

		if ( $this->needs_initial_upgrade() ) {

			// Delete old/irrelevant database options.
			delete_option( $this->table_prefix . 'edd_customers_db_version' );
			delete_option( 'wp_edd_customers_db_version' );


			// Modify existing columns.
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `email` varchar(100) NOT NULL default ''" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `name` varchar(255) NOT NULL default ''" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `user_id` bigint(20) unsigned NOT NULL default '0'" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `purchase_value` decimal(18,9) NOT NULL default '0'" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `purchase_count` bigint(20) unsigned NOT NULL default '0'" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `date_created` datetime NOT NULL default CURRENT_TIMESTAMP" );

			if ( ! $this->column_exists( 'status' ) ) {
				$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `status` varchar(20) NOT NULL default 'active' AFTER `name`;" );
				$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX status (status(20))" );
			}

			if ( ! $this->column_exists( 'date_modified' ) ) {
				$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime DEFAULT CURRENT_TIMESTAMP AFTER `date_created`" );
				$this->get_db()->query( "UPDATE {$this->table_name} SET `date_modified` = `date_created`" );
				$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX date_created (date_created)" );
			}

			if ( ! $this->column_exists( 'uuid' ) ) {
				$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;" );
			}
		}

		parent::maybe_upgrade();
	}

	/**
	 * Whether the initial upgrade from the 1.0 database needs to be run.
	 *
	 * @since 3.0.3
	 * @return bool
	 */
	private function needs_initial_upgrade() {
		return $this->exists() && ! $this->column_exists( 'status' ) && ! $this->column_exists( 'uuid' );
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

}
