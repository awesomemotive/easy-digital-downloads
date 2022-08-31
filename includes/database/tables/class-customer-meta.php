<?php
/**
 * Customer Meta Table.
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
 * Setup the global "edd_customermeta" database table
 *
 * @since 3.0
 */
final class Customer_Meta extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'customermeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201807111;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807111' => 201807111
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_customer_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY edd_customer_id (edd_customer_id),
			KEY meta_key (meta_key({$max_index_length}))";
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
			delete_option( $this->table_prefix . 'edd_customermeta_db_version' );
			delete_option( 'wp_edd_customermeta_db_version' );

			$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `customer_id` `edd_customer_id` bigint(20) unsigned NOT NULL default '0';" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX customer_id" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX edd_customer_id (edd_customer_id)" );
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
		return $this->exists() && $this->column_exists( 'customer_id' ) && ! $this->column_exists( 'edd_customer_id' );
	}

	/**
	 * Upgrade to version 201807111
	 * - Rename  `customer_id` column to `edd_customer_id`
	 * - Add `status` column.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __201807111() {

		// Alter the database with separate queries so indexes succeed
		if ( $this->column_exists( 'customer_id' ) && ! $this->column_exists( 'edd_customer_id' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `customer_id` `edd_customer_id` bigint(20) unsigned NOT NULL default '0'" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX customer_id" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX edd_customer_id (edd_customer_id)" );
		}

		// Return success/fail
		return $this->is_success( true );
	}
}
