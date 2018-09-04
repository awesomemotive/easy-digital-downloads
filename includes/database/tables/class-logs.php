<?php
/**
 * Logs Table.
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
 * Setup the global "edd_logs" database table
 *
 * @since 3.0
 */
final class Logs extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_logs';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201807270003;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807240001' => 201807240001,
		'201807270002' => 201807270002,
		'201807270003' => 201807270003,
	);

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) DEFAULT NULL,
		user_id bigint(20) unsigned NOT NULL default '0',
		type varchar(20) DEFAULT NULL,
		title varchar(200) DEFAULT NULL,
		content longtext DEFAULT NULL,
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		date_modified datetime NOT NULL default '0000-00-00 00:00:00',
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY object_id_type (object_id,object_type(20)),
		KEY user_id (user_id),
		KEY type (type(20)),
		KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201807230001
	 * - Add `user_id` column.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function __201807240001() {

		// Alter the database
		if ( ! $this->column_exists( 'user_id' ) ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN user_id bigint(20) unsigned NOT NULL default '0' AFTER object_type" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX user_id (user_id)" );
		}

		// Return success/fail
		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 201807270002
	 * - Add the `date_modified` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807270002() {

		// Look for column
		$result = $this->column_exists( 'date_modified' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `date_created`;
			"
			);
		}

		// Return success/fail
		return $this->is_success( $result );
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
			$result = $this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;
			"
			);
		}

		// Return success/fail
		return $this->is_success( $result );
	}
}
