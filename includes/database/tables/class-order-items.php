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
	protected $version = 202110141;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201906241' => 201906241,
		'202002141' => 202002141,
		'202102010' => 202102010,
		'202103151' => 202103151,
		'202105221' => 202105221,
		'202110141' => 202110141,
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
			order_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(20) unsigned NOT NULL default '0',
			product_name text NOT NULL default '',
			price_id bigint(20) unsigned default null,
			cart_index bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'download',
			status varchar(20) NOT NULL default 'pending',
			quantity int signed NOT NULL default '0',
			amount decimal(18,9) NOT NULL default '0',
			subtotal decimal(18,9) NOT NULL default '0',
			discount decimal(18,9) NOT NULL default '0',
			tax decimal(18,9) NOT NULL default '0',
			total decimal(18,9) NOT NULL default '0',
			rate decimal(10,5) NOT NULL DEFAULT 1.00000,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY order_product_price_id (order_id,product_id,price_id),
			KEY type_status (type(20),status(20)),
			KEY parent (parent)";
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
	 * @return bool
	 */
	protected function __202102010() {
		// Update `status`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `status` varchar(20) NOT NULL default 'pending';
		" );

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

	/**
	 * Upgrade to version 202110141
	 *    - Change default value for `price_id` to `null`.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202110141() {
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN price_id bigint(20) unsigned default null;
		" );

		return $this->is_success( $result );
	}

}
