<?php
/**
 * Logs Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Setup the global "edd_logs" database table
 *
 * @since 3.0
 */
final class Logs extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_logs';

	/**
	 * Database version
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
		'201807270003' => 201807270003,
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
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) DEFAULT NULL,
		type varchar(20) DEFAULT NULL,
		title varchar(200) DEFAULT NULL,
		content longtext DEFAULT NULL,
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		date_modified datetime NOT NULL default '0000-00-00 00:00:00',
		uuid varchar(100) NOT NULL default '',
		PRIMARY KEY (id),
		KEY object_id_type (object_id,object_type(20)),
		KEY type (type(20)),
		KEY date_created (date_created)";
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

		// Alter the database
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;
		" );

		// Return success/fail
		return $this->is_success( $result );
	}
}