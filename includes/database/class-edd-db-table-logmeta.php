<?php

/**
 * Log Meta Table: EDD_DB_Table_Logmeta class
 *
 * @package Plugins/EDD/Database/Object/Meta
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the global "edd_logmeta" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Logmeta extends WP_DB_Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'edd_logmeta';

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
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_log_id bigint(20) unsigned NOT NULL default 0,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY log_id (log_id),
			KEY meta_key (meta_key({$max_index_length}))";
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
