<?php
/**
 * Log Query Class.
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
 * Class used for querying items.
 *
 * @since 3.0.0
 *
 * @see \EDD\Database\Queries\Log::__construct() for accepted arguments.
 */
class Log extends Base {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_name = 'logs';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_alias = 'n';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_schema = '\\EDD\\Database\\Schemas\\Logs';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name = 'log';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name_plural = 'logs';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed
	 */
	public $item_shape = 'EDD\\Logs\\Log';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $cache_group = 'logs';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An log ID to only return that order. Default empty.
	 *     @type array        $id__in               Array of log IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of log IDs to exclude. Default empty.
	 *     @type string       $object_id            An object ID to only return those objects. Default empty.
	 *     @type array        $object_id__in        Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in    Array of IDs object to exclude. Default empty.
	 *     @type string       $object_type          An object types to only return that type. Default empty.
	 *     @type array        $object_type__in      Array of object types to include. Default empty.
	 *     @type array        $object_type__not_in  Array of object types to exclude. Default empty.
	 *     @type string       $user_id              A user ID to only return those users. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type array        $title                Title to search by. Default empty.
	 *     @type array        $content              Content to search by. Default empty.
	 *     @type bool         $count                Whether to return a count (true) or array of objects.
	 *                                              Default false.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of IDs)
	 *                                              or empty (returns an array of complete objects). Default empty.
	 *     @type int          $number               Limit number of orders to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of orders to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'object_id', 'object_type', 'user_id', 'date_created',
	 *                                              'user_id__in', 'object_id__in', 'object_type__in'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to retrieved orders. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching orders for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found orders. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
