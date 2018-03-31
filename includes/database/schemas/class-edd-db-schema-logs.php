<?php

/**
 * Logs: EDD_DB_Schema class
 *
 * @package Plugins/EDD/Database/Schema/Logs
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Schema_Logs extends EDD_DB_Schema {

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
			'searchable' => true,
			'sortable'   => true
		),

		// object_id
		array(
			'name'       => 'object_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'searchable' => true,
			'sortable'   => true
		),

		// object_type
		array(
			'name'       => 'object_type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true
		),

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '30',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true
		),

		// title
		array(
			'name'       => 'title',
			'type'       => 'varchar',
			'length'     => '200',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
			'in'         => false,
			'not_in'     => false
		),

		// content
		array(
			'name'       => 'content',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
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
