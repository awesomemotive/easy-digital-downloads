<?php
/**
 * Discount Query Class.
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
 * Class used for querying discounts.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Discount::__construct() for accepted arguments.
 */
class Discount extends Base {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_name = 'discounts';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_alias = 'd';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Discounts';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name = 'discount';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name_plural = 'discounts';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Database\\Objects\\Discount';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'discounts';

	/** Methods ***************************************************************/

	/**
	 * Sets up the adjustment query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of adjustment query parameters. Default empty.
	 *
	 *     @type int          $id                   A discount ID to only return that discount. Default empty.
	 *     @type array        $id__in               Array of discount IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of discount IDs to exclude. Default empty.
	 *     @type int          $code                 A discount code to only return that discount. Default empty.
	 *     @type array        $code__in             Array of discount codes to include. Default empty.
	 *     @type array        $code__not_in         Array of discount codes to exclude. Default empty.
	 *     @type int          $status               A discount status to only return that discount. Default empty.
	 *     @type array        $status__in           Array of discount statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of discount statuses to exclude. Default empty.
	 *     @type int          $type                 A discount type to only return that discount. Default empty.
	 *     @type array        $type__in             Array of discount types to include. Default empty.
	 *     @type array        $type__not_in         Array of discount types to exclude. Default empty.
	 *     @type int          $scope                A discount scope to only return that discount. Default empty.
	 *     @type array        $scope__in            Array of discount scopes to include. Default empty.
	 *     @type array        $scope__not_in        Array of discount scopes to exclude. Default empty.
	 *     @type int          $amount               A discount amount to only return that discount. Default empty.
	 *     @type array        $amount__in           Array of discount amounts to include. Default empty.
	 *     @type array        $amount__not_in       Array of discount amounts to exclude. Default empty.
	 *     @type int          $max_uses             A discount max_uses to only return that discount. Default empty.
	 *     @type array        $max_uses__in         Array of discount max_uses to include. Default empty.
	 *     @type array        $max_uses__not_in     Array of discount max_uses to exclude. Default empty.
	 *     @type int          $use_count            A discount use_count to only return that discount. Default empty.
	 *     @type array        $use_count__in        Array of discount use_counts to include. Default empty.
	 *     @type array        $use_count__not_in    Array of discount use_counts to exclude. Default empty.
	 *     @type array        $once_per_customer    A true or false. Default empty.
	 *     @type array        $min_cart_price       Minimum cart price. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $start_date_query     Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $end_date_query       Date query clauses to limit discounts by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a discount count (true) or array of discount objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete discount objects). Default empty.
	 *     @type int          $number               Limit number of discounts to retrieve. Default 100.
	 *     @type int          $offset               Number of discounts to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching discounts for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found discounts. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
