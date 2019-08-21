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
	protected $version = 201908200001;

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
		'201908200001' => 201908200001,
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
			type varchar(20) NOT NULL default 'billing',
			is_primary tinyint(1) UNSIGNED NOT NULL default '0',
			status varchar(20) NOT NULL default 'active',
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
			KEY customer (customer_id),
			KEY type (type(20)),
			KEY status (status(20)),
			KEY date_created (date_created),
			KEY customer_primary (customer_id,type(20),is_primary)";
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
	 * - Add the `name` mediumtext column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201906250001() {

		$result = $this->column_exists( 'name' );

		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `name` mediumtext AFTER `status`;
			" );
		}

		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 201908200001
	 * - Add the `is_primary` tinyint column
	 * - Add the index for customer_id, type, and primary
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __201908200001() {
		$result = $this->column_exists( 'is_primary' );

		if ( false === $result ) {
			$result = $this->get_db()->query( "
				ALTER TABLE {$this->table_name} ADD COLUMN `is_primary` tinyint UNSIGNED DEFAULT '0' AFTER `type`;
			" );

			if ( false !== $result ) {
				$index_result = $this->get_db()->query( "
					CREATE INDEX customer_primary ON {$this->table_name}(customer_id, type(20), is_primary);
				" );
			}
		}

		return $this->is_success( $result );
	}
}
