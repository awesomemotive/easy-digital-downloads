<?php

/**
 * Discount Meta Table: EDD_DB_Table_Discount_Meta class
 *
 * @package Plugins/EDD/Database/Object/Meta
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EDD_DB_Table' ) ) :
/**
 * Setup the global "edd_discountmeta" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Discount_Meta extends EDD_DB_Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var string
	 */
	protected $name = 'edd_discountmeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var int
	 */
	protected $version = 201801300001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_discount_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY edd_discount_id (edd_discount_id),
			KEY meta_key (meta_key({$max_index_length}))";
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
