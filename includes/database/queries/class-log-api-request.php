<?php
/**
 * API Request Log Query Class.
 *
 * @package     EDD
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Database\Query;

/**
 * Class used for querying items.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Log_Api_Request::__construct() for accepted arguments.
 */
class Log_Api_Request extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'logs_api_requests';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'la';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Logs_Api_Requests';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'logs_api_request';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'logs_api_requests';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = 'EDD\\Logs\\Api_Request_Log';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'logs_api_requests';

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
	 *     @type string       $user_id              A user ID to only return those users. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type string       $api_key              An API key to only return that key. Default empty.
	 *     @type array        $api_key__in          Array of API keys to include. Default empty.
	 *     @type array        $api_key__not_in      Array of API keys to exclude. Default empty.
	 *     @type string       $token                A token to only return that token. Default empty.
	 *     @type array        $token__in            Array of tokens to include. Default empty.
	 *     @type array        $token__not_in        Array of tokens to exclude. Default empty.
	 *     @type string       $version              A version to only return that version. Default empty.
	 *     @type array        $version__in          Array of versions to include. Default empty.
	 *     @type array        $version__not_in      Array of versions to exclude. Default empty.
	 *     @type string       $request              Request to search by. Default empty.
	 *     @type string       $error                Error to search by. Default empty.
	 *     @type string       $ip                   An IP to only return that IP address. Default empty.
	 *     @type array        $ip__in               Array of IPs to include. Default empty.
	 *     @type array        $ip__not_in           Array of IPs to exclude. Default empty.
	 *     @type string       $time                 A time to only return that time. Default empty.
	 *     @type array        $time__in             Array of times to include. Default empty.
	 *     @type array        $time__not_in         Array of times to exclude. Default empty.
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
	 *     @type string|array $orderby              Accepts 'id', 'user_id', 'api_key', 'token', 'version', 'ip',
	 *                                              'time', 'date_created', and 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
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
