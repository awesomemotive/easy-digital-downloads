<?php
/**
 * Orders Table.
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

/**
 * Setup the global "edd_orders" database table
 *
 * @since 3.0
 */
final class Orders extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_orders';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201806110001;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201806110001' => 201806110001
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "id bigint(20) unsigned NOT NULL auto_increment,
			parent bigint(20) unsigned NOT NULL default '0',
			order_number varchar(255) NOT NULL default '',
			status varchar(20) NOT NULL default 'pending',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			date_completed datetime NOT NULL default '0000-00-00 00:00:00',
			date_refundable datetime NOT NULL default '0000-00-00 00:00:00',
			user_id bigint(20) unsigned NOT NULL default '0',
			customer_id bigint(20) unsigned NOT NULL default '0',
			email varchar(100) NOT NULL default '',
			ip varchar(60) NOT NULL default '',
			gateway varchar(20) NOT NULL default '',
			mode varchar(20) NOT NULL default '',
			currency varchar(20) NOT NULL default '',
			payment_key varchar(64) NOT NULL default '',
			subtotal decimal(18,9) NOT NULL default '0',
			discount decimal(18,9) NOT NULL default '0',
			tax decimal(18,9) NOT NULL default '0',
			total decimal(18,9) NOT NULL default '0',
			PRIMARY KEY (id),
			KEY order_number (order_number({$max_index_length})),
			KEY status (status(20)),
			KEY user_id (user_id),
			KEY customer_id (customer_id),
			KEY email (email(100)),
			KEY payment_key (payment_key(64)),
			KEY date_created_completed (date_created,date_completed)";
	}

	/**
	 * Upgrade to version 201806110001
	 * - Add the `date_refundable` datetime column
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201806110001() {

		// Alter the database
		$result = $this->get_db()->query( "
			ALTER TABLE {$this->table_name} ADD COLUMN `date_refundable` datetime DEFAULT '0000-00-00 00:00:00';
		" );

		// Return success/fail
		return $this->is_success( $result );
	}
}