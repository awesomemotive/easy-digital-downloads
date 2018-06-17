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

		if ( ! empty( $query ) ) {
			$this->parse_query();
		}
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
	 */
	private function parse_query() {

		// Reset query vars each time arguments are parsed.
		$this->query_vars = array();

	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface
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
}