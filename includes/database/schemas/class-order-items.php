<?php
/**
 * Order Items Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Schemas;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Order Items Schema Class.
 *
 * @since 3.0.0
 */
class Order_Items extends Base {

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

		// order_id
		array(
			'name'       => 'order_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// product_id
		array(
			'name'       => 'product_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// price_id
		array(
			'name'       => 'price_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// cart_index
		array(
			'name'       => 'cart_index',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'download',
			'sortable'   => true
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// quantity
		array(
			'name'       => 'quantity',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// amount
		array(
			'name'       => 'amount',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// subtotal
		array(
			'name'       => 'subtotal',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// discount
		array(
			'name'       => 'discount',
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

		// total
		array(
			'name'       => 'total',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		)
	);
}
