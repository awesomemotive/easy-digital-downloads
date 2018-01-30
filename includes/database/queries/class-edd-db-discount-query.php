<?php

/**
 * Orders: EDD_Discount_Query class
 *
 * @package Plugins/EDD/Database/Queries/Discounts
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying discounts.
 *
 * @since 3.0.0
 *
 * @see EDD_Discount_Query::__construct() for accepted arguments.
 */
class EDD_Discount_Query extends EDD_DB_Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_name = 'edd_discounts';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_alias = 'd';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name = 'discount';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name_plural = 'discounts';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed
	 */
	public $single_item_callback = 'edd_get_discount';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $cache_group = 'discounts';

	/** Columns ***************************************************************/

	/**
	 * Array of database column objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $columns = array(

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
			'default'    => '0'
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
			'sortable'   => true
		),

		// start_date
		array(
			'name'       => 'start_date',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'sortable'   => true
		),

		// end_date
		array(
			'name'       => 'end_date',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'sortable'   => true
		)
	);

	/** Methods ***************************************************************/

	/**
	 * Sets up the discount query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of discount query parameters. Default empty.
	 *
	 *     @type int          $id                   An discount ID to only return that discount. Default empty.
	 *     @type array        $id__in               Array of discount IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of discount IDs to exclude. Default empty.
	 *     @type array        $date_created_query   Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $start_date_query     Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $end_date_query       Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a discount count (true) or array of discount objects.
	 *                                              Default false.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of discount IDs)
	 *                                              or empty (returns an array of complete discount objects). Default empty.
	 *     @type int          $limit                Limit number of discounts to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of discounts to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to discount retrieved discounts. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type string       $search               Search term(s) to retrieve matching discounts for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found discounts. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
