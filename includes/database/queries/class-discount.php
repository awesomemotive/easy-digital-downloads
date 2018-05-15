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
	protected $item_shape = '\\EDD_Discount';

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
	 * Sets up the discount query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access protected
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
	 *     @type int          $number               Limit number of discounts to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of discounts to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to discount retrieved discounts. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching discounts for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found discounts. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
