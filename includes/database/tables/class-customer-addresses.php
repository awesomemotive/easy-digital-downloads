<?php
/**
 * Customer Addresses Table.
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
 * Setup the global "edd_customer_addresses" database table
 *
 * @since 3.0
 */
final class Customer_Addresses extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'customer_addresses';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202004051;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201906251' => 201906251,
		'202002141' => 202002141,
		'202004051' => 202004051,
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL default '0',
			is_primary tinyint(1) signed NOT NULL default '0',
			type varchar(20) NOT NULL default 'billing',
			status varchar(20) NOT NULL default 'active',
			name mediumtext NOT NULL,
			address mediumtext NOT NULL,
			address2 mediumtext NOT NULL,
			city mediumtext NOT NULL,
			region mediumtext NOT NULL,
			postal_code varchar(32) NOT NULL default '',
			country mediumtext NOT NULL,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY customer_is_primary (customer_id, is_primary),
			KEY type (type(20)),
			KEY status (status(20)),
			KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201906251
	 * - Add the `name` mediumtext column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201906251() {

		$result = $this->column_exists( 'name' );

		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `name` mediumtext AFTER `status`;
			" );
		}

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
	 * Upgrade to version 202004051
	 * - Update the customer physical address table to have `is_primary`
	 *
	 * @since 3.0
	 * @return bool
	 */
	protected function __202004051() {

		$result = $this->column_exists( 'is_primary' );
		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `is_primary` tinyint SIGNED NOT NULL default '0' AFTER `customer_id`;
			" );
		}

		return $this->is_success( $result );
	}

}
