<?php
/**
 * Customer Address Query Class.
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
 * @see \EDD\Database\Queries\Customer_Address::__construct() for accepted arguments.
 */
class Customer_Address extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'customer_addresses';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'ca';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Customer_Addresses';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'customer_address';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'customer_addresses';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Customers\\Customer_Address';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'customer_addresses';

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
	 *     @type int          $id                   An address ID to only return that address. Default empty.
	 *     @type array        $id__in               Array of address IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of address IDs to exclude. Default empty.
	 *     @type int          $customer_id          A customer ID to only return that object. Default empty.
	 *     @type array        $customer_id__in      Array of customer IDs to include. Default empty.
	 *     @type array        $customer_id__not_in  Array of customer IDs to exclude. Default empty.
	 *     @type int          $is_primary           Search for primary or non-primary addresses
	 *     @type array        $is_primary__in       Array of 0 and/or 1 to get primary and/or non-primary.
	 *     @type array        $is_primary__not_in   Array of 0 and/or 1 to avoid getting addresses of primary designation.
	 *     @type string       $type                 Limit results to those affiliated with a given type. Default empty.
	 *     @type array        $type__in             Array of types to include affiliated addresses for. Default empty.
	 *     @type array        $type__not_in         Array of types to exclude affiliated addresses for. Default empty.
	 *     @type string       $status               An address statuses to only return that address. Default empty.
	 *     @type array        $status__in           Array of address statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of address statuses to exclude. Default empty.
	 *     @type string       $name                 A name to only return addresses with that name. Default empty.
	 *     @type array        $name__in             An array of names to include. Default empty.
	 *     @type array        $name__not_in         An array of names to exclude. Default empty.
	 *     @type string       $address              An address line 1 to only return that address. Default empty.
	 *     @type array        $address__in          Array of addresses to include. Default empty.
	 *     @type array        $address__not_in      Array of addresses to exclude. Default empty.
	 *     @type string       $address2             An address line 2 to only return that address. Default empty.
	 *     @type array        $address2__in         Array of addresses to include. Default empty.
	 *     @type array        $address2__not_in     Array of addresses to exclude. Default empty.
	 *     @type string       $city                 A city to only return that address. Default empty.
	 *     @type array        $city__in             Array of cities to include. Default empty.
	 *     @type array        $city__not_in         Array of cities to exclude. Default empty.
	 *     @type string       $region               A region to only return that region. Default empty.
	 *     @type array        $region__in           Array of regions to include. Default empty.
	 *     @type array        $region__not_in       Array of regions to exclude. Default empty.
	 *     @type string       $postal_code          A postal code to only return that post code. Default empty.
	 *     @type array        $postal_code__in      Array of postal codes to include. Default empty.
	 *     @type array        $postal_code__not_in  Array of postal codes to exclude. Default empty.
	 *     @type string       $country              A country to only return that country. Default empty.
	 *     @type array        $country__in          Array of countries to include. Default empty.
	 *     @type array        $country__not_in      Array of countries to exclude. Default empty.
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
	 *     @type string|array $orderby              Accepts 'id', 'is_primary', 'type', 'status', 'name',
	 *                                              'address', 'address2', 'city', 'region', 'postal_code',
	 *                                              'country', 'date_created', 'date_modified'.
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
