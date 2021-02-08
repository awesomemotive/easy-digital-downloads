<?php
/**
 * API Request Logs Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * API Request Logs Schema Class.
 *
 * @since 3.0
 */
class Logs_Api_Requests extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 3.0
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
			'default'    => '0',
			'sortable'   => true
		),

		// api_key
		array(
			'name'       => 'api_key',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => 'public',
			'searchable' => true,
			'sortable'   => true
		),

		// token
		array(
			'name'       => 'token',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
			'searchable' => true,
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
			'searchable' => true,
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
			'default'    => '', // Defaults to current time in query class
			'created'    => true,
			'date_query' => true,
			'sortable'   => true
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '', // Defaults to current time in query class
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true
		),

		// uuid
		array(
			'uuid'       => true,
		)
	);
}
