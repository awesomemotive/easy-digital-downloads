<?php
/**
 * Discounts Schema Class.
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
 * Discounts Schema Class.
 *
 * @since 3.0.0
 */
class Discounts extends Base {

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

		// name
		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '200',
			'searchable' => true,
			'sortable'   => true
		),

		// code
		array(
			'name'       => 'code',
			'type'       => 'varchar',
			'length'     => '50',
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

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// scope
		array(
			'name'       => 'scope',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// amount
		array(
			'name'       => 'amount',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'searchable' => true
		),

		// description
		array(
			'name'       => 'description',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true
		),

		// max_uses
		array(
			'name'       => 'max_uses',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// use_count
		array(
			'name'       => 'use_count',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0'
		),

		// once_per_customer
		array(
			'name'       => 'once_per_customer',
			'type'       => 'int',
			'length'     => '1',
			'default'    => '0'
		),

		// min_cart_price
		array(
			'name'       => 'min_cart_price',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0'
		),

		// product_condition
		array(
			'name'       => 'product_condition',
			'type'       => 'varchar',
			'length'     => '20'
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'searchable' => true,
			'sortable'   => true
		),

		// start_date
		array(
			'name'       => 'start_date',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'searchable' => true,
			'sortable'   => true
		),

		// end_date
		array(
			'name'       => 'end_date',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'searchable' => true,
			'sortable'   => true
		)
	);
}
