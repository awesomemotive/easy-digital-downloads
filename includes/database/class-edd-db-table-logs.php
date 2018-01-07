<?php

/**
 * Logs Table: EDD_DB_Table_Logs class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the global "edd_logs" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Logs extends WP_DB_Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'edd_logs';

	/**
	 * @var string Database version
	 */
	protected $version = 201801060001;

	/**
	 * @var boolean This is not a global table
	 */
	protected $global = false;

	/**
	 * Setup the database schema
	 *
	 * @since 3.0.0
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			object_type varchar(20) DEFAULT NULL,
			type varchar(30) DEFAULT NULL,
			title varchar(200) DEFAULT NULL,
			message longtext DEFAULT NULL,
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY object_id_type (object_id,object_type(20)),
			KEY type (type(30)),
			KEY date_created (date_created)";
	}

	/**
	 * Handle schema changes
	 *
	 * @since 3.0.0
	 */
	protected function upgrade() {

	}
}
endif;
