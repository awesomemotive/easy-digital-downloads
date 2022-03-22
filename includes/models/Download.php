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
	 * Constructor.
	 *
	 * @param int $id The download ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Gets the gross number of sales for this download from the database.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function get_gross_sales() {
		global $wpdb;

		$statuses      = edd_get_gross_order_statuses();
		$status_string = $this->convert_status_array_to_string( $statuses );
		$results       = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT SUM(oi.quantity) AS sales
				FROM {$wpdb->edd_order_items} oi
				INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
				WHERE oi.product_id = %d
				AND o.type = 'sale'
				AND o.status IN({$status_string})",
				$this->id,
				...$statuses
			)
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

		$statuses      = edd_get_gross_order_statuses();
		$status_string = $this->convert_status_array_to_string( $statuses );
		$order_items   = $wpdb->prepare(
			"SELECT SUM(oi.subtotal / oi.rate) AS revenue
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE oi.product_id = %d
			AND o.type = 'sale'
			AND o.status IN({$status_string})",
			$this->id,
			...$statuses
		);
		// Fees on order items count as part of gross revenue.
		$order_adjustments = $wpdb->prepare(
			"SELECT SUM(oa.subtotal/ oa.rate) as revenue
			FROM {$wpdb->edd_order_adjustments} oa
			INNER JOIN {$wpdb->edd_order_items} oi ON(oi.id = oa.object_id)
			WHERE oi.product_id = %d
			AND oa.object_type = 'order_item'
			AND oa.total > 0
			AND oi.status IN('complete','partially_refunded')",
			$this->id
		);
		$results           = $wpdb->get_row( "SELECT SUM(revenue) AS revenue FROM ({$order_items} UNION {$order_adjustments})a" );

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

		$statuses      = edd_get_net_order_statuses();
		$status_string = $this->convert_status_array_to_string( $statuses );

		$complete_orders = $wpdb->prepare(
			"SELECT SUM(oi.quantity) as sales
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE oi.product_id = %d
			AND oi.status IN('complete','partially_refunded')
			AND o.status IN({$status_string})",
			$this->id,
			...$statuses
		);
		$partial_orders  = $wpdb->prepare(
			"SELECT SUM(oi.quantity) as sales
			FROM {$wpdb->edd_order_items} oi
			LEFT JOIN {$wpdb->edd_order_items} ri
			ON ri.parent = oi.id
			WHERE oi.product_id = %d
			AND oi.status = 'partially_refunded'
			AND oi.quantity = - ri.quantity",
			$this->id
		);
		$results         = $wpdb->get_row( "SELECT SUM(sales) AS sales FROM ({$complete_orders} UNION {$partial_orders})a" );

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

		$statuses      = edd_get_net_order_statuses();
		$status_string = $this->convert_status_array_to_string( $statuses );

		/**
		 * Note on the select statements:
		 * 1. This gets the net sum for revenue and sales for all orders with net statuses.
		 * 2. Because a partial refund with an identical quantity as the original order will
		 *    negate the original, we also sum partially refunded sales where the quantity
		 *    matches the partial refund quantity.
		 */
		$order_items       = $wpdb->prepare(
			"SELECT SUM((oi.total - oi.tax)/ oi.rate) as revenue
			FROM {$wpdb->edd_order_items} oi
			INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
			WHERE oi.product_id = %d
			AND oi.status IN('complete','partially_refunded')
			AND o.status IN({$status_string})",
			$this->id,
			...$statuses
		);
		$order_adjustments = $wpdb->prepare(
			"SELECT SUM((oa.total - oa.tax)/ oa.rate) as revenue
			FROM {$wpdb->edd_order_adjustments} oa
			INNER JOIN {$wpdb->edd_order_items} oi ON(oi.id = oa.object_id)
			WHERE oi.product_id = %d
			AND oa.object_type = 'order_item'
			AND oi.status IN('complete','partially_refunded')",
			$this->id
		);
		$results           = $wpdb->get_row( "SELECT SUM(revenue) AS revenue FROM ({$order_items} UNION {$order_adjustments})a" );

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
}
