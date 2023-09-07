<?php
/**
 * Adjustments Table.
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
 * Setup the global "edd_adjustments" database table.
 *
 * @since 3.0
 */
final class Adjustments extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'adjustments';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202307311;

	/**
	 * Array of upgrade versions and methods.
	 *
	 * @access protected
	 * @since 3.0
	 * @var array
	 */
	protected $upgrades = array(
		'201906031' => 201906031,
		'202002121' => 202002121,
		'202102161' => 202102161,
		'202307311' => 202307311,
	);

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.0
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			parent bigint(20) unsigned NOT NULL default '0',
			name varchar(200) NOT NULL default '',
			code varchar(50) NOT NULL default '',
			status varchar(20) NOT NULL default '',
			type varchar(20) NOT NULL default '',
			scope varchar(20) NOT NULL default 'all',
			amount_type varchar(20) NOT NULL default '',
			amount decimal(18,9) NOT NULL default '0',
			description longtext NOT NULL default '',
			max_uses bigint(20) unsigned NOT NULL default '0',
			use_count bigint(20) unsigned NOT NULL default '0',
			once_per_customer int(1) NOT NULL default '0',
			min_charge_amount decimal(18,9) NOT NULL default '0',
			start_date datetime default null,
			end_date datetime default null,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY type_status (type(20), status(20)),
			KEY type_status_dates (type(20), status(20), start_date, end_date),
			KEY code (code),
			KEY date_created (date_created),
			KEY date_start_end (start_date,end_date)";
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

			$result = $this->get_db()->get_var( "SELECT ID FROM {$this->get_db()->prefix}posts WHERE post_type = 'edd_discount' ORDER BY ID DESC LIMIT 1;" );

			if ( ! empty( $result )  ) {
				$auto_increment = $result + 1;
				$this->get_db()->query( "ALTER TABLE {$this->table_name}  AUTO_INCREMENT = {$auto_increment};" );
			}

		}

		return $created;

	}

	/**
	 * Upgrade to version 201906031
	 * - Drop the `product_condition` column.
	 *
	 * @since 3.0
	 *
	 * @return boolean True if upgrade was successful, false otherwise.
	 */
	protected function __201906031() {

		// Look for column
		$result = $this->column_exists( 'product_condition' );

		// Maybe remove column
		if ( true === $result ) {

			// Try to remove it
			$result = ! $this->get_db()->query( "
				ALTER TABLE {$this->table_name} DROP COLUMN `product_condition`
			" );

			// Return success/fail
			return $this->is_success( $result );

		// Return true because column is already gone
		} else {
			return $this->is_success( true );
		}
	}

	/**
	 * Upgrade to version 202002121
	 *  - Change default value to `null` for columns `start_date` and `end_date`.
	 *  - Change default value to `CURRENT_TIMESTAMP` for columns `date_created` and `date_modified`.
	 *
	 * @return bool
	 */
	protected function __202002121() {

		// Update `start_date`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `start_date` datetime default null;
		" );

		if ( $this->is_success( $result ) ) {
			$this->get_db()->query( "UPDATE {$this->table_name} SET `start_date` = NULL WHERE `start_date` = '0000-00-00 00:00:00'" );
		}

		// Update `end_date`.
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `end_date` datetime default null;
		" );

		if ( $this->is_success( $result ) ) {
			$this->get_db()->query( "UPDATE {$this->table_name} SET `end_date` = NULL WHERE `end_date` = '0000-00-00 00:00:00'" );
		}

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
	 * Upgrade to version 202102161
	 * 	- Drop old `code_status_type_scope_amount` index
	 * 	- Create new `status_type` index
	 * 	- Create new `code` index
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202102161() {
		if ( $this->index_exists( 'code_status_type_scope_amount' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX code_status_type_scope_amount" );
		}

		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX type_status (type(20), status(20))" );
		$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX code (code)" );

		return true;
	}

	/**
	 * Upgrade to version 202307311
	 *
	 * 	- Create new `type_status_dates` index that includes the start and end dates.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	protected function __202307311() {
		if ( ! $this->index_exists( 'type_status_dates') ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX type_status_dates (type(20), status(20), start_date, end_date)" );
			return $this->is_success( $result );
		}

		return true;
	}
}
