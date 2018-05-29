<?php
/**
 * Customers Table.
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
 * Setup the global "edd_customers" database table
 *
 * @since 3.0
 */
final class Customers extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_customers';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201805290002;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			email varchar(100) NOT NULL,
			name mediumtext NOT NULL,
			status varchar(20) NOT NULL default '',
			purchase_value mediumtext NOT NULL,
			purchase_count bigint(20) NOT NULL,
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY user (user_id),
			KEY status (status(20)),
			KEY date_created (date_created)";
	}

	/**
	 * Handle schema changes
	 *
	 * @access protected
	 * @since 3.0
	 */
	protected function upgrade() {
		$query = "
			ALTER TABLE {$this->table_name} MODIFY `email` VARCHAR(100) NOT NULL default '';
			ALTER TABLE {$this->table_name} MODIFY `user_id` bigint(20) unsigned NOT NULL default '0';
			ALTER TABLE {$this->table_name} MODIFY `purchase_count` bigint(20) unsigned NOT NULL default '0'
			ALTER TABLE {$this->table_name} ALTER COLUMN `date_created` SET DEFAULT '0000-00-00 00:00:00';
			ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime DEFAULT '0000-00-00 00:00:00';
		";
		$this->get_db()->query( $query );
	}
}
endif;