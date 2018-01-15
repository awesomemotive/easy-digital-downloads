<?php

/**
 * Orders Table: EDD_DB_Table_Orders class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the global "edd_orders" database table
 *
 * @since 3.0.0
 */
final class EDD_DB_Table_Orders extends WP_DB_Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'edd_orders';

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
			number tinytext NOT NULL default '',
			status varchar(20) NOT NULL default 'pending',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_completed datetime NOT NULL default '0000-00-00 00:00:00',
			user_id bigint(20) unsigned NOT NULL default '0',
			customer_id bigint(20) unsigned NOT NULL default '0',
			email varchar(100) NOT NULL default '',
			ip varchar(60) NOT NULL default '',
			gateway varchar(20) NOT NULL default '',
			payment_key varchar(64) NOT NULL default '',
			subtotal double NOT NULL default '0',
			tax double NOT NULL default '0',
			discounts double NOT NULL default '0',
			total double NOT NULL default '0',
			PRIMARY KEY (id),
			KEY number (number),
			KEY status (status(20)),
			KEY user_id (user_id),
			KEY customer_id (customer_id),
			KEY email (email(100)),
			KEY ip (ip(60)),
			KEY order_key (order_key(64)),
			KEY date_created_completed (date_created,date_completed)";
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
