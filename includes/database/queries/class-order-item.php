<?php
/**
 * Order Item Query Class.
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
 * Class used for querying order items.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order_Item::__construct() for accepted arguments.
 */
class Order_Item extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'order_items';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'oi';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Order_Items';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'order_item';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'order_items';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Orders\\Order_Item';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'order_items';

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
	 *     @type int          $id                   An order item ID to only return that item. Default empty.
	 *     @type array        $id__in               Array of order item IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of order item IDs to exclude. Default empty.
	 *     @type int          $parent               A parent ID to only return items with that parent. Default empty.
	 *     @type array        $parent__in           An array of parent IDs to include. Default empty.
	 *     @type array        $parent__not_in       An array of parent IDs to exclude. Default empty.
	 *     @type int          $order_id             An order ID to only return those order items. Default empty.
	 *     @type array        $order_id__in         Array of order IDs to include. Default empty.
	 *     @type array        $order_id__not_in     Array of order IDs to exclude. Default empty.
	 *     @type int          $product_id           A product ID to only return those products. Default empty.
	 *     @type array        $product_id__in       Array of product IDs to include. Default empty.
	 *     @type array        $product_id__not_in   Array of product IDs to exclude. Default empty.
	 *     @type string       $product_name         A product name to filter by. Default empty.
	 *     @type array        $product_name__in     An array of product names to include. Default empty.
	 *     @type array        $product_name__not_in An array of product names to exclude. Default empty.
	 *     @type int          $price_id             A price ID to only return that price. Default empty.
	 *     @type array        $price_id__in         Array of price IDs to include. Default empty.
	 *     @type array        $price_id__not_in     Array of price IDs to exclude. Default empty.
	 *     @type int          $cart_index           A cart index to only return that index. Default empty.
	 *     @type array        $cart_index__in       Array of cart index to include. Default empty.
	 *     @type array        $cart_index__not_in   Array of cart index to exclude. Default empty.
	 *     @type string       $type                 A product type to only return that type. Default empty.
	 *     @type array        $type__in             Array of product types to include. Default empty.
	 *     @type array        $type__not_in         Array of product types to exclude. Default empty.
	 *     @type string       $status               An order statuses to only return that status. Default empty.
	 *     @type array        $status__in           Array of order statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of order statuses to exclude. Default empty.
	 *     @type int          $quantity             A quantity to only return those quantities. Default empty.
	 *     @type array        $quantity__in         Array of quantities to include. Default empty.
	 *     @type array        $quantity__not_in     Array of quantities to exclude. Default empty.
	 *     @type float        $amount               Limit results to those affiliated with a given amount. Default empty.
	 *     @type array        $amount__in           Array of amounts to include affiliated order items for. Default empty.
	 *     @type array        $amount__not_in       Array of amounts to exclude affiliated order items for. Default empty.
	 *     @type float        $subtotal             Limit results to those affiliated with a given subtotal. Default empty.
	 *     @type array        $subtotal__in         Array of subtotals to include affiliated order items for. Default empty.
	 *     @type array        $subtotal__not_in     Array of subtotals to exclude affiliated order items for. Default empty.
	 *     @type float        $discount             Limit results to those affiliated with a given discount. Default empty.
	 *     @type array        $discount__in         Array of discounts to include affiliated order items for. Default empty.
	 *     @type array        $discount__not_in     Array of discounts to exclude affiliated order items for. Default empty.
	 *     @type float        $tax                  Limit results to those affiliated with a given tax. Default empty.
	 *     @type array        $tax__in              Array of taxes to include affiliated order items for. Default empty.
	 *     @type array        $tax__not_in          Array of taxes to exclude affiliated order items for. Default empty.
	 *     @type float        $total                Limit results to those affiliated with a given total. Default empty.
	 *     @type array        $total__in            Array of totals to include affiliated order items for. Default empty.
	 *     @type array        $total__not_in        Array of totals to exclude affiliated order items for. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a order count (true) or array of order objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete order objects). Default empty.
	 *     @type int          $number               Limit number of order items to retrieve. Default 100.
	 *     @type int          $offset               Number of order items to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'order_id', 'product_id', 'price_id', 'cart_index', 'type'
	 *                                              'status', 'quantity', 'amount', 'subtotal', 'discount', 'tax',
	 *                                              'total', 'date_created', 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching order items for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found order items. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
