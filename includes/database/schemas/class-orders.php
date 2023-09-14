<?php
/**
 * Orders Schema Class.
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
 * Orders Schema Class.
 *
 * @since 3.0
 */
class Orders extends Schema {

	/**
	 * Array of database column objects.
	 *
	 * @since 3.0
	 * @var   array
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
			'name'     => 'parent',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),

		// order_number.
		array(
			'name'       => 'order_number',
			'type'       => 'varchar',
			'length'     => '255',
			'searchable' => true,
			'sortable'   => true,
		),

		// status.
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'pending',
			'sortable'   => true,
			'transition' => true,
		),

		// type.
		array(
			'name'     => 'type',
			'type'     => 'varchar',
			'length'   => '20',
			'default'  => 'sale',
			'sortable' => true,
		),

		// user_id.
		array(
			'name'     => 'user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),

		// customer_id.
		array(
			'name'     => 'customer_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),

		// email.
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100',
			'searchable' => true,
			'sortable'   => true,
		),

		// ip.
		array(
			'name'     => 'ip',
			'type'     => 'varchar',
			'length'   => '60',
			'sortable' => true,
		),

		// gateway.
		array(
			'name'     => 'gateway',
			'type'     => 'varchar',
			'length'   => '100',
			'sortable' => true,
			'default'  => 'manual',
		),

		// mode.
		array(
			'name'   => 'mode',
			'type'   => 'varchar',
			'length' => '20',
		),

		// currency.
		array(
			'name'     => 'currency',
			'type'     => 'varchar',
			'length'   => '20',
			'validate' => 'strtoupper',
		),

		// payment_key.
		array(
			'name'       => 'payment_key',
			'type'       => 'varchar',
			'length'     => '64',
			'searchable' => true,
		),

		// tax_rate_id.
		array(
			'name'       => 'tax_rate_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => null,
			'allow_null' => true,
			'sortable'   => true,
		),

		// subtotal.
		array(
			'name'     => 'subtotal',
			'type'     => 'decimal',
			'length'   => '18,9',
			'default'  => '0',
			'sortable' => true,
			'validate' => 'edd_sanitize_amount',
		),

		// discount.
		array(
			'name'     => 'discount',
			'type'     => 'decimal',
			'length'   => '18,9',
			'default'  => '0',
			'sortable' => true,
			'validate' => 'edd_sanitize_amount',
		),

		// tax.
		array(
			'name'     => 'tax',
			'type'     => 'decimal',
			'length'   => '18,9',
			'default'  => '0',
			'sortable' => true,
			'validate' => 'edd_sanitize_amount',
		),

		// total.
		array(
			'name'     => 'total',
			'type'     => 'decimal',
			'length'   => '18,9',
			'default'  => '0',
			'sortable' => true,
			'validate' => 'edd_sanitize_amount',
		),

		// rate.
		array(
			'name'    => 'rate',
			'type'    => 'decimal',
			'length'  => '10,5',
			'default' => '1.00000',
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

		// date_completed.
		array(
			'name'       => 'date_completed',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_refundable.
		array(
			'name'       => 'date_refundable',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_actions_run.
		array(
			'name'       => 'date_actions_run',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// uuid.
		array(
			'uuid' => true,
		),
	);
}
