<?php
/**
 * Order Transaction Query Class.
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
 * Class used for querying order transactions.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order_Transaction::__construct() for accepted arguments.
 */
class Order_Transaction extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'order_transactions';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'ot';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Order_Transactions';

	/** Item ******************************************************************/

	/**
	 * Name for a single item.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'order_transaction';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'order_transactions';

	/**
	 * Callback function for turning IDs into objects.
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Orders\\Order_Transaction';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'order_transactions';

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
	 *     @type int          $id                     An ID to only return that order transaction. Default empty.
	 *     @type array        $id__in                 Array of order transaction IDs to include. Default empty.
	 *     @type array        $id__not_in             Array of order transaction IDs to exclude. Default empty.
	 *     @type string       $object_id              An object ID to only return those objects. Default empty.
	 *     @type array        $object_id__in          Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in      Array of IDs object to exclude. Default empty.
	 *     @type string       $object_type            An object type to only return that type. Default empty.
	 *     @type array        $object_type__in        Array of object types to include. Default empty.
	 *     @type array        $object_type__not_in    Array of object types to exclude. Default empty.
	 *     @type string       $transaction_id         A transaction ID to only return that transaction. Default empty.
	 *     @type array        $transaction_id__in     Array of transaction IDs to include. Default empty.
	 *     @type array        $transaction_id__not_in Array of transaction IDs to exclude. Default empty.
	 *     @type string       $gateway                A gateway to filter by. Default empty.
	 *     @type array        $gateway__in            Array of gateways to include. Default empty.
	 *     @type array        $gateway__not_in        Array of gateways to exclude. Default empty.
	 *     @type string       $status                 A status to only return that status. Default empty.
	 *     @type array        $status__in             Array of statuses to include. Default empty.
	 *     @type array        $status__not_in         Array of statuses to exclude. Default empty.
	 *     @type float        $total                  Limit results to those affiliated with a given total. Default empty.
	 *     @type array        $total__in              Array of totals to include affiliated order items for. Default empty.
	 *     @type array        $total__not_in          Array of totals to exclude affiliated order items for. Default empty.
	 *     @type array        $date_query             Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query     Date query clauses to limit by. See WP_Date_Query.
	 *                                                Default null.
	 *     @type array        $date_modified_query    Date query clauses to limit by. See WP_Date_Query.
	 *                                                Default null.
	 *     @type bool         $count                  Whether to return an item count (true) or array of item objects.
	 *                                                Default false.
	 *     @type string       $fields                 Item fields to return. Accepts any column known names
	 *                                                or empty (returns an array of complete order transactions objects).
	 *                                                Default empty.
	 *     @type int          $number                 Limit number of order transactions to retrieve. Default 100.
	 *     @type int          $offset                 Number of items to offset the query. Used to build LIMIT clause.
	 *                                                Default 0.
	 *     @type bool         $no_found_rows          Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby                Accepts 'id', 'object_id', 'object_type', 'transaction_id', 'gateway',
	 *                                                'status', 'total', 'date_created', 'date_modified'.
	 *                                                Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                                Default 'id'.
	 *     @type string       $order                  How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search                 Search term(s) to retrieve matching order transactions for. Default empty.
	 *     @type bool         $update_cache           Whether to prime the cache for found order transactions. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
