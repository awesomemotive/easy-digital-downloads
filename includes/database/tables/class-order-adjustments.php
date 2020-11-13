<?php
/**
 * Order Adjustments Table.
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
 * Setup the global "edd_order_adjustments" database table
 *
 * @since 3.0
 */
final class Order_Adjustments extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'order_adjustments';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202011121;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807071' => 201807071,
		'201807273' => 201807273,
		'202002141' => 202002141,
		'202011121' => 202011121,
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
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) DEFAULT NULL,
		type_id bigint(20) unsigned DEFAULT NULL,
		type_key varchar(255) DEFAULT NULL,
		type varchar(20) DEFAULT NULL,
		description varchar(100) DEFAULT NULL,
		subtotal decimal(18,9) NOT NULL default '0',
		tax decimal(18,9) NOT NULL default '0',
		total decimal(18,9) NOT NULL default '0',
		date_created datetime NOT NULL default CURRENT_TIMESTAMP,
		date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY object_id_type (object_id,object_type(20)),
		KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201807071
	 * - Add subtotal and tax columns.
	 * - Rename amount column to total.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __201807071() {

		// Alter the database.
		$this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `amount` `total` decimal(18,9) NOT NULL default '0'" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `subtotal` decimal(18,9) NOT NULL default '0';" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `tax` decimal(18,9) NOT NULL default '0'" );

		// Return success/fail.
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

	/**
	 * Upgrade to version 202011121
	 *  - Change default value to `NULL` for `type_id` column.
	 *  - Add `type_key` column.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202011121() {

		// Update `type_id`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `type_id` bigint(20) default NULL;
		" );

		// Add `type_key`.
		$column_exists = $this->column_exists( 'type_key' );
		if ( false === $column_exists ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `type_key` varchar(255) default NULL AFTER `type_id`;
			" );
		}

		// Change `type_id` with `0` value to `null` to support new default.
		$this->get_db()->query( "UPDATE {$this->table_name} SET type_id = null WHERE type_id = 0;" );

		return $this->is_success( $result );
	}
}
