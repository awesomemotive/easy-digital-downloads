<?php
/**
 * API Request Logs Table.
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

if ( class_exists( '\\EDD\\Database\\Tables\\Base' ) ) :
/**
 * Setup the global "edd_logs_api_requests" database table
 *
 * @since 3.0.0
 */
final class Logs_Api_Requests extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var string
	 */
	protected $name = 'edd_logs_api_requests';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var int
	 */
	protected $version = 201802280001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0.0
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
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY date_created (date_created)";
	}

	/**
	 * Handle schema changes
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function upgrade() {

	}
}
endif;