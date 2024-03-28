<?php
/**
 * Adjustment Query Class.
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
 * Class used for querying adjustments.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Adjustment::__construct() for accepted arguments.
 */
class Adjustment extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_name = 'adjustments';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_alias = 'a';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Adjustments';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name = 'adjustment';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name_plural = 'adjustments';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Database\\Rows\\Adjustment';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'adjustments';

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
	 *     @type int          $id                   A adjustment ID to only return that adjustment. Default empty.
	 *     @type array        $id__in               Array of adjustment IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of adjustment IDs to exclude. Default empty.
	 *     @type int          $parent               A parent adjustment ID to only return adjustments with
	 *                                              that parent. Default empty.
	 *     @type array        $parent_id__in        An array of parent IDs to include. Default empty.
	 *     @type array        $parent_id__not_in    An array of parent IDs to exclude. Default empty.
	 *     @type int          $code                 A adjustment code to only return that adjustment. Default empty.
	 *     @type array        $code__in             Array of adjustment codes to include. Default empty.
	 *     @type array        $code__not_in         Array of adjustment codes to exclude. Default empty.
	 *     @type int          $status               A adjustment status to only return that status. Default empty.
	 *     @type array        $status__in           Array of adjustment statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of adjustment statuses to exclude. Default empty.
	 *     @type int          $type                 A adjustment type to only return that type. Default empty.
	 *     @type array        $type__in             Array of adjustment types to include. Default empty.
	 *     @type array        $type__not_in         Array of adjustment types to exclude. Default empty.
	 *     @type int          $scope                A adjustment scope to only return that scope. Default empty.
	 *     @type array        $scope__in            Array of adjustment scopes to include. Default empty.
	 *     @type array        $scope__not_in        Array of adjustment scopes to exclude. Default empty.
	 *     @type int          $amount_type          A adjustment amount type to only return that type. Default empty.
	 *     @type array        $amount_type__in      Array of amount adjustment types to include. Default empty.
	 *     @type array        $amount_type__not_in  Array of amount adjustment types to exclude. Default empty.
	 *     @type int|float    $amount               A adjustment amount to only return that amount. Default empty.
	 *     @type array        $amount__in           Array of adjustment amounts to include. Default empty.
	 *     @type array        $amount__not_in       Array of adjustment amounts to exclude. Default empty.
	 *     @type int          $max_uses             A adjustment max_uses to only return that amount. Default empty.
	 *     @type array        $max_uses__in         Array of adjustment max_uses to include. Default empty.
	 *     @type array        $max_uses__not_in     Array of adjustment max_uses to exclude. Default empty.
	 *     @type int          $use_count            A adjustment use_count to only return that count. Default empty.
	 *     @type array        $use_count__in        Array of adjustment use_counts to include. Default empty.
	 *     @type array        $use_count__not_in    Array of adjustment use_counts to exclude. Default empty.
	 *     @type int          $once_per_customer    '1' for true, '0' for false. Default empty.
	 *     @type int|float    $min_charge_amount    Minimum charge amount. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit adjustments by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $start_date_query     Date query clauses to limit adjustments by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $end_date_query       Date query clauses to limit adjustments by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a adjustment count (true) or array of adjustment objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete adjustment objects). Default empty.
	 *     @type int          $number               Limit number of adjustments to retrieve. Default 100.
	 *     @type int          $offset               Number of adjustments to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'parent', 'name', 'code', 'status', 'type',
	 *                                              'scope', 'amount_type', 'amount', 'start_date', 'end_date',
	 *                                              'date_created', and 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching adjustments for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found adjustments. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}

	/**
	 * Adds a new adjustment to the database.
	 *
	 * @param array $data The array of data to insert into the database.
	 * @return int|false
	 */
	public function add_item( $data = array() ) {
		return parent::add_item( $this->update_empty_start_end_dates( $data ) );
	}

	/**
	 * Updates an adjustment in the database.
	 *
	 * @param int   $item_id The ID of the adjustment to update.
	 * @param array $data    The array of data to update in the database.
	 * @return int|false
	 */
	public function update_item( $item_id = 0, $data = array() ) {
		return parent::update_item( $item_id, $this->update_empty_start_end_dates( $data ) );
	}

	/**
	 * Updates empty start and end dates.
	 *
	 * @param array $data The data to filter.
	 * @return array
	 */
	private function update_empty_start_end_dates( $data ) {
		if ( empty( $data['start_date'] ) ) {
			$data['start_date'] = null;
		}

		if ( empty( $data['end_date'] ) ) {
			$data['end_date'] = null;
		}

		return $data;
	}
}
