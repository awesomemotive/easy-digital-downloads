<?php
/**
 * File Download Log Query Class.
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
 * Class used for querying items.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Log_File_Download::__construct() for accepted arguments.
 */
class Log_File_Download extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'logs_file_downloads';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'lf';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Logs_File_Downloads';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'logs_file_download';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'logs_file_downloads';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = 'EDD\\Logs\\File_Download_Log';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'logs_file_downloads';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An log ID to only return that log. Default empty.
	 *     @type array        $id__in               Array of log IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of log IDs to exclude. Default empty.
	 *     @type string       $product_id           A product ID to only return those products. Default empty.
	 *     @type array        $product_id__in       Array of product IDs to include. Default empty.
	 *     @type array        $product_id__not_in   Array of product IDs to exclude. Default empty.
	 *     @type string       $file_id              A file ID to only return those files. Default empty.
	 *     @type array        $file_id__in          Array of file IDs to include. Default empty.
	 *     @type array        $file_id__not_in      Array of file IDs to exclude. Default empty.
	 *     @type string       $order_id             An order ID to only return those orders. Default empty.
	 *     @type array        $order_id__in         Array of order IDs to include. Default empty.
	 *     @type array        $order_id__not_in     Array of order IDs to exclude. Default empty.
	 *     @type string       $price_id             A price ID to only return those prices. Default empty.
	 *     @type array        $price_id__in         Array of price IDs to include. Default empty.
	 *     @type array        $price_id__not_in     Array of price IDs to exclude. Default empty.
	 *     @type string       $customer_id          A customer ID to only return those customers. Default empty.
	 *     @type array        $customer_id__in      Array of customer IDs to include. Default empty.
	 *     @type array        $customer_id__not_in  Array of customer IDs to exclude. Default empty.
	 *     @type string       $ip                   An IP to only return that IP address. Default empty.
	 *     @type array        $ip__in               Array of IPs to include. Default empty.
	 *     @type array        $ip__not_in           Array of IPs to exclude. Default empty.
	 *     @type string       $user_agent           A user agent to only return that agent. Default empty.
	 *     @type array        $user_agent__in       Array of user agents to include. Default empty.
	 *     @type array        $user_agent__not_in   Array of user agents to exclude. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a count (true) or array of objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete objects). Default empty.
	 *     @type int          $number               Limit number of logs to retrieve. Default 100.
	 *     @type int          $offset               Number of logs to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'product_id', 'file_id', 'order_id', 'customer_id',
	 *                                              'ip', 'date_created', and 'date_modified'. Also accepts false,
	 *                                              an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching logs for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found logs. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
