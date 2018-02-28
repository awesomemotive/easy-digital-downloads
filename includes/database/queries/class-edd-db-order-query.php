<?php

/**
 * Orders: EDD_Order_Query class
 *
 * @package Plugins/EDD/Database/Queries/Orders
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying orders.
 *
 * @since 3.0.0
 *
 * @see EDD_Order_Query::__construct() for accepted arguments.
 */
class EDD_Order_Query extends EDD_DB_Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_name = 'orders';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_alias = 'o';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name = 'order';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name_plural = 'orders';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed
	 */
	public $item_shape = '';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $cache_group = 'orders';

	/** Methods ***************************************************************/

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type int          $id                   An order ID to only return that order. Default empty.
	 *     @type array        $id__in               Array of order IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of order IDs to exclude. Default empty.
	 *     @type string       $order_number         An order number to only return that order. Default empty.
	 *     @type array        $order_number__in     Array of order numbers to include. Default empty.
	 *     @type array        $order_number__not_in Array of order numbers to exclude. Default empty.
	 *     @type string       $status               An order statuses to only return that order. Default empty.
	 *     @type array        $status__in           Array of order statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of order statuses to exclude. Default empty.
	 *     @type int          $user_id              A user ID to only return that object. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type int          $customer_id          A customer ID to only return that object. Default empty.
	 *     @type array        $customer_id__in      Array of customer IDs to include. Default empty.
	 *     @type array        $customer_id__not_in  Array of customer IDs to exclude. Default empty.
	 *     @type string       $email                Limit results to those affiliated with a given email. Default empty.
	 *     @type array        $email__in            Array of email to include affiliated orders for. Default empty.
	 *     @type array        $email__not_in        Array of email to exclude affiliated orders for. Default empty.
	 *     @type string       $gateway              Limit results to those affiliated with a given gateway. Default empty.
	 *     @type array        $gateway__in          Array of gateways to include affiliated orders for. Default empty.
	 *     @type array        $gateway__not_in      Array of gateways to exclude affiliated orders for. Default empty.
	 *     @type string       $payment_key          Limit results to those affiliated with a given payment key. Default empty.
	 *     @type array        $payment_key__in      Array of payment keys to include affiliated orders for. Default empty.
	 *     @type array        $payment_key__not_in  Array of payment keys to exclude affiliated orders for. Default empty.
	 *     @type array        $date_created_query   Date query clauses to limit orders by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_completed_query Date query clauses to limit orders by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a order count (true) or array of order objects.
	 *                                              Default false.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of order IDs)
	 *                                              or empty (returns an array of complete order objects). Default empty.
	 *     @type int          $number               Limit number of orders to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of orders to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'number', 'status', 'user_id', 'customer_id', 'email', 'gateway'
	 *                                              'payment_key', 'date_created', 'date_completed', 'user_id__in', 'customer_id__in'.
	 *                                              'email__in', 'gateway__in', 'payment_key__in'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order retrieved orders. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type string       $search               Search term(s) to retrieve matching orders for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found orders. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
