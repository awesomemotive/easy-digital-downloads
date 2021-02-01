<?php
/**
 * Customer Query Class.
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
 * Class used for querying customers.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Customer::__construct() for accepted arguments.
 */
class Customer extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'customers';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'c';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Customers';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'customer';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'customers';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD_Customer';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'customers';

	/** Methods ***************************************************************/

	/**
	 * Sets up the customer query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of customer query parameters. Default empty.
	 *
	 *     @type int          $id                   An customer ID to only return that customer. Default empty.
	 *     @type array        $id__in               Array of customer IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of customer IDs to exclude. Default empty.
	 *     @type int          $user_id              A user ID to only return that user. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type string       $email                Limit results to those affiliated with a given email. Default empty.
	 *     @type array        $email__in            Array of email to include affiliated orders for. Default empty.
	 *     @type array        $email__not_in        Array of email to exclude affiliated orders for. Default empty.
	 *     @type string       $status               A statuses to only return that status. Default empty.
	 *     @type array        $status__in           Array of order statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of order statuses to exclude. Default empty.
	 *     @type float        $purchase_value       A purchase value. Default empty.
	 *     @type int          $purchase_count       A numeric value. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit customers by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a customer count (true) or array of customer objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete customer objects). Default empty.
	 *     @type int          $number               Limit number of customers to retrieve. Default 100.
	 *     @type int          $offset               Number of customers to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'email', 'name', 'status', 'purchase_value',
	 *                                              'purchase_count', 'date_created', 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching customers for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found customers. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
