<?php

/**
 * Log API Requests: EDD_DB_Schema class
 *
 * @package Plugins/EDD/Database/Schema/Logs/Requests
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class EDD_DB_Schema_Logs_Api_Requests extends EDD_DB_Schema {

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

		// api_key
		array(
			'name'       => 'api_key',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => 'public',
			'sortable'   => true
		),

		// token
		array(
			'name'       => 'token',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
			'sortable'   => true
		),

		// version
		array(
			'name'       => 'version',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
			'sortable'   => true
		),

		// request
		array(
			'name'       => 'request',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
			'in'         => false,
			'not_in'     => false
		),

		// error
		array(
			'name'       => 'error',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
			'in'         => false,
			'not_in'     => false
		),

		// ip
		array(
			'name'       => 'ip',
			'type'       => 'varchar',
			'length'     => '60',
			'default'    => '',
			'sortable'   => true
		),

		// time
		array(
			'name'       => 'time',
			'type'       => 'varchar',
			'length'     => '60',
			'default'    => '',
			'sortable'   => true
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
