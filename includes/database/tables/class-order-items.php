<?php
/**
 * Order Items Table.
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
 * Setup the global "edd_order_items" database table
 *
 * @since 3.0
 */
final class Order_Items extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'order_items';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202102010;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807272' => 201807272,
		'201807273' => 201807273,
		'201906241' => 201906241,
		'202002141' => 202002141,
		'202102010' => 202102010,
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
			order_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(20) unsigned NOT NULL default '0',
			product_name text NOT NULL default '',
			price_id bigint(20) unsigned NOT NULL default '0',
			cart_index bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'download',
			status varchar(20) NOT NULL default 'pending',
			quantity int signed NOT NULL default '0',
			amount decimal(18,9) NOT NULL default '0',
			subtotal decimal(18,9) NOT NULL default '0',
			discount decimal(18,9) NOT NULL default '0',
			tax decimal(18,9) NOT NULL default '0',
			total decimal(18,9) NOT NULL default '0',
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY order_product_price_id (order_id,product_id,price_id),
			KEY type_status (type(20),status(20))";
	}

	/**
	 * Upgrade to version 201807272
	 * - Add the `date_modified` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807272() {

		// Look for column
		$result = $this->column_exists( 'date_created' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `date_created` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `total`;
			" );
		}

		// Look for column
		$result = $this->column_exists( 'date_modified' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `date_created`;
			" );
		}

		// Return success/fail
		return $this->is_success( $result );
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
	 * Upgrade to version 201906241
	 * - Make the quantity column signed so it can contain negative numbers.
	 * - Switch the quantity column from bigint to int for storage optimization.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __201906241() {
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY `quantity` int signed NOT NULL default '0';
		" );

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
	 * Upgrade to version 202102010.
	 *  - Change default value for `status` column to 'pending'.
	 *
	 * @return void
	 */
	protected function __202102010() {
		// Update `status`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `status` varchar(20) NOT NULL default 'pending';
		" );

		return $this->is_success( $result );
	}

}
