<?php
/**
 * Tax Rate Query Class.
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
 * Class used for querying tax rates.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Tax_Rate::__construct() for accepted arguments.
 */
class Tax_Rate extends Base {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_name = 'tax_rates';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_alias = 'tr';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Tax_Rates';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name = 'tax_rate';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $item_name_plural = 'tax_rates';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access protected
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Database\\Objects\\Tax_Rate';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'tax_rates';

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
	 *     @type int          $id                  A tax rate ID to only return that tax rate. Default empty.
	 *     @type array        $id__in              Array of tax rate IDs to include. Default empty.
	 *     @type array        $id__not_in          Array of tax rate IDs to exclude. Default empty.
	 *     @type int          $status              A tax rate status to only return that tax rate. Default empty.
	 *     @type array        $status__in          Array of tax rate statuses to include. Default empty.
	 *     @type array        $status__not_in      Array of tax rate statuses to exclude. Default empty.
	 *     @type int          $country             A tax rate country to only return that tax rate. Default empty.
	 *     @type array        $country__in         Array of tax rate countries to include. Default empty.
	 *     @type array        $country__not_in     Array of tax rate countries to exclude. Default empty.
	 *     @type int          $region              A tax rate region to only return that tax rate. Default empty.
	 *     @type array        $region__in          Array of tax rate regions to include. Default empty.
	 *     @type array        $region__not_in      Array of tax rate regions to exclude. Default empty.
	 *     @type int          $scope               A scope to only return that tax rate. Default empty.
	 *     @type array        $scope__in           Array of scopes to include. Default empty.
	 *     @type array        $scope__not_in       Array of scopes to exclude. Default empty.
	 *     @type int          $rate                A tax rate to only return that tax rate. Default empty.
	 *     @type array        $rate__in            Array of tax rates to include. Default empty.
	 *     @type array        $rate__not_in        Array of tax rates to exclude. Default empty.
	 *     @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query  Date query clauses to limit tax rates by. See WP_Date_Query.
	 *                                             Default null.
	 *     @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query.
	 *                                             Default null.
	 *     @type array        $start_date_query    Date query clauses to limit tax rates by. See WP_Date_Query.
	 *                                             Default null.
	 *     @type array        $end_date_query      Date query clauses to limit tax rates by. See WP_Date_Query.
	 *                                             Default null.
	 *     @type bool         $count               Whether to return a tax rate count (true) or array of tax rate objects.
	 *                                             Default false.
	 *     @type string       $fields              Item fields to return. Accepts any column known names
	 *                                             or empty (returns an array of complete tax rate objects). Default empty.
	 *     @type int          $number              Limit number of tax rates to retrieve. Default 100.
	 *     @type int          $offset              Number of tax rates to offset the query. Used to build LIMIT clause.
	 *                                             Default 0.
	 *     @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby             Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                             Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                             Default 'id'.
	 *     @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search              Search term(s) to retrieve matching tax rates for. Default empty.
	 *     @type bool         $update_cache        Whether to prime the cache for found tax rates. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
