<?php
/**
 * Order Transactions Table.
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
 * Setup the global "edd_order_transactions" database table.
 *
 * @since 3.0
 */
final class Order_Transactions extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'order_transactions';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202205241;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202002141' => 202002141,
		'202005261' => 202005261,
		'202105291' => 202105291,
		'202205241' => 202205241,
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
			object_type varchar(20) NOT NULL default '',
			transaction_id varchar(256) NOT NULL default '',
			gateway varchar(20) NOT NULL default '',
			status varchar(20) NOT NULL default '',
			total decimal(18,9) NOT NULL default '0',
			rate decimal(10,5) NOT NULL DEFAULT 1.00000,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY transaction_id (transaction_id(64)),
			KEY gateway (gateway(20)),
			KEY status (status(20)),
			KEY date_created (date_created),
			KEY object_type_object_id (object_type, object_id)";
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
	 * Upgrade to version 202005261
	 *  - Changed the column length from 64 to 256 in order to account for future updates to gateway data.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __202005261() {

		// Increase the transaction_id column.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `transaction_id` varchar(256) NOT NULL default '';
		" );

		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 202105291
	 * 	- Add `rate` column.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202105291() {
		if ( ! $this->column_exists( 'rate' ) ) {
			return $this->is_success(
				$this->get_db()->query(
					"ALTER TABLE {$this->table_name} ADD COLUMN rate decimal(10,5) NOT NULL DEFAULT 1.00000 AFTER total"
				)
			);
		}

		return true;
	}

	/**
	 * Upgrade to version 202205241
	 * 	- Add combined index for object_type, object_id.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202205241() {
		if ( $this->index_exists( 'object_type_object_id' ) ) {
			return true;
		}

		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX object_type_object_id (object_type, object_id)" );

		return true;
	}

}
