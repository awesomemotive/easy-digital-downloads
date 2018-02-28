<?php

/**
 * Customers Table: EDD_DB_Table_Customers class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EDD_DB_Table' ) ) :
/**
 * Setup the global "edd_customers" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Customers extends EDD_DB_Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var string
	 */
	protected $name = 'edd_customers';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var int
	 */
	protected $version = 201802120001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			email varchar(50) NOT NULL,
			name mediumtext NOT NULL,
			status varchar(20) NOT NULL default '',
			purchase_value mediumtext NOT NULL,
			purchase_count bigint(20) NOT NULL,
			payment_ids longtext NOT NULL,
			notes longtext NOT NULL,
			date_created datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY user (user_id),
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
