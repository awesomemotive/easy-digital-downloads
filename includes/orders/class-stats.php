<?php
/**
 * Order Stats class.
 *
 * @package     EDD
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Orders;

use EDD\Reports as Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Stats Class.
 *
 * @since 3.0
 */
class Stats {

	/**
	 * Parsed query vars.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Date ranges.
	 *
	 * @since 3.0
	 * @access protected
	 * @var array
	 */
	protected $date_ranges = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param array $query {
	 *     Optional. Array of query parameters.
	 *     Default empty.
	 * }
	 */
	public function __construct( $query = array() ) {

		// Start the Reports API.
		new Reports\Init();

		// Set date ranges.
		$this->set_date_ranges();

		if ( ! empty( $query ) ) {
			$this->parse_query( $query );
		}
	}

	/** Calculation Methods ***************************************************/

	/** Orders ***************************************************************/

	/**
	 * Calculate order earnings.
	 *
	 * @since 3.0
	 *
	 * @param array $query
	 *
	 * @return string Formatted order earnings.
	 */
	public function get_order_earnings( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'total';
		$this->query_vars['date_query_column'] = 'date_created';

		$function = isset( $this->query_vars['function'] )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: "SUM({$this->query_vars['column']})";

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT {$function}
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: (float) $result;

		return edd_currency_filter( edd_format_amount( $total ) );
	}

	/**
	 * Calculate the number of orders.
	 *
	 * @param array $query
	 *
	 * @return int Number of orders.
	 */
	public function get_order_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Only `COUNT` and `AVG` are accepted by this method.
		$accepted_functions = array( 'COUNT', 'AVG' );

		$function = isset( $this->query_vars['function'] ) && in_array( strtoupper( $this->query_vars['function'] ), $accepted_functions, true )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: 'COUNT(id)';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT {$function}
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: absint( $result );

		return $total;
	}

	/**
	 * Calculate number of refunded orders.
	 *
	 * @param array $query
	 *
	 * @return int
	 */
	public function get_order_refund_count( $query = array() ) {
		$this->query_vars['where_sql'] = $this->get_db()->prepare( 'AND status = %s', 'refunded' );

		return $this->get_order_count( $query );
	}

	/**
	 * Calculate total amount for refunded orders.
	 *
	 * @param array $query
	 *
	 * @return string Formatted amount from refunded orders.
	 */
	public function get_order_refund_amount( $query = array() ) {
		$this->query_vars['where_sql'] = $this->get_db()->prepare( 'AND status = %s', 'refunded' );

		return $this->get_order_earnings( $query );
	}

	public function get_average_refund_time( $query = array() ) {
		// TODO: Implement as per partial refunds
	}

	/**
	 * Calculate refund rate.
	 *
	 * @param array $query
	 *
	 * @return float|int Rate of refunded orders.
	 */
	public function get_refund_rate( $query ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$status_sql = $this->get_db()->prepare( 'AND status = %s', 'refunded' );

		$sql = "SELECT COUNT(id) / o.total * 100 AS `refund_rate`
				FROM {$this->query_vars['table']}
				CROSS JOIN (
					SELECT COUNT(id) AS total
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				) o
				WHERE 1=1 {$status_sql} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: round( $result, 2 );

		return $total;
	}

	/** Order Item ************************************************************/

	public function get_order_item_earnings( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = 'total';
		$this->query_vars['date_query_column'] = 'date_created';

		$function = isset( $this->query_vars['function'] )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: "SUM({$this->query_vars['column']})";

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$product_id = isset( $this->query_vars['product_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['product_id'] ) )
			: '';

		$sql = "SELECT {$function}
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$product_id} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: (float) $result;

		return edd_currency_filter( edd_format_amount( $total ) );
	}

	public function get_order_item_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Only `COUNT` and `AVG` are accepted by this method.
		$accepted_functions = array( 'COUNT', 'AVG' );

		$function = isset( $this->query_vars['function'] ) && in_array( strtoupper( $this->query_vars['function'] ), $accepted_functions, true )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: 'COUNT(id)';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$product_id = isset( $this->query_vars['product_id'] )
			? $this->get_db()->prepare( 'AND product_id = %d', absint( $this->query_vars['product_id'] ) )
			: '';

		// Calculating an average requires a subquery.
		if ( 'AVG' === $this->query_vars['function'] ) {
			$sql = "SELECT AVG(id)
					FROM (
						SELECT COUNT(id) AS id
						FROM {$this->query_vars['table']}
						WHERE 1=1 {$product_id} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
						GROUP BY order_id
					) AS counts";
		} else {
			$sql = "SELECT {$function}
					FROM {$this->query_vars['table']}
					WHERE 1=1 {$product_id} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";
		}

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: absint( $result );

		return $total;
	}

	public function get_most_valuable_order_item( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_items;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT product_id, SUM(total) AS total
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				GROUP BY product_id
				ORDER BY total DESC
				LIMIT 1";

		$result = $this->get_db()->get_row( $sql );

		// Format resultant object.
		$result->product_id = absint( $result->product_id );
		$result->total      = edd_currency_filter( edd_format_amount( $result->total ) );

		// Add instance of EDD_Download to resultant object.
		$result->object = edd_get_download( $result->product_id );

		return $result;
	}

	/** Discounts ************************************************************/

	public function get_discount_usage_count( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$discount_code = isset( $this->query_vars['discount_code'] )
			? $this->get_db()->prepare( 'AND type = %s AND description = %s', 'discount', sanitize_text_field( $this->query_vars['discount_code'] ) )
			: $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT COUNT({$this->query_vars['column']})
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$discount_code} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0
			: absint( $result );

		return $total;
	}

	public function get_discount_savings( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'amount';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$discount_code = isset( $this->query_vars['discount_code'] )
			? $this->get_db()->prepare( 'AND type = %s AND description = %s', 'discount', sanitize_text_field( $this->query_vars['discount_code'] ) )
			: $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT SUM({$this->query_vars['column']})
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$discount_code} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: floatval( $result );

		return edd_currency_filter( edd_format_amount( $total ) );
	}

	public function get_average_discount_amount( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_order_adjustments;
		$this->query_vars['column']            = 'amount';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$type_discount = $this->get_db()->prepare( 'AND type = %s', 'discount' );

		$sql = "SELECT AVG({$this->query_vars['column']})
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$type_discount} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_var( $sql );

		$total = null === $result
			? 0.00
			: floatval( $result );

		return edd_currency_filter( edd_format_amount( $total ) );
	}

	public function get_ratio_of_discounted_orders( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'id';
		$this->query_vars['date_query_column'] = 'date_created';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql = "SELECT COUNT(id) AS total, o.discounted_orders
				FROM {$this->query_vars['table']}
				CROSS JOIN (
					SELECT COUNT(id) AS discounted_orders
					FROM {$this->query_vars['table']}
					WHERE 1=1 AND discount > 0 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				) o
				WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

		$result = $this->get_db()->get_row( $sql );

		// No need to calculate the ratio if there are no discounted orders.
		if ( 0 === (int) $result->discounted_orders ) {
			return $result->discounted_orders . ':' . $result->total;
		}

		// Calculate GCD.
		$result->total             = absint( $result->total );
		$result->discounted_orders = absint( $result->discounted_orders );

		$original_result = clone $result;

		while ( 0 !== $result->total ) {
			$remainder                 = $result->discounted_orders % $result->total;
			$result->discounted_orders = $result->total;
			$result->total             = $remainder;
		}

		$ratio = absint( $result->discounted_orders );

		// Return ratio.
		return ( $original_result->discounted_orders / $ratio ) . ':' . ( $original_result->total / $ratio );
	}

	/** Gateways *************************************************************/

	private function get_gateway_data( $query = array() ) {

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']             = $this->get_db()->edd_orders;
		$this->query_vars['column']            = 'total';
		$this->query_vars['date_query_column'] = 'date_created';
		$this->query_vars['function']          = 'COUNT';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		// Only `COUNT`, `AVG` and `SUM` are accepted by this method.
		$accepted_functions = array( 'COUNT', 'AVG', 'SUM' );

		$function = isset( $this->query_vars['function'] ) && in_array( strtoupper( $this->query_vars['function'] ), $accepted_functions, true )
			? $this->query_vars['function'] . "({$this->query_vars['column']})"
			: 'COUNT(id)';

		$gateway = isset( $this->query_vars['gateway'] )
			? $this->get_db()->prepare( 'AND gateway = %s', sanitize_text_field( $this->query_vars['gateway'] ) )
			: '';

		$groupby = empty( $gateway )
			? 'GROUP BY gateway'
			: '';

		$sql = "SELECT gateway, {$function} AS count
				FROM {$this->query_vars['table']}
				WHERE 1=1 {$gateway} {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}
				{$groupby}";

		$result = $this->get_db()->get_results( $sql );

		// Ensure count values are always valid integers if counting sales.
		if ( 'COUNT' === $this->query_vars['function'] ) {
			array_walk( $result, function ( &$value ) {
				$value->count = absint( $value->count );
			} );
		}

		if ( ! empty( $gateway ) ) {

			// Filter based on gateway if passed.
			$filter = wp_filter_object_list( $result, array( 'gateway' => $this->query_vars['gateway'] ) );

			// Return number of sales for gateway passed.
			return absint( $filter[0]->count );
		}

		// Return array of objects with gateway name and count.
		return $result;
	}

	public function get_gateway_sales( $query = array() ) {

		// Dispatch to \EDD\Orders\Stats::get_gateway_data().
		return $this->get_gateway_data( $query );
	}

	public function get_gateway_earnings( $query = array() ) {

		// Summation is required as we are returning earnings.
		$this->query_vars['function'] = 'SUM';

		// Dispatch to \EDD\Orders\Stats::get_gateway_data().
		$result = $this->get_gateway_data( $query );

		// Rename object var.
		array_walk( $result, function( &$value ) {
			$value->earnings = $value->count;
			$value->earnings = edd_currency_filter( edd_format_amount( $value->earnings ) );
			unset( $value->count );
		} );

		// Return array of objects with gateway name and earnings.
		return $result;
	}

	public function get_gateway_refund_amount() {

	}

	public function get_gateway_average_value() {

	}

	/** Tax ******************************************************************/

	public function get_tax() {

	}

	/** Customers ************************************************************/

	public function get_customer_lifetime_value() {

	}

	public function get_customer_orders() {

	}

	public function get_customer_age() {

	}

	public function get_most_valuable_customers() {

	}

	/** Private Methods ******************************************************/

	/**
	 * Parse query vars to be passed to the calculation methods.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @see \EDD\Orders\Stats::__construct()
	 *
	 * @param array $query Array of arguments. See \EDD\Orders\Stats::__construct().
	 */
	private function parse_query( $query = array() ) {
		$query_var_defaults = array(
			'start'             => '',
			'end'               => '',
			'where_sql'         => '',
			'date_query_sql'    => '',
			'date_query_column' => '',
			'column'            => '',
			'table'             => '',
			'function'          => 'SUM',
		);

		if ( empty( $this->query_vars ) ) {
			$this->query_vars = wp_parse_args( $query, $query_var_defaults );
		} else {
			$this->query_vars = wp_parse_args( $query, $this->query_vars );
		}

		// Use Carbon to set up start and end date based on range passed.
		if ( isset( $this->query_vars['range'] ) && isset( $this->date_ranges[ $this->query_vars['range'] ] ) ) {
			$this->query_vars['start'] = $this->date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			$this->query_vars['end']   = $this->date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
		}

		// Correctly format functions and column names.
		if ( isset( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = strtoupper( $this->query_vars['function'] );
		}

		if ( isset( $this->query_vars['column'] ) ) {
			$this->query_vars['column'] = strtolower( $this->query_vars['column'] );
		}

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Orders\Stats &$this The \EDD\Orders\Stats (passed by reference).
		 */
		do_action_ref_array( 'edd_order_stats_parse_query', array( &$this ) );
	}

	/**
	 * Ensures arguments exist before going ahead and calculating statistics.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param array $query
	 */
	private function pre_query( $query = array() ) {
		if ( ! empty( $query ) ) {
			$this->parse_query( $query );
		}

		// Generate date query SQL if dates have been set.
		if ( isset( $this->query_vars['start'] ) || isset( $this->query_vars['end'] ) ) {
			$date_query_sql = "AND {$this->query_vars['table']}.{$this->query_vars['date_query_column']} ";

			if ( isset( $this->query_vars['start'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( '>= %s', $this->query_vars['start'] );
			}

			// Join dates with `AND` if start and end date set.
			if ( isset( $this->query_vars['start'] ) && isset( $this->query_vars['end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( isset( $this->query_vars['end'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} <= %s", $this->query_vars['end'] );
			}

			$this->query_vars['date_query_sql'] = $date_query_sql;
		}
	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface.
	 *
	 * @since 3.0
	 * @access private
	 * @static
	 *
	 * @return \wpdb|\stdClass
	 */
	private static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new \stdClass();
	}

	/** Private Setters ******************************************************/

	/**
	 * Set up the date ranges available.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function set_date_ranges() {
		$date = EDD()->utils->date( 'now' );

		$date_filters = Reports\get_dates_filter_options();

		foreach ( $date_filters as $range => $label ) {
			$this->date_ranges[ $range ] = Reports\parse_dates_for_range( $date, $range );
		}
	}
}
