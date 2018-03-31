<?php

/**
 * File Download Logs Table: EDD_DB_Table_Logs_File_Downloads class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EDD_DB_Table' ) ) :
	/**
	 * Setup the global "edd_logs_file_downloads" database table
	 *
	 * @since 3.0.0
	 */
	final class EDD_DB_Table_Logs_File_Downloads extends EDD_DB_Table {

		/**
		 * Table name
		 *
		 * @access protected
		 * @since 3.0.0
		 * @var string
		 */
		protected $name = 'edd_logs_file_downloads';

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
			download_id bigint(20) unsigned NOT NULL default '0',
			file_id bigint(20) unsigned NOT NULL default '0',
			payment_id bigint(20) unsigned NOT NULL default '0',
			price_id bigint(20) unsigned NOT NULL default '0',
			user_id bigint(20) unsigned NOT NULL default '0',
			ip varchar(60) NOT NULL default '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY download_id (download_id),
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