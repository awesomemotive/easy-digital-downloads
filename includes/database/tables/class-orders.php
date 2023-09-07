<?php
/**
 * Orders Table.
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
 * Setup the global "edd_orders" database table
 *
 * @since 3.0
 */
final class Orders extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since  3.0
	 * @var    string
	 */
	protected $name = 'orders';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since  3.0
	 * @var    int
	 */
	protected $version = 202307111;

	/**
	 * Array of upgrade versions and methods.
	 *
	 * @access protected
	 * @since  3.0
	 * @var    array
	 */
	protected $upgrades = array(
		'201901111' => 201901111,
		'202002141' => 202002141,
		'202012041' => 202012041,
		'202102161' => 202102161,
		'202103261' => 202103261,
		'202105221' => 202105221,
		'202108041' => 202108041,
		'202302241' => 202302241,
		'202307111' => 202307111,
	);

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since  3.0
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "id bigint(20) unsigned NOT NULL auto_increment,
			parent bigint(20) unsigned NOT NULL default '0',
			order_number varchar(255) NOT NULL default '',
			status varchar(20) NOT NULL default 'pending',
			type varchar(20) NOT NULL default 'sale',
			user_id bigint(20) unsigned NOT NULL default '0',
			customer_id bigint(20) unsigned NOT NULL default '0',
			email varchar(100) NOT NULL default '',
			ip varchar(60) NOT NULL default '',
			gateway varchar(100) NOT NULL default 'manual',
			mode varchar(20) NOT NULL default '',
			currency varchar(20) NOT NULL default '',
			payment_key varchar(64) NOT NULL default '',
			tax_rate_id bigint(20) DEFAULT NULL,
			subtotal decimal(18,9) NOT NULL default '0',
			discount decimal(18,9) NOT NULL default '0',
			tax decimal(18,9) NOT NULL default '0',
			total decimal(18,9) NOT NULL default '0',
			rate decimal(10,5) NOT NULL DEFAULT 1.00000,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			date_completed datetime default null,
			date_refundable datetime default null,
			date_actions_run datetime default null,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY order_number (order_number({$max_index_length})),
			KEY status_type (status, type),
			KEY user_id (user_id),
			KEY customer_id (customer_id),
			KEY email (email(100)),
			KEY payment_key (payment_key(64)),
			KEY date_created_completed (date_created,date_completed),
			KEY currency (currency)";
	}

	/**
	 * Create the table
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function create() {

		$created = parent::create();

		// After successful creation, we need to set the auto_increment for legacy orders.
		if ( ! empty( $created ) ) {

			$last_payment_id = $this->get_db()->get_var( "SELECT ID FROM {$this->get_db()->prefix}posts WHERE post_type = 'edd_payment' ORDER BY ID DESC LIMIT 1;" );

			if ( ! empty( $last_payment_id ) ) {
				update_option( 'edd_v3_migration_pending', $last_payment_id, false );
				$auto_increment = $last_payment_id + 1;
				$this->get_db()->query( "ALTER TABLE {$this->table_name}  AUTO_INCREMENT = {$auto_increment};" );
			}

		}

		return $created;

	}

	/**
	 * Upgrade to version 201901111
	 * - Set any 'publish' status items to 'complete'.
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201901111() {
		$this->get_db()->query( "
			UPDATE {$this->table_name} set `status` = 'complete' WHERE `status` = 'publish';
		" );

		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 202002141
	 *  - Change default value to `CURRENT_TIMESTAMP` for columns `date_created` and `date_modified`.
	 *  - Change default value to `null` for columns `date_completed` and `date_refundable`.
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

		// Update `date_completed`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_completed` datetime default null;
		" );

		if ( $this->is_success( $result ) ) {
			$this->get_db()->query( "UPDATE {$this->table_name} SET `date_completed` = NULL WHERE `date_completed` = '0000-00-00 00:00:00'" );
		}

		// Update `date_refundable`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_refundable` datetime default null;
		" );

		if ( $this->is_success( $result ) ) {
			$this->get_db()->query( "UPDATE {$this->table_name} SET `date_refundable` = NULL WHERE `date_refundable` = '0000-00-00 00:00:00'" );
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 202012041
	 * 	- Add column `tax_rate_id`
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202012041() {
		// Look for column
		$result = $this->column_exists( 'tax_rate_id' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN tax_rate_id bigint(20) DEFAULT NULL AFTER payment_key;
			" );
		}

		// Return success/fail.
		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 202102161
	 * 	- Drop `status` index
	 * 	- Create new `status_type` index
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202102161() {
		if ( $this->index_exists( 'status' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX status" );
		}

		if ( ! $this->index_exists( 'status_type' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX status_type (status, type)" );
		}

		return true;
	}

	/**
	 * Upgrade to version 202103261
	 *  - Change length of `gateway` column to `100`.
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202103261() {
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `gateway` varchar(100) NOT NULL default '';
		" );

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
	 * Upgrade to version 202108041
	 * - Set any empty gateway items to 'manual'.
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __202108041() {
		$this->get_db()->query( "
			UPDATE {$this->table_name} set `gateway` = 'manual' WHERE `gateway` = '';
		" );

		$this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `gateway` varchar(100) NOT NULL default 'manual';
		" );

		return true;
	}

	/**
	 * Upgrade to version 202302241
	 * - Set an index for the 'currency' column as we use that in the admin frequenly.
	 *
	 * @since 3.1.1
	 *
	 * @return boolean
	 */
	protected function __202302241() {

		if ( ! $this->index_exists( 'currency' ) ) {
			$success = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX currency (currency)" );
		} else {
			$success = true;
		}

		return $this->is_success( $success );
	}

	protected function __202307111() {
		if ( ! $this->column_exists( 'date_actions_run' ) ) {
			return $this->is_success(
				$this->get_db()->query(
					"ALTER TABLE {$this->table_name} ADD COLUMN date_actions_run datetime default NULL AFTER date_refundable"
				)
			);
		}

		return true;
	}
}
