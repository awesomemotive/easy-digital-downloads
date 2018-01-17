<?php

/**
 * Order Items Table: EDD_DB_Table_Order_Items class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EDD_DB_Table' ) ) :
/**
 * Setup the global "edd_order_items" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Order_Items extends EDD_DB_Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var string
	 */
	protected $name = 'edd_order_items';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var int
	 */
	protected $version = 201801170001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			order_id bigint(20) unsigned NOT NULL default '0',
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
			KEY order_product_price_id (order_id,product_id,price_id),
			KEY type_status (type(20),status(20))";
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
