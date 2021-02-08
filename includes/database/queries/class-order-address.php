<?php
/**
 * Order Address Query Class.
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
 * Class used for querying customer addresses.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order_Address::__construct() for accepted arguments.
 */
class Order_Address extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'order_addresses';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'oa';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Order_Addresses';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'order_address';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'order_addresses';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Orders\\Order_Address';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'order_addresses';

	/** Methods ***************************************************************/

	/**
	 * Sets up the customer query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An address ID to only return that order address. Default empty.
	 *     @type array        $id__in               Array of order address IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of order address IDs to exclude. Default empty.
	 *     @type int          $order_id             A order ID to only return that order. Default empty.
	 *     @type array        $order_id__in         Array of order IDs to include. Default empty.
	 *     @type array        $order_id__not_in     Array of order IDs to exclude. Default empty.
	 *     @type string       $type                 An order address type. Default empty.
	 *     @type array        $type__in             An array of types to include. Default empty.
	 *     @type array        $type__not_in         An array of types to exclude. Default empty.
	 *     @type string       $name                 A name to only return that address.
	 *     @type array        $name__in             Array of names to include. Default empty.
	 *     @type array        $name__not_in         Array of names to exclude. Default empty.
	 *     @type string       $address              An address line 1 to only return that address. Default empty.
	 *     @type array        $address__in          Array of addresses to include. Default empty.
	 *     @type array        $address__not_in      Array of addresses to exclude. Default empty.
	 *     @type string       $address2             An address line 2 to only return that address. Default empty.
	 *     @type array        $address2__in         Array of addresses to include. Default empty.
	 *     @type array        $address2__not_in     Array of addresses to exclude. Default empty.
	 *     @type string       $city                 A city to only return that address. Default empty.
	 *     @type array        $city__in             Array of cities to include. Default empty.
	 *     @type array        $city__not_in         Array of cities to exclude. Default empty.
	 *     @type string       $region               A region to only return that address. Default empty.
	 *     @type array        $region__in           Array of regions to include. Default empty.
	 *     @type array        $region__not_in       Array of regions to exclude. Default empty.
	 *     @type string       $postal_code          A postal code to only return that address. Default empty.
	 *     @type array        $postal_code__in      Array of postal codes to include. Default empty.
	 *     @type array        $postal_code__not_in  Array of postal codes to exclude. Default empty.
	 *     @type string       $country              A country to only return that address. Default empty.
	 *     @type array        $country__in          Array of countries to include. Default empty.
	 *     @type array        $country__not_in      Array of countries to exclude. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit results by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return an item count (true) or array of item objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete item objects). Default empty.
	 *     @type int          $number               Limit number of results to retrieve. Default 100.
	 *     @type int          $offset               Number of items to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'type', 'name', 'address', 'address2', 'city',
	 *                                              'region', 'postal_code', 'country', 'date_created', 'date_modified'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching results for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found items. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
