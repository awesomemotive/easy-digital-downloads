<?php

/**
 * Payment Items Table: EDD_DB_Table_Payment_Items class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the global "edd_payment_items" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Payment_Items extends WP_DB_Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'edd_payment_items';

	/**
	 * @var string Database version
	 */
	protected $version = 201801070001;

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
			payment_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(20) unsigned NOT NULL default '0',
			price_id bigint(20) unsigned NOT NULL default '0',
			cart_index bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'download',
			status varchar(20) NOT NULL default '',
			quantity bigint(20) unsigned NOT NULL default '0',
			amount double NOT NULL default '0',
			subtotal double NOT NULL default '0',
			discount double NOT NULL default '0',
			tax double NOT NULL default '0',
			total double NOT NULL default '0',
			PRIMARY KEY (id),
			KEY payment_product_price_id (payment_id,product_id,price_id),
			KEY type_status (type(20),status(20))";
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
