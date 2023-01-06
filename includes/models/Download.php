<?php
/**
 * Download.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2022, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.0
 */

namespace EDD\Models;

class Download {

	/**
	 * The download ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The price ID.
	 *
	 * @var null|int
	 */
	public $price_id = null;

	/**
	 * Optional additional parameters that can be passed to the model.
	 *
	 * @since 3.0
	 * @var array
	 */
	public $args = array();

	/**
	 * Constructor.
	 *
	 * @param int $id The download ID.
	 */
	public function __construct( $id, $price_id = null, $args = array() ) {
		$this->id       = $id;
		$this->price_id = $price_id;
		$this->args     = $args;
	}

	/**
	 * Gets the gross number of sales for this download from the database.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_gross_sales() {
		global $wpdb;

		$product_id_sql   = $this->generate_product_id_query_sql();
		$price_id_sql     = $this->generate_price_id_query_sql();
		$order_status_sql = $this->generate_order_status_query_sql( false );
		$date_query_sql   = $this->generate_date_query_sql();

		$results = $wpdb->get_row(
			"SELECT SUM(oi.quantity) AS sales
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND o.type = 'sale'
			{$order_status_sql}
			{$date_query_sql}"
		);

		return ! empty( $results->sales ) ? intval( $results->sales ) : 0;
	}

	/**
	 * Gets the gross earnings for this download from the database.
	 *
	 * @since 3.0
	 * @return float
	 */
	public function get_gross_earnings() {
		global $wpdb;

		$product_id_sql   = $this->generate_product_id_query_sql();
		$price_id_sql     = $this->generate_price_id_query_sql();
		$order_status_sql = $this->generate_order_status_query_sql( false );
		$date_query_sql   = $this->generate_date_query_sql();

		$order_items =
			"SELECT SUM(oi.subtotal / oi.rate) AS revenue
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND o.type = 'sale'
			{$order_status_sql}
			{$date_query_sql}";
		// Fees on order items count as part of gross revenue.
		$order_adjustments =
			"SELECT SUM(oa.subtotal/ oa.rate) as revenue
			FROM {$wpdb->edd_order_adjustments} oa
			INNER JOIN {$wpdb->edd_order_items} oi ON(oi.id = oa.object_id)
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND oa.object_type = 'order_item'
			AND oa.type != 'discount'
			AND oa.total > 0
			{$date_query_sql}";

		$results = $wpdb->get_row( "SELECT SUM(revenue) AS revenue FROM ({$order_items} UNION {$order_adjustments})a" );

		return ! empty( $results->revenue ) ? floatval( edd_sanitize_amount( $results->revenue ) ) : 0.00;
	}

	/**
	 * Gets the net number of sales for this download from the database.
	 * Because a partial refund with an identical quantity as the original order will
	 * negate the original, we also sum partially refunded sales where the quantity
	 * matches the partial refund quantity.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_net_sales() {
		global $wpdb;

		$product_id_sql      = $this->generate_product_id_query_sql();
		$price_id_sql        = $this->generate_price_id_query_sql();
		$order_status_sql    = $this->generate_order_status_query_sql();
		$date_query_sql      = $this->generate_date_query_sql();

		$complete_orders =
			"SELECT SUM(oi.quantity) as sales
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND oi.status IN('complete','refunded','partially_refunded')
			{$order_status_sql} {$date_query_sql}";
		$partial_orders  =
			"SELECT SUM(oi.quantity) as sales
			FROM {$wpdb->edd_order_items} oi
			LEFT JOIN {$wpdb->edd_order_items} ri
			ON ri.parent = oi.id
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND oi.status = 'partially_refunded'
			AND oi.quantity = - ri.quantity
			{$date_query_sql}";

		$results = $wpdb->get_row( "SELECT SUM(sales) AS sales FROM ({$complete_orders} UNION {$partial_orders})a" );

		return ! empty( $results->sales ) ? $results->sales : 0;
	}

	/**
	 * Gets the net earnings for this download from the database.
	 *
	 * @since 3.0
	 * @return float
	 */
	public function get_net_earnings() {
		global $wpdb;

		$product_id_sql   = $this->generate_product_id_query_sql();
		$price_id_sql     = $this->generate_price_id_query_sql();
		$order_status_sql = $this->generate_order_status_query_sql( false );
		$date_query_sql   = $this->generate_date_query_sql();

		/**
		 * Note on the select statements:
		 * 1. This gets the net sum for revenue and sales for all orders with net statuses.
		 * 2. Because a partial refund with an identical quantity as the original order will
		 *    negate the original, we also sum partially refunded sales where the quantity
		 *    matches the partial refund quantity.
		 */
		$order_items       =
			"SELECT SUM((oi.total - oi.tax)/ oi.rate) as revenue
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND oi.status IN('complete','partially_refunded','refunded')
			{$order_status_sql} {$date_query_sql}";
		$order_adjustments =
			"SELECT SUM((oa.total - oa.tax)/ oa.rate) as revenue
			FROM {$wpdb->edd_order_adjustments} oa
			INNER JOIN {$wpdb->edd_order_items} oi ON(oi.id = oa.object_id)
			INNER JOIN {$wpdb->edd_orders} o ON oi.order_id = o.id AND o.type = 'sale' {$order_status_sql}
			WHERE {$product_id_sql}
			{$price_id_sql}
			AND oa.object_type = 'order_item'
			AND oa.type != 'discount'
			AND oi.status IN('complete','partially_refunded')
			{$date_query_sql}";

		$sql = "SELECT SUM(revenue) AS revenue FROM ({$order_items} UNION {$order_adjustments})a";

		$results = $wpdb->get_row( $sql );

		return ! empty( $results->revenue ) ? $results->revenue : 0.00;
	}

	/**
	 * Converts an array of statuses to a string for a SQL query.
	 *
	 * @since 3.0
	 * @param array $statuses
	 * @return string
	 */
	private function convert_status_array_to_string( $statuses ) {
		return implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
	}

	/**
	 * Gets the query string for the product ID.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function generate_product_id_query_sql() {
		global $wpdb;

		return $wpdb->prepare(
			'oi.product_id = %d',
			$this->id
		);
	}

	/**
	 * Gets the query string for a product price ID.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function generate_price_id_query_sql() {
		if ( is_null( $this->price_id ) || ! is_numeric( $this->price_id ) ) {
			return '';
		}
		global $wpdb;

		return $wpdb->prepare( 'AND oi.price_id = %d', $this->price_id );
	}

	/**
	 * Gets the query string for the order statuses.
	 *
	 * @since 3.0
	 * @param bool $net Whether to use net statuses.
	 * @return string
	 */
	private function generate_order_status_query_sql( $net = true ) {
		global $wpdb;
		$statuses = $net ? edd_get_net_order_statuses() : edd_get_gross_order_statuses();

		return $wpdb->prepare(
			"AND o.status IN({$this->convert_status_array_to_string( $statuses )})",
			...$statuses
		);
	}

	/**
	 * Gets the query string for the dates, if set.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function generate_date_query_sql() {
		if ( empty( $this->args['start'] ) && empty( $this->args['end'] ) ) {
			return '';
		}
		global $wpdb;
		$date_query_sql = ' AND ';

		if ( ! empty( $this->args['start'] ) ) {
			$date_query_sql .= $wpdb->prepare( 'oi.date_created >= %s', $this->args['start'] );
		}

		// Join dates with `AND` if start and end date set.
		if ( ! empty( $this->args['start'] ) && ! empty( $this->args['end'] ) ) {
			$date_query_sql .= ' AND ';
		}

		if ( ! empty( $this->args['end'] ) ) {
			$date_query_sql .= $wpdb->prepare( 'oi.date_created <= %s', $this->args['end'] );
		}

		return $date_query_sql;
	}
}
