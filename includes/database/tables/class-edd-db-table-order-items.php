<?php
/**
 * Order Items Table.
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
 * Setup the global "edd_order_items" database table
 *
 * @since 3.0.0
 */
final class Order_Items extends Base {

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
			order_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(20) unsigned NOT NULL default '0',
			price_id bigint(20) unsigned NOT NULL default '0',
			cart_index bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'download',
			status varchar(20) NOT NULL default '',
			quantity bigint(20) unsigned NOT NULL default '0',
			amount decimal(18,9) NOT NULL default '0',
			subtotal decimal(18,9) NOT NULL default '0',
			discount decimal(18,9) NOT NULL default '0',
			tax decimal(18,9) NOT NULL default '0',
			total decimal(18,9) NOT NULL default '0',
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
