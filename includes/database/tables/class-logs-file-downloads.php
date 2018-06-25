<?php
/**
 * File Download Logs Table.
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
 * Setup the global "edd_logs_file_downloads" database table
 *
 * @since 3.0
 */
final class Logs_File_Downloads extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_logs_file_downloads';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201806250003;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201806250003' => 201806250003
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
		product_id bigint(20) unsigned NOT NULL default '0',
		file_id bigint(20) unsigned NOT NULL default '0',
		order_id bigint(20) unsigned NOT NULL default '0',
		price_id bigint(20) unsigned NOT NULL default '0',
		customer_id bigint(20) unsigned NOT NULL default '0',
		ip varchar(60) NOT NULL default '',
		user_agent varchar(200) NOT NULL default '',
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		date_modified datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY customer_id (customer_id),
		KEY product_id (product_id),
		KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 201806250003
	 * - Rename  `download_id` column to `product_id`
	 *
	 * (Might not work all as one query. Let's see?)
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201806250003() {

		// Alter the database
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} CHANGE COLUMN download_id product_id bigint(20) unsigned NOT NULL default 0;
			ALTER TABLE {$this->table_name} CHANGE COLUMN user_id customer_id bigint(20) unsigned NOT NULL default 0;
			ALTER TABLE {$this->table_name} CHANGE COLUMN payment_id order_id bigint(20) unsigned NOT NULL default 0;
			ALTER TABLE {$this->table_name} DROP INDEX download_id;
			ALTER TABLE {$this->table_name} ADD INDEX product_id (product_id);
		" );

		// Return success/fail
		return $this->is_success( $result );
	}
}
endif;