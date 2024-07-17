<?php
/**
 * Emails Table.
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
 * Setup the emails database table.
 *
 * @since 3.1.1
 */
final class Emails extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @var string
	 */
	protected $name = 'emails';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @var int
	 */
	protected $version = 202310270;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.1.1
	 *
	 * @var array
	 */
	protected $upgrades = array();

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		email_id varchar(32) NOT NULL,
		context varchar(32) NOT NULL DEFAULT 'order',
		sender varchar(32) NOT NULL DEFAULT 'edd',
		recipient varchar(32) NOT NULL DEFAULT 'customer',
		subject text NOT NULL,
		heading text DEFAULT NULL,
		content longtext NOT NULL,
		status tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
		date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY email_id (email_id)";
	}
}
