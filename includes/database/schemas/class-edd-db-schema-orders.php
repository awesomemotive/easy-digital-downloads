<?php

/**
 * Orders: EDD_DB_Schema class
 *
 * @package Plugins/EDD/Database/Schema/Orders
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Schema_Orders extends EDD_DB_Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true
		),

		// order_number
		array(
			'name'       => 'order_number',
			'type'       => 'varchar',
			'length'     => '255',
			'searchable' => true,
			'sortable'   => true
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'pending',
			'searchable' => true,
			'sortable'   => true
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// date_completed
		array(
			'name'       => 'date_completed',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// user_id
		array(
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// customer_id
		array(
			'name'       => 'customer_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// email
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100'
		),

		// ip
		array(
			'name'       => 'ip',
			'type'       => 'varchar',
			'length'     => '60'
		),

		// gateway
		array(
			'name'       => 'gateway',
			'type'       => 'varchar',
			'length'     => '20'
		),

		// payment_key
		array(
			'name'       => 'payment_key',
			'type'       => 'varchar',
			'length'     => '64'
		),

		// subtotal
		array(
			'name'       => 'subtotal',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// tax
		array(
			'name'       => 'tax',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// discounts
		array(
			'name'       => 'discount',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// total
		array(
			'name'       => 'total',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		)
	);
}
