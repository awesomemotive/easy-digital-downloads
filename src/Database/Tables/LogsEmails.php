<?php
/**
 * Email Logs Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.0
 */

namespace EDD\Database\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Setup the global "edd_logs" database table
 *
 * @since 3.3.0
 */
final class LogsEmails extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var string
	 */
	protected $name = 'logs_emails';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var int
	 */
	protected $version = 202311100;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.3.0
	 *
	 * @var array
	 */
	protected $upgrades = array();

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "
			id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			object_type varchar(20) NOT NULL DEFAULT 'customer',
			email varchar(100) NOT NULL default '',
			email_id varchar(32) NOT NULL,
			subject varchar(200) NOT NULL,
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY object_id_type (object_id,object_type(20)),
			KEY email_id (email_id),
			KEY date_created (date_created)
		";
	}
}
