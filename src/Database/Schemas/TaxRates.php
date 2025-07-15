<?php
/**
 * TaxRates Schema Class.
 *
 * @package     EDD\Database\Schemas
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Schema;

/**
 * TaxRates Schema Class.
 *
 * @since 3.5.0
 */
class TaxRates extends Schema {

	/**
	 * Array of database column objects.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var array
	 */
	public $columns = array(
		array(
			'name'           => 'id',
			'type'           => 'bigint',
			'length'         => 20,
			'unsigned'       => true,
			'auto_increment' => true,
			'primary_key'    => true,
		),
		array(
			'name'    => 'country',
			'type'    => 'varchar',
			'length'  => 64,
			'default' => null,
		),
		array(
			'name'    => 'state',
			'type'    => 'varchar',
			'length'  => 64,
			'default' => null,
		),
		array(
			'name'       => 'amount',
			'type'       => 'decimal',
			'length'     => '18,9',
			'allow_null' => false,
			'default'    => 0,
		),
		array(
			'name'       => 'scope',
			'type'       => 'varchar',
			'length'     => 20,
			'allow_null' => false,
			'default'    => 'country',
		),
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => 20,
			'allow_null' => false,
			'default'    => 'active',
		),
		array(
			'name'       => 'source',
			'type'       => 'varchar',
			'length'     => 20,
			'default'    => 'manual',
			'allow_null' => false,
		),
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'allow_null' => false,
			'default'    => '',
			'created'    => true,
		),
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'allow_null' => false,
			'default'    => '',
			'modified'   => true,
		),
	);
}
