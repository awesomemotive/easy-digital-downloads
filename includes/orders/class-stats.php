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

		$sql = "SELECT {$function} FROM {$this->query_vars['table']} WHERE 1=1 {$this->query_vars['date_query_sql']}";

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

		$sql = "SELECT {$function} FROM {$this->query_vars['table']} WHERE 1=1 {$this->query_vars['where_sql']} {$this->query_vars['date_query_sql']}";

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

	public function get_average_refund_time() {

	}

	public function get_refund_rate() {

	}

	/** Order Item ************************************************************/

	public function get_order_item_earnings() {

	}

	public function get_order_item_count() {

	}

	public function get_most_valuable_order_item() {

	}

	/** Discounts ************************************************************/

	public function get_discount_usage_count() {

	}

	public function get_customer_savings() {

	}

	public function get_average_discount_amount() {

	}

	/** Gateways *************************************************************/

	public function get_gateway_sales() {

	}

	public function get_gateway_earnings() {

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
			'date_query_sql'    => '',
			'date_query_column' => '',
			'column'            => '',
			'table'             => '',
			'function'          => 'SUM',
		);

		$this->query_vars = wp_parse_args( $query, $query_var_defaults );

		// Use Carbon to set up start and end date based on range passed.
		if ( isset( $this->query_vars['range'] ) && isset( $this->date_ranges[ $this->query_vars['range'] ] ) ) {
			$this->query_vars['start'] = $this->date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			$this->query_vars['end']   = $this->date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
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
	 * @return \wpdb|object
	 */
	private static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new stdClass();
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