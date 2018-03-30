<?php

/**
 * Customers: EDD_DB_Schema class
 *
 * @package Plugins/EDD/Database/Schema/Customers
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Schema_Customers extends EDD_DB_Schema {

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

		// user_id
		array(
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// email
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100',
			'searchable' => true,
			'sortable'   => true
		),

		// name
		array(
			'name'       => 'name',
			'type'       => 'mediumtext',
			'searchable' => true,
			'sortable'   => true
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true
		),

		// purchase_value
		array(
			'name'       => 'purchase_value',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// purchase_count
		array(
			'name'       => 'purchase_count',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// payment_ids
		array(
			'name'       => 'payment_ids',
			'type'       => 'longtext',
			'searchable' => false,
			'sortable'   => false,
			'in'         => false,
			'not_in'     => false
		),

		// notes
		array(
			'name'       => 'notes',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => false,
			'sortable'   => false,
			'in'         => false,
			'not_in'     => false
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		)
	);
}
