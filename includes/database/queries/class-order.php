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
	 *     @type int          $product_id            Filter by product ID. Default empty.
	 *     @type int          $product_price_id      Filter by product price ID. Default empty.
	 *     @type string       $txn                   Filter by transaction ID.
	 *     @type int          $discount_id           Filter by discount code.
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
		$query_clauses_filters = $this->get_query_clauses_filters( $query );
		foreach ( $query_clauses_filters as $filter ) {
			if ( $filter['condition'] ) {
				add_filter( 'edd_orders_query_clauses', array( $this, $filter['callback'] ) );
			}
		}

		$result = parent::query( $query );

		foreach ( $query_clauses_filters as $filter ) {
			if ( $filter['condition'] ) {
				remove_filter( 'edd_orders_query_clauses', array( $this, $filter['callback'] ) );
			}
		}

		return $result;
	}

	/**
	 * Filter the query clause to add the country and region from the order addresses table.
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

		// Filter by the order address's region (state/province/etc)..
		if ( ! empty( $this->query_vars['region'] ) && 'all' !== $this->query_vars['region'] ) {
			$location_join = $wpdb->prepare(
				" INNER JOIN {$order_addresses_query->table_name} {$join_alias} ON ({$primary_alias}.{$primary_column} = {$join_alias}.order_id AND {$join_alias}.country = %s AND {$join_alias}.region = %s)",
				$this->query_vars['country'],
				$this->query_vars['region']
			);

			// Add the region to the query var defaults.
			$this->query_var_defaults['region'] = $this->query_vars['region'];

			// Filter only by the country, not by region.
		} else {
				$location_join = $wpdb->prepare(
					" INNER JOIN {$order_addresses_query->table_name} {$join_alias} ON ({$primary_alias}.{$primary_column} = {$join_alias}.order_id AND {$join_alias}.country = %s)",
					$this->query_vars['country']
				);

				// Add the country to the query var defaults.
				$this->query_var_defaults['country'] = $this->query_vars['country'];
		}

		// Add the customized join to the query.
		$clauses['join'] .= ' ' . $location_join;

		return $clauses;
	}

	/**
	 * Filter the query clause to filter by product ID.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param string|array $clauses The clauses which will generate the final SQL query.
	 */
	public function query_by_product( $clauses ) {
		if (
			empty( $this->query_vars['product_id'] ) &&
			( ! isset( $this->query_vars['product_price_id'] ) || ! is_numeric( $this->query_vars['product_price_id'] ) )
		) {
			return $clauses;
		}

		global $wpdb;

		$primary_column = parent::get_primary_column_name();
		$order_items_query = new Order_Item();

		// Build up our conditions.
		$conditions = array();
		foreach ( array( 'product_id' => 'product_id', 'product_price_id' => 'price_id' ) as $query_var => $db_col ) {
			if ( isset( $this->query_vars[ $query_var ] ) && is_numeric( $this->query_vars[ $query_var ] ) ) {
				$conditions[] = $wpdb->prepare(
					"AND {$order_items_query->table_alias}.{$db_col} = %d",
					absint( $this->query_vars[ $query_var ] )
				);
			}
		}

		$conditions = implode( ' ', $conditions );

		$clauses['join'] .= " INNER JOIN {$order_items_query->table_name} {$order_items_query->table_alias} ON(
				{$this->table_alias}.{$primary_column} = {$order_items_query->table_alias}.order_id
				{$conditions}
			)";

		return $clauses;
	}

	/**
	 * Filter the query clause to filter by transaction ID.
	 *
	 * @since 3.0.2
	 * @param string $clauses
	 * @return string
	 */
	public function query_by_txn( $clauses ) {
		if ( empty( $this->query_vars['txn'] ) ) {
			return $clauses;
		}

		global $wpdb;

		$primary_column          = parent::get_primary_column_name();
		$order_transaction_query = new Order_Transaction();

		$clauses['join'] .= $wpdb->prepare(
			" INNER JOIN {$order_transaction_query->table_name} {$order_transaction_query->table_alias}
			ON( {$this->table_alias}.{$primary_column} = {$order_transaction_query->table_alias}.object_id
			AND {$order_transaction_query->table_alias}.transaction_id = %s )",
			sanitize_text_field( $this->query_vars['txn'] )
		);

		return $clauses;
	}

	/**
	 * Filter the query clause to filter by discount ID.
	 *
	 * @since 3.0.2
	 * @param string $clauses
	 * @return string
	 */
	public function query_by_discount_id( $clauses ) {
		if ( empty( $this->query_vars['discount_id'] ) ) {
			return $clauses;
		}

		global $wpdb;

		$primary_column         = parent::get_primary_column_name();
		$order_adjustment_query = new Order_Adjustment();

		$clauses['join'] .= $wpdb->prepare(
			" INNER JOIN {$order_adjustment_query->table_name} {$order_adjustment_query->table_alias}
			ON( {$this->table_alias}.{$primary_column} = {$order_adjustment_query->table_alias}.object_id
			AND {$order_adjustment_query->table_alias}.type_id = %d )",
			absint( $this->query_vars['discount_id'] )
		);

		return $clauses;
	}

	/**
	 * When searching by a numeric order number, we need to override the default where clause
	 * to return orders matching either the ID or order number.
	 *
	 * @since 3.1.1.4
	 * @param array $clauses
	 * @return array
	 */
	public function query_by_order_search( $clauses ) {
		global $wpdb;
		$clauses['where'] = $wpdb->prepare(
			"{$this->table_alias}.id = %d OR {$this->table_alias}.order_number = %d",
			absint( $this->query_vars['id'] ),
			absint( $this->query_vars['order_number'] )
		);

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

		$this->query_var_defaults['country']            = false;
		$this->query_var_defaults['region']             = false;
		$this->query_var_defaults['product_id']         = false;
		$this->query_var_defaults['product_product_id'] = false;
	}

	/**
	 * Adds an item to the database
	 *
	 * @since 3.0
	 *
	 * @param array $data
	 * @return false|int  Returns the item ID on success; false on failure.
	 */
	public function add_item( $data = array() ) {
		// Every order should have a currency assigned.
		if ( empty( $data['currency'] ) ) {
			$data['currency'] = edd_get_currency();
		}

		// If the payment key isn't already created, generate it.
		if ( empty( $data['payment_key'] ) ) {
			$email               = ! empty( $data['email'] ) ? $data['email'] : '';
			$data['payment_key'] = edd_generate_order_payment_key( $email );
		}

		// Add the IP address if it hasn't been already.
		if ( empty( $data['ip'] ) ) {
			$data['ip'] = edd_get_ip();
		}

		return parent::add_item( $data );
	}

	/**
	 * Get the array of possible query clause filters.
	 *
	 * @since 3.0.2
	 * @param array $query
	 * @return array
	 */
	private function get_query_clauses_filters( $query ) {
		return array(
			array(
				'condition' => ! empty( $query['country'] ),
				'callback'  => 'query_by_country',
			),
			array(
				'condition' => ! empty( $query['product_id'] ) || ( isset( $query['product_price_id'] ) && is_numeric( $query['product_price_id'] ) ),
				'callback'  => 'query_by_product',
			),
			array(
				'condition' => ! empty( $query['txn'] ),
				'callback'  => 'query_by_txn',
			),
			array(
				'condition' => ! empty( $query['discount_id'] ),
				'callback'  => 'query_by_discount_id',
			),
			array(
				'condition' => ! empty( $query['id'] ) && ! empty( $query['order_number'] ) && $query['id'] === $query['order_number'],
				'callback'  => 'query_by_order_search',
			),
		);
	}
}
