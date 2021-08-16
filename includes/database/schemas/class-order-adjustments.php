<?php
/**
 * Order Adjustments Schema Class.
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
 * Order Adjustments Schema Class.
 *
 * @since 3.0
 */
class Order_Adjustments extends Schema {

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

		// parent
		array(
			'name'       => 'parent',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
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

		// type_id
		array(
			'name'       => 'type_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true,
			'transition' => true
		),

		// type key
		array(
			'name'       => 'type_key',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'sortable'   => true,
		),

		// description
		array(
			'name'       => 'description',
			'type'       => 'varchar',
			'length'     => '100',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true
		),

		// subtotal
		array(
			'name'       => 'subtotal',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'sortable'   => true,
			'validate'   => 'edd_sanitize_amount'
		),

		// tax
		array(
			'name'       => 'tax',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'sortable'   => true,
			'validate'   => 'edd_sanitize_amount'
		),

		// total
		array(
			'name'       => 'total',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'sortable'   => true,
			'validate'   => 'edd_sanitize_amount'
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
