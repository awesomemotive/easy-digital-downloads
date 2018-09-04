<?php
/**
 * Customer Email Addresses Table.
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
 * Setup the global "edd_customer_email_addresses" database table
 *
 * @since 3.0
 */
final class Customer_Email_Addresses extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_customer_email_addresses';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201808170001;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201808140001' => 201808140001,
		'201808170001' => 201808170001,
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
			type varchar(20) NOT NULL default 'secondary',
			status varchar(20) NOT NULL default 'active',
			email varchar(100) NOT NULL default '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY customer (customer_id),
			KEY email (email),
			KEY type (type(20)),
			KEY status (status(20)),
			KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201808140001
	 * - Add the `uuid` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201808140001() {

		// Look for column
		$result = $this->column_exists( 'uuid' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;
			"
			);
		}

		// Return success/fail
		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 201808170001
	 * - Add the `email` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201808170001() {
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY COLUMN `email` varchar(100) NOT NULL default ''" );
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX email (email)" );

		// Return success/fail
		return $this->is_success( $result );
	}
}
