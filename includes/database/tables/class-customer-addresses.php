<?php
/**
 * Customer Addresses Table.
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
 * Setup the global "edd_customer_addresses" database table
 *
 * @since 3.0
 */
final class Customer_Addresses extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_customer_addresses';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201807010001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default 'billing',
			status varchar(20) NOT NULL default 'active',
			address mediumtext NOT NULL,
			address2 mediumtext NOT NULL,
			city mediumtext NOT NULL,
			region mediumtext NOT NULL,
			postal_code varchar(32) NOT NULL default '',
			country mediumtext NOT NULL,
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY customer (customer_id),
			KEY type (type(20)),
			KEY status (status(20)),
			KEY date_created (date_created)";
	}
}
endif;