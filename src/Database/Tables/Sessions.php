<?php
/**
 * Sessions Table Class.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2023, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.0
 */

namespace EDD\Database\Tables;

defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Class Sessions
 *
 * @since 3.3.0
 * @package EDD\Database\Tables
 */
class Sessions extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var string
	 */
	protected $name = 'sessions';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.3.0
	 * @var int
	 */
	protected $version = 202311090;

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
		$this->schema = '
			session_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_key varchar(64) NOT NULL,
			session_value longtext NOT NULL,
			session_expiry bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (session_id),
			KEY session_key (session_key),
			KEY session_expiry (session_expiry)
		';
	}
}
