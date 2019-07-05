<?php
/**
 * Order Addresses Table.
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
 * Setup the global "edd_order_addresses" database table
 *
 * @since 3.0
 */
final class Order_Addresses extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'order_addresses';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201906250001;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807270003' => 201807270003,
		'201906250001' => 201906250001,
		'201906280001' => 201906280001,
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
		$this->schema     = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'billing',
			name mediumtext NOT NULL,
			address mediumtext NOT NULL,
			address2 mediumtext NOT NULL,
			city mediumtext NOT NULL,
			region mediumtext NOT NULL,
			postal_code varchar(32) NOT NULL default '',
			country mediumtext NOT NULL,
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY city (city({$max_index_length})),
			KEY region (region({$max_index_length})),
			KEY postal_code (postal_code(32)),
			KEY country (country({$max_index_length})),
			KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201807270003
	 * - Add the `uuid` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807270003() {

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
	 * Upgrade to version 201906250001
	 * - Adds the 'name' column
	 * - Combines the `first_name` and `last_name` columns to the `name` column.
	 * - Removes the `first_name` and `last_name` columns.
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201906250001() {

		$success = true;

		$column_exists = $this->column_exists( 'name' );

		// Don't take any action if the column already exists.
		if ( false === $column_exists ) {
			$column_exists = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `name` mediumtext NOT NULL AFTER `last_name`;
			" );

		}

		$deprecated_columns_exist = ( $this->column_exists( 'first_name' ) && $this->column_exists( 'last_name' ) );
		if ( $column_exists && $deprecated_columns_exist ) {
			$data_merged = $this->get_db()->query( "
					UPDATE {$this->table_name} SET name = CONCAT(first_name, ' ', last_name);
				" );

			if ( $data_merged ) {
				$success = $this->get_db()->query( "
					ALTER TABLE {$this->table_name} DROP first_name, DROP last_name;
				" );
				}
		}

		return $this->is_success( $success );
	}

	/**
	 * Upgrade to version 201906280001
	 * - Add the `type` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201906280001() {

		// Look for column.
		$result = $this->column_exists( 'type' );

		// Maybe add column.
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `type` varchar(20) default 'billing' AFTER `order_id`;
			" );
		}

		// Return success/fail.
		return $this->is_success( $result );
	}

}
