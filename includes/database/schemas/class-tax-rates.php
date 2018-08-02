<?php
/**
 * Tax Rates Schema Class.
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
 * Tax Rates Schema Class.
 *
 * @since 3.0
 */
class Tax_Rates extends Base {

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

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'active',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true
		),

		// country
		array(
			'name'       => 'country',
			'type'       => 'varchar',
			'length'     => '200',
			'searchable' => true,
			'sortable'   => true
		),

		// region
		array(
			'name'       => 'region',
			'type'       => 'varchar',
			'length'     => '200',
			'searchable' => true,
			'sortable'   => true
		),

		// scope
		array(
			'name'       => 'scope',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'region',
			'searchable' => true,
			'sortable'   => true
		),

		// rate
		array(
			'name'       => 'rate',
			'type'       => 'decimal',
			'length'     => '18,9',
			'default'    => '0',
			'validate'   => 'edd_sanitize_amount',
			'searchable' => true
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
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'searchable' => true,
			'sortable'   => true
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'searchable' => true,
			'sortable'   => true
		),

		// uuid
		array(
			'uuid'       => true,
		)
	);
}
