<?php
/**
 * Email Log Meta Table.
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
 * Setup the global "edd_logmeta" database table
 *
 * @since 3.3.0
 */
final class LogsEmailMeta extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var string
	 */
	protected $name = 'logs_emailmeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var int
	 */
	protected $version = 202311100;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.3.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_logs_email_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY edd_logs_email_id (edd_logs_email_id),
			KEY meta_key (meta_key({$max_index_length}))
		";
	}
}
