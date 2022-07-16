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
	protected $name = 'customer_email_addresses';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202207161;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'202207161' => 202207161,
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
	 * Upgrade to version 202207161
	 *  - Change default value to '0000-00-00 00:00:00' for columns `date_created` and `date_modified`.
	 *
	 * @since 3.0.2
	 * @return bool
	 */
	protected function __202207161() {

		// Update `date_created`.
		$this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_created` datetime NOT NULL default '0000-00-00 00:00:00'
		" );

		// Update `date_modified`.
		$this->get_db()->query( "
			ALTER TABLE {$this->table_name} MODIFY COLUMN `date_modified` datetime NOT NULL default '0000-00-00 00:00:00'
		" );

		return true;
	}
}
