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

		if ( ! empty( $query ) ) {
			$this->parse_query( $query );
		}

		// Set date ranges.
		$this->set_date_ranges();
	}

	/** Calculation Methods ***************************************************/

	/** Orders ***************************************************************/

	public function get_order_earnings() {
		
	}

	public function get_order_count() {

	}

	public function get_order_refund_count() {

	}

	public function get_order_refund_amount() {

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

	/** Private Parsers *******************************************************/

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
		$this->query_vars = $query;

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Orders\Stats &$this The \EDD\Orders\Stats (passed by reference).
		 */
		do_action_ref_array( 'edd_order_stats_parse_query', array( &$this ) );
	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface.
	 *
	 * @since 3.0
	 * @access private
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

		if ( array_key_exists( 'range', $this->query_vars ) && array_key_exists( $this->query_vars['range'], $this->date_ranges ) ) {
			$this->query_vars['start'] = $this->date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			$this->query_vars['end']   = $this->date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
		}
	}
}