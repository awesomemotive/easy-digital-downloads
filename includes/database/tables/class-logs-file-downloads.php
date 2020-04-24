<?php
/**
 * File Download Logs Table.
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
 * Setup the global "edd_logs_file_downloads" database table
 *
 * @since 3.0
 */
final class Logs_File_Downloads extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'logs_file_downloads';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202002141;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201806281' => 201806281,
		'201807273' => 201807273,
		'202002141' => 202002141,
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
		product_id bigint(20) unsigned NOT NULL default '0',
		file_id bigint(20) unsigned NOT NULL default '0',
		order_id bigint(20) unsigned NOT NULL default '0',
		price_id bigint(20) unsigned NOT NULL default '0',
		customer_id bigint(20) unsigned NOT NULL default '0',
		ip varchar(60) NOT NULL default '',
		user_agent varchar(200) NOT NULL default '',
		date_created datetime NOT NULL default CURRENT_TIMESTAMP,
		date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY customer_id (customer_id),
		KEY product_id (product_id),
		KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201806281
	 * - Rename  `download_id` column to `product_id`
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201806281() {

		// Alter the database with separate queries so indexes succeed
		$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE COLUMN download_id product_id bigint(20) unsigned NOT NULL default 0" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE COLUMN user_id customer_id bigint(20) unsigned NOT NULL default 0" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE COLUMN payment_id order_id bigint(20) unsigned NOT NULL default 0" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX download_id" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX product_id (product_id)" );

		// Return success/fail
		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 201807273
	 * - Add the `uuid` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807273() {

		// Look for column
		$result = $this->column_exists( 'uuid' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;
			" );
		}

		// Return success/fail
		return $this->is_success( $result );
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

}
