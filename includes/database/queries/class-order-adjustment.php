<?php
/**
 * Order Adjustment Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Query;

/**
 * Class used for querying order order adjustments.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order_Adjustment::__construct() for accepted arguments.
 */
class Order_Adjustment extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'order_adjustments';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'oa';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Order_Adjustments';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'order_adjustment';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'order_adjustments';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Orders\\Order_Adjustment';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'order_adjustments';

	/** Methods ***************************************************************/

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An order adjustment ID to only return that item. Default empty.
	 *     @type array        $id__in               Array of order adjustment IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of order adjustment IDs to exclude. Default empty.
	 *     @type int          $parent               A parent ID to only return items with that parent. Default empty.
	 *     @type array        $parent__in           An array of parent IDs to include. Default empty.
	 *     @type array        $parent__not_in       An array of parent IDs to exclude. Default empty.
	 *     @type string       $object_id            An object ID to only return those objects. Default empty.
	 *     @type array        $object_id__in        Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in    Array of IDs object to exclude. Default empty.
	 *     @type string       $object_type          An object types to only return that type. Default empty.
	 *     @type array        $object_type__in      Array of object types to include. Default empty.
	 *     @type array        $object_type__not_in  Array of object types to exclude. Default empty.
	 *     @type string       $type_id              A type ID to only return that type. Default empty.
	 *     @type array        $type_id__in          Array of types IDs to include. Default empty.
	 *     @type array        $type_id__not_in      Array of types IDS to exclude. Default empty.
	 *     @type string       $type                 A type to only return that type. Default empty.
	 *     @type array        $type__in             Array of types to include. Default empty.
	 *     @type array        $type__not_in         Array of types to exclude. Default empty.
	 *     @type string       $type_key             Filter by type key. Default empty.
	 *     @type array        $type_key__in         An array of type keys to include. Default empty.
	 *     @type array        $type_key__not_in     An array of type keys to exclude. Default empty.
	 *     @type float        $tax                  Limit results to those with a specific tax amount. Default empty.
	 *     @type array        $tax__in              Array of tax amounts to include. Default empty.
	 *     @type array        $tax__not_in          Array of tax amounts to exclude. Default empty.
	 *     @type float        $subtotal             Limit results to those affiliated with a given subtotal. Default empty.
	 *     @type array        $subtotal__in         Array of subtotal amounts to include. Default empty.
	 *     @type array        $subtotal__not_in     Array of subtotal amounts to exclude. Default empty.
	 *     @type float        $total                Limit results to those affiliated with a given total. Default empty.
	 *     @type array        $total__in            Array of totals to include. Default empty.
	 *     @type array        $total__not_in        Array of totals to exclude. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return an item count (true) or array of item objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete item objects). Default empty.
	 *     @type int          $number               Limit number of order adjustments to retrieve. Default 100.
	 *     @type int          $offset               Number of order adjustments to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'object_id', 'object_type', 'type_id', 'type',
	 *                                              'type_key', 'subtotal', 'tax', 'total', 'date_created', 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching order adjustments for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found order adjustments. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
