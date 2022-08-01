<?php
/**
 * Order Transactions Schema Class.
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
 * Order Transactions Schema Class.
 *
 * @since 3.0
 */
class Order_Transactions extends Schema {

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

		// object_id
		array(
			'name'       => 'object_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// object_type
		array(
			'name'       => 'object_type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// transaction_id
		array(
			'name'       => 'transaction_id',
			'type'       => 'varchar',
			'length'     => '256',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true
		),

		// gateway
		array(
			'name'       => 'gateway',
			'type'       => 'varchar',
			'length'     => '20',
			'sortable'   => true,
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'pending',
			'sortable'   => true,
			'transition' => true
		),

		// total
		array(
			'name'       => 'total',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'sortable'   => true
		),

		// rate
		array(
			'name'       => 'rate',
			'type'       => 'decimal',
			'length'     => '10,5',
			'default'    => '1.00000',
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
