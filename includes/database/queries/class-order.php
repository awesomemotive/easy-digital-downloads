<?php
/**
 * Order Query Class.
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
 * Class used for querying orders.
 *
 * @since 3.0
 *
 * @see \EDD\Database\Queries\Order::__construct() for accepted arguments.
 */
class Order extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'orders';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'o';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Orders';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'order';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'orders';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\EDD\\Orders\\Order';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'orders';

	/** Methods ***************************************************************/

	/**
	 * Sets up the order query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of order query parameters. Default empty.
	 *
	 *     @type int          $id                    An order ID to only return that order. Default empty.
	 *     @type array        $id__in                Array of order IDs to include. Default empty.
	 *     @type array        $id__not_in            Array of order IDs to exclude. Default empty.
	 *     @type int          $parent                A parent ID to only return orders with that parent. Default empty.
	 *     @type array        $parent__in            An array of parent IDs to include. Default empty.
	 *     @type array        $parent__not_in        An array of parent IDs to exclude. Default empty.
	 *     @type string       $order_number          An order number to only return that number. Default empty.
	 *     @type array        $order_number__in      Array of order numbers to include. Default empty.
	 *     @type array        $order_number__not_in  Array of order numbers to exclude. Default empty.
	 *     @type string       $status                An order status to only return that status. Default empty.
	 *     @type array        $status__in            Array of order statuses to include. Default empty.
	 *     @type array        $status__not_in        Array of order statuses to exclude. Default empty.
	 *     @type string       $type                  An order type to only return that type. Default empty.
	 *     @type array        $type__in              Array of order types to include. Default empty.
	 *     @type array        $type__not_in          Array of order types to exclude. Default empty.
	 *     @type int          $user_id               A user ID to only return that user. Default empty.
	 *     @type array        $user_id__in           Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in       Array of user IDs to exclude. Default empty.
	 *     @type int          $customer_id           A customer ID to only return that customer. Default empty.
	 *     @type array        $customer_id__in       Array of customer IDs to include. Default empty.
	 *     @type array        $customer_id__not_in   Array of customer IDs to exclude. Default empty.
	 *     @type string       $email                 Limit results to those affiliated with a given email. Default empty.
	 *     @type array        $email__in             Array of email to include affiliated orders for. Default empty.
	 *     @type array        $email__not_in         Array of email to exclude affiliated orders for. Default empty.
	 *     @type string       $ip                    A filter IP address to only include orders with that IP. Default empty.
	 *     @type array        $ip__in                An array of IPs to include. Default empty.
	 *     @type array        $ip__not_in            An array of IPs to exclude. Default empty.
	 *     @type string       $gateway               Limit results to those affiliated with a given gateway. Default empty.
	 *     @type array        $gateway__in           Array of gateways to include affiliated orders for. Default empty.
	 *     @type array        $gateway__not_in       Array of gateways to exclude affiliated orders for. Default empty.
	 *     @type string       $mode                  Limit results to those affiliated with a given mode. Default empty.
	 *     @type array        $mode__in              Array of modes to include affiliated orders for. Default empty.
	 *     @type array        $mode__not_in          Array of modes to exclude affiliated orders for. Default empty.
	 *     @type string       $currency              Limit results to those affiliated with a given currency. Default empty.
	 *     @type array        $currency__in          Array of currencies to include affiliated orders for. Default empty.
	 *     @type array        $currency__not_in      Array of currencies to exclude affiliated orders for. Default empty.
	 *     @type string       $payment_key           Limit results to those affiliated with a given payment key. Default empty.
	 *     @type array        $payment_key__in       Array of payment keys to include affiliated orders for. Default empty.
	 *     @type array        $payment_key__not_in   Array of payment keys to exclude affiliated orders for. Default empty.
	 *     @type int          $tax_rate_id           A tax rate ID to filter by. Default empty.
	 *     @type array        $tax_rate_id__in       Array of tax rate IDs to filter by. Default empty.
	 *     @type array        $tax_rate_id__not_in   Array of tax rate IDs to exclude orders for. Default empty.
	 *     @type array        $date_query            Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query    Date query clauses to limit orders by. See WP_Date_Query.
	 *                                               Default null.
	 *     @type array        $date_completed_query  Date query clauses to limit orders by. See WP_Date_Query.
	 *                                               Default null.
	 *     @type array        $date_refundable_query Date query clauses to limit orders by. See WP_Date_Query.
	 *                                               Default null.
	 *     @type bool         $count                 Whether to return a order count (true) or array of order objects.
	 *                                               Default false.
	 *     @type string       $fields                Item fields to return. Accepts any column known names
	 *                                               or empty (returns an array of complete order objects). Default empty.
	 *     @type int          $number                Limit number of orders to retrieve. Default 100.
	 *     @type int          $offset                Number of orders to offset the query. Used to build LIMIT clause.
	 *                                               Default 0.
	 *     @type bool         $no_found_rows         Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby               Accepts 'id', 'parent', 'order_number', 'status', 'type',
	 *                                               'user_id', 'customer_id', 'email', 'ip', 'gateway',
	 *                                               'tax_rate_id', 'subtotal', 'discount', 'tax', 'total',
	 *                                               'date_created', 'date_modified', 'date_completed', 'date_refundable'.
	 *                                               Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                               Default 'id'.
	 *     @type string       $order                 How to order retrieved orders. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search                Search term(s) to retrieve matching orders for. Default empty.
	 *     @type bool         $update_cache          Whether to prime the cache for found orders. Default false.
	 *     @type string       $country               Limit results to those affiliated with a given country. Default empty.
	 *     @type string       $region                Limit results to those affiliated with a given region. Default empty.
	 * }
	 */
	public function __construct( $query = array() ) {

		// In EDD 3.0 we converted our use of the status 'publish' to 'complete', this accounts for queries using publish.
		if ( isset( $query['status'] ) ) {
			if ( is_array( $query['status'] ) && in_array( 'publish', $query['status'], true ) ) {
				foreach ( $query['status'] as $key => $status ) {
					if ( 'publish' === $status ) {
						unset( $query['status'][ $key ] );
					}
				}

				$query['status'][] = 'complete';
			} elseif ( 'publish' === $query['status'] ) {
				$query['status'] = 'complete';
			}
		}

		parent::__construct( $query );
	}

	/**
	 * Set up the filter callback to add the country and region from the order addresses table.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $query See Order::__construct() for accepted arguments.
	 *
	 * @see Order::__construct()
	 */
	public function query( $query = array() ) {
		if ( ! empty( $query['country'] ) ) {
			add_filter( 'edd_orders_query_clauses', array( $this, 'query_by_country' ) );
		}

		$result = parent::query( $query );

		if ( ! empty( $query['country'] ) ) {
			remove_filter( 'edd_orders_query_clauses', array( $this, 'query_by_country' ) );
		}

		return $result;
	}

	/**
	 * Filter the query clasue to add the country and region from the order addresses table.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $clauses The clauses which will generate the final SQL query.
	 */
	public function query_by_country( $clauses ) {

		if ( empty( $this->query_vars['country'] ) || 'all' === $this->query_vars['country'] ) {
			return $clauses;
		}

		global $wpdb;

		$primary_alias  = $this->table_alias;
		$primary_column = parent::get_primary_column_name();

		$order_addresses_query = new \EDD\Database\Queries\Order_Address();
		$join_alias            = $order_addresses_query->table_alias;

		if ( ! empty( $clauses['where'] ) ) {
			$where_clause = ' AND ' . $clauses['where'];
		} else {
			$where_clause = '';
		}

		// Filter by the order address's region (state/province/etc)..
		if ( ! empty( $this->query_vars['region'] ) && 'all' !== $this->query_vars['region'] ) {
			$location_join = $wpdb->prepare(
				" LEFT JOIN {$order_addresses_query->table_name} {$join_alias} ON {$primary_alias}.{$primary_column} = {$join_alias}.order_id WHERE {$join_alias}.country = %s AND {$join_alias}.region = %s {$where_clause}",
				$this->query_vars['country'],
				$this->query_vars['region']
			);

			// Add the region to the query var defaults.
			$this->query_var_defaults['region'] = $this->query_vars['region'];

			// Filter only by the country, not by region.
		} else {
				$location_join = $wpdb->prepare(
					" LEFT JOIN {$order_addresses_query->table_name} {$join_alias} ON {$primary_alias}.{$primary_column} = {$join_alias}.order_id WHERE {$join_alias}.country = %s {$where_clause}",
					$this->query_vars['country']
				);

				// Add the country to the query var defaults.
				$this->query_var_defaults['country'] = $this->query_vars['country'];
		}

		// Add the customized join to the query.
		$clauses['join'] .= ' ' . $location_join;

		// We have added the wheres to the join calls so we don't need it to be here anymore.
		unset( $clauses['where'] );

		return $clauses;
	}

	/**
	 * Set the query var defaults for country and region.
	 *
	 * @since 3.0
	 * @access public
	 */
	protected function set_query_var_defaults() {
		parent::set_query_var_defaults();

		$this->query_var_defaults['country'] = false;
		$this->query_var_defaults['region']  = false;
	}

}
