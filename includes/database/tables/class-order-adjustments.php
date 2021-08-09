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
	protected $version = 202105221;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202002141' => 202002141,
		'202011122' => 202011122,
		'202103151' => 202103151,
		'202105221' => 202105221,
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
		parent bigint(20) unsigned NOT NULL default '0',
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) DEFAULT NULL,
		type_id bigint(20) unsigned DEFAULT NULL,
		type varchar(20) DEFAULT NULL,
		type_key varchar(255) DEFAULT NULL,
		description varchar(100) DEFAULT NULL,
		subtotal decimal(18,9) NOT NULL default '0',
		tax decimal(18,9) NOT NULL default '0',
		total decimal(18,9) NOT NULL default '0',
		rate decimal(10,5) NOT NULL DEFAULT 1.00000,
		date_created datetime NOT NULL default CURRENT_TIMESTAMP,
		date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY object_id_type (object_id,object_type(20)),
		KEY date_created (date_created),
		KEY parent (parent)";
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
	 * Upgrade to version 202011122
	 *  - Change default value to `NULL` for `type_id` column.
	 *  - Add `type_key` column.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202011122() {

		// Update `type_id`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `type_id` bigint(20) default NULL;
		" );

		// Add `type_key`.
		$column_exists = $this->column_exists( 'type_key' );
		if ( false === $column_exists ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `type_key` varchar(255) default NULL AFTER `type`;
			" );
		} else {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} MODIFY `type_key` varchar(255) default NULL AFTER `type`
			" );
		}

		// Change `type_id` with `0` value to `null` to support new default.
		$this->get_db()->query( "UPDATE {$this->table_name} SET type_id = null WHERE type_id = 0;" );

		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 202103151
	 * 	- Add column `parent`
	 * 	- Add index on `parent` column.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202103151() {
		// Look for column
		$result = $this->column_exists( 'parent' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN parent bigint(20) unsigned NOT NULL default '0' AFTER id;
			" );
		}

		if ( ! $this->index_exists( 'parent' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX parent (parent)" );
		}

		// Return success/fail.
		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 202105221
	 * 	- Add `rate` column.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202105221() {
		if ( ! $this->column_exists( 'rate' ) ) {
			return $this->is_success(
				$this->get_db()->query(
					"ALTER TABLE {$this->table_name} ADD COLUMN rate decimal(10,5) NOT NULL DEFAULT 1.00000 AFTER total"
				)
			);
		}

		return true;
	}
}
