<?php
/**
 * Order Adjustments Table.
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
 * Setup the global "edd_order_adjustments" database table
 *
 * @since 3.0
 */
final class Order_Adjustments extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_order_adjustments';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201805220001;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
		object_id bigint(20) unsigned NOT NULL default '0',
		object_type varchar(20) DEFAULT NULL,
		type_id bigint(20) unsigned NOT NULL default '0',
		type varchar(20) DEFAULT NULL,
		description varchar(100) DEFAULT NULL,
		amount decimal(18,9) NOT NULL default '0',
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		date_modified datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY object_id_type (object_id,object_type(20)),
		KEY date_created (date_created)";
	}

	/**
	 * Handle schema changes
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function upgrade() {

	}
}
endif;