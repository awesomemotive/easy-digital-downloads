<?php

/**
 * Notes Table: EDD_DB_Table_Notes class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the global "edd_notes" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Notes extends WP_DB_Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'edd_notes';

	/**
	 * @var string Database version
	 */
	protected $version = 201801150001;

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
			object_type varchar(20) NOT NULL default '',
			author_id bigint(20) unsigned NOT NULL default '0',
			author_ip varchar(60) NOT NULL default '',
			content longtext NOT NULL default '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY object_id_type (object_id,object_type(20)),
			KEY author (author_id,author_ip(60)),
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
