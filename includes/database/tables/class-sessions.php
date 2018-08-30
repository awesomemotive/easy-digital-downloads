<?php
/**
 * Sessions Table.
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
 * Setup the global "edd_sessions" database table
 *
 * @since 3.0
 */
final class Sessions extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_sessions';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201808300001;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array();

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			hash varchar(100) NOT NULL default '',
			content longtext NOT NULL default '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			date_expires datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY hash (hash(100)),
			KEY date_created (date_created),
			KEY date_modified (date_modified),
			KEY date_expires (date_expires)";
	}
}