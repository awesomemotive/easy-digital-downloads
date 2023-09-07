<?php
/**
 * Adjustments Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * Adjustments Schema Class.
 *
 * @since 3.0
 */
final class Adjustments extends Schema {

	/**
	 * Array of database column objects.
	 *
	 * @since 3.0
	 * @var array
	 */
	public $columns = array(

		// id.
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		// parent.
		array(
			'name'       => 'parent',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true,
		),

		// name.
		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '200',
			'searchable' => true,
			'sortable'   => true,
		),

		// code.
		array(
			'name'       => 'code',
			'type'       => 'varchar',
			'length'     => '50',
			'searchable' => true,
			'sortable'   => true,
			'cache_key'  => true,
		),

		// status.
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'draft',
			'sortable'   => true,
			'transition' => true,
		),

		// type
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		// scope.
		array(
			'name'       => 'scope',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		// amount_type.
		array(
			'name'       => 'amount_type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		// amount.
		array(
			'name'     => 'amount',
			'type'     => 'decimal',
			'length'   => '18,9',
			'default'  => '0',
			'sortable' => true,
			'compare'  => true,
		),

		// description.
		array(
			'name'       => 'description',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
		),

		// max_uses.
		array(
			'name'       => 'max_uses',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'compare'  => true,
		),

		// use_count.
		array(
			'name'     => 'use_count',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
			'compare'  => true,
		),

		// once_per_customer.
		array(
			'name'    => 'once_per_customer',
			'type'    => 'int',
			'length'  => '1',
			'default' => '0',
		),

		// min_charge_amount.
		array(
			'name'    => 'min_charge_amount',
			'type'    => 'decimal',
			'length'  => '18,9',
			'default' => '0',
		),

		// start_date.
		array(
			'name'       => 'start_date',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// end_date.
		array(
			'name'       => 'end_date',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_created.
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '', // Defaults to current time in query class.
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_modified.
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '', // Defaults to current time in query class.
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// uuid.
		array(
			'uuid' => true,
		),
	);
}
