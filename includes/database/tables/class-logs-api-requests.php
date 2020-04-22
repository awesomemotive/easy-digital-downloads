<?php
/**
 * API Request Logs Table.
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
 * Setup the global "edd_logs_api_requests" database table
 *
 * @since 3.0
 */
final class Logs_Api_Requests extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'logs_api_requests';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 202002141;

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
		'202002141' => 202002141,
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
		user_id bigint(20) unsigned NOT NULL default '0',
		api_key varchar(32) NOT NULL default 'public',
		token varchar(32) NOT NULL default '',
		version varchar(32) NOT NULL default '',
		request longtext NOT NULL default '',
		error longtext NOT NULL default '',
		ip varchar(60) NOT NULL default '',
		time varchar(60) NOT NULL default '',
		date_created datetime NOT NULL default CURRENT_TIMESTAMP,
		date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201807273
	 * - Add the `date_modified` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807272() {

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
	 * Upgrade to version 201807272
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

}
