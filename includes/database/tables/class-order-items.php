<?php
/**
 * Order Items Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Setup the global "edd_order_items" database table
 *
 * @since 3.0
 */
final class Order_Items extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_order_items';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201807270003;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201807270002' => 201807270002,
		'201807270003' => 201807270003,
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			order_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(20) unsigned NOT NULL default '0',
			product_name text NOT NULL default '',
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
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY order_product_price_id (order_id,product_id,price_id),
			KEY type_status (type(20),status(20))";
	}

	/**
	 * Upgrade to version 201807270002
	 * - Add the `date_modified` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807270002() {

		// Look for column
		$result = $this->column_exists( 'date_created' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `date_created` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `total`;
			"
			);
		}

		// Look for column
		$result = $this->column_exists( 'date_modified' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `date_modified` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `date_created`;
			"
			);
		}

		// Return success/fail
		return $this->is_success( $result );
	}

	/**
	 * Upgrade to version 201807270003
	 * - Add the `uuid` varchar column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201807270003() {

		// Look for column
		$result = $this->column_exists( 'uuid' );

		// Maybe add column
		if ( false === $result ) {
			$result = $this->get_db()->query(
				"
				ALTER TABLE {$this->table_name} ADD COLUMN `uuid` varchar(100) default '' AFTER `date_modified`;
			"
			);
		}

		// Return success/fail
		return $this->is_success( $result );
	}
}
