<?php
/**
 * Order Item Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying order items.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order_Item::__construct() for accepted arguments.
 */
class Order_Item extends Base {

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
	protected $item_shape = '';

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
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type int          $id                   An order item ID to only return that order. Default empty.
	 *     @type array        $id__in               Array of order item IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of order item IDs to exclude. Default empty.
	 *     @type string       $order_id             An order ID to only return those order items. Default empty.
	 *     @type array        $order_id__in         Array of order IDs to include. Default empty.
	 *     @type array        $order_id__not_in     Array of order IDs to exclude. Default empty.
	 *     @type string       $product_id           A product ID to only return those products. Default empty.
	 *     @type array        $product_id__in       Array of product IDs to include. Default empty.
	 *     @type array        $product_id__not_in   Array of product IDs to exclude. Default empty.
	 *     @type string       $price_id             A price ID to only return those prices. Default empty.
	 *     @type array        $price_id__in         Array of price IDs to include. Default empty.
	 *     @type array        $price_id__not_in     Array of price IDs to exclude. Default empty.
	 *     @type string       $cart_index           A cart index to only return those prices. Default empty.
	 *     @type array        $cart_index__in       Array of cart index to include. Default empty.
	 *     @type array        $cart_index__not_in   Array of cart index to exclude. Default empty.
	 *     @type string       $type                 An order types to only return that order. Default empty.
	 *     @type array        $type__in             Array of order types to include. Default empty.
	 *     @type array        $type__not_in         Array of order types to exclude. Default empty.
	 *     @type string       $status               An order statuses to only return that order. Default empty.
	 *     @type array        $status__in           Array of order statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of order statuses to exclude. Default empty.
	 *     @type int          $quantity             A quantity to only return those quantities. Default empty.
	 *     @type array        $quantity__in         Array of quantities to include. Default empty.
	 *     @type array        $quantity__not_in     Array of quantities to exclude. Default empty.
	 *     @type string       $amount               Limit results to those affiliated with a given amount. Default empty.
	 *     @type array        $amount__in           Array of amounts to include affiliated orders for. Default empty.
	 *     @type array        $amount__not_in       Array of amounts to exclude affiliated orders for. Default empty.
	 *     @type string       $subtotal             Limit results to those affiliated with a given subtotal. Default empty.
	 *     @type array        $subtotal__in         Array of subtotals to include affiliated orders for. Default empty.
	 *     @type array        $subtotal__not_in     Array of subtotals to exclude affiliated orders for. Default empty.
	 *     @type string       $discount             Limit results to those affiliated with a given discount. Default empty.
	 *     @type array        $discount__in         Array of discounts to include affiliated orders for. Default empty.
	 *     @type array        $discount__not_in     Array of discounts to exclude affiliated orders for. Default empty.
	 *     @type string       $tax                  Limit results to those affiliated with a given tax. Default empty.
	 *     @type array        $tax__in              Array of taxes to include affiliated orders for. Default empty.
	 *     @type array        $tax__not_in          Array of taxes to exclude affiliated orders for. Default empty.
	 *     @type string       $total                Limit results to those affiliated with a given total. Default empty.
	 *     @type array        $total__in            Array of totals to include affiliated orders for. Default empty.
	 *     @type array        $total__not_in        Array of totals to exclude affiliated orders for. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a order count (true) or array of order objects.
	 *                                              Default false.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of order IDs)
	 *                                              or empty (returns an array of complete order objects). Default empty.
	 *     @type int          $number               Limit number of orders to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of orders to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'order_id', 'product_id', 'price_id', 'cart_index', 'type'
	 *                                              'status', 'quantity', 'amount', 'subtotal', 'total', 'id__in', 'product_id__in',
	 *                                              'price_id__in', 'card_index__in', 'type__in', 'status__in', 'quantity__in',
	 *                                              'amount__in', 'subtotal__in', 'discount__in', 'tax__in', 'total__in'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order retrieved orders. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching orders for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found orders. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
