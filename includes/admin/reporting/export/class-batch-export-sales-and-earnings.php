<?php
/**
 * Sales and Earnings Export Class.
 *
 * This class handles sales and earnings export on a day-by-day basis.
 *
 * @package    EDD
 * @subpackage Admin/Reporting/Export
 * @copyright  Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_Payments_Export Class
 *
 * @since 2.4
 */
class EDD_Batch_Sales_And_Earnings_Export extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $export_type = 'sales_and_earnings';

	/**
	 * Download ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $download_id = 0;

	/**
	 * Customer ID.
	 *
	 * @since 3.0
	 * @access protected
	 * @var int
	 */
	protected $customer_id = 0;

	/**
	 * Set the CSV columns.
	 *
	 * @since 3.0
	 *
	 * @return array $cols CSV columns.
	 */
	public function csv_cols() {
		$cols = array(
			'date'     => __( 'Date', 'easy-digital-downloads' ),
			'sales'    => __( 'Sales', 'easy-digital-downloads' ),
			'earnings' => __( 'Earnings', 'easy-digital-downloads' ),
		);

		return $cols;
	}

	/**
	 * Get the export data.
	 *
	 * @since 3.0
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$args = array(
			'number' => 30,
			'offset' => ( $this->step * 30 ) - 30,
		);

		$status         = "AND {$wpdb->edd_orders}.status IN ( '" . implode( "', '", $wpdb->_escape( edd_get_complete_order_statuses() ) ) . "' )";
		$date_query_sql = '';

		// Customer ID.
		$customer_id = ! empty( $this->customer_id )
			? $wpdb->prepare( "AND {$wpdb->edd_orders}.customer_id = %d", $this->customer_id )
			: '';

		// Download ID.
		$download_id = ! empty( $this->download_id )
			? $wpdb->prepare( "AND {$wpdb->edd_order_items}.product_id = %d", $this->download_id )
			: '';

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {

			// Fetch GMT offset.
			$offset = EDD()->utils->get_gmt_offset();

			$date_query_sql = 'AND ';

			if ( ! empty( $this->start ) ) {
				$this->start = date( 'Y-m-d 00:00:00', strtotime( $this->start ) );

				$this->start = 0 < $offset
					? EDD()->utils->date( $this->start )->subSeconds( $offset )->format( 'mysql' )
					: EDD()->utils->date( $this->start )->addSeconds( $offset )->format( 'mysql' );

				$date_query_sql .= $wpdb->prepare( "{$wpdb->edd_orders}.date_created >= %s", $this->start );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->start ) && ! empty( $this->end ) ) {
				$this->end = date( 'Y-m-d 23:59:59', strtotime( $this->end ) );

				$this->end = 0 < $offset
					? EDD()->utils->date( $this->end )->addSeconds( $offset )->format( 'mysql' )
					: EDD()->utils->date( $this->end )->subSeconds( $offset )->format( 'mysql' );

				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->end ) ) {
				$date_query_sql .= $wpdb->prepare( "{$wpdb->edd_orders}.date_created <= %s", $this->end );
			}
		}

		// Look in orders table if a product ID was not passed.
		if ( 0 === $this->download_id ) {
			$sql = "
				SELECT COUNT(id) AS sales, SUM(total) AS earnings, date_created
				FROM {$wpdb->edd_orders}
				WHERE 1=1 {$status} {$customer_id} {$date_query_sql}
				GROUP BY YEAR(date_created), MONTH(date_created), DAY(date_created)
	            ORDER BY YEAR(date_created), MONTH(date_created), DAY(date_created) ASC
	            LIMIT {$args['offset']}, {$args['number']}
			";

		// Join orders and order items table.
		} else {
			$sql = "
				SELECT SUM({$wpdb->edd_order_items}.quantity) AS sales, SUM({$wpdb->edd_order_items}.total) AS earnings, {$wpdb->edd_orders}.date_created
				FROM {$wpdb->edd_orders}
				INNER JOIN {$wpdb->edd_order_items} ON {$wpdb->edd_orders}.id = {$wpdb->edd_order_items}.order_id
				WHERE 1=1 {$status} {$download_id} {$date_query_sql}
				GROUP BY YEAR({$wpdb->edd_orders}.date_created), MONTH({$wpdb->edd_orders}.date_created), DAY({$wpdb->edd_orders}.date_created)
	            ORDER BY YEAR({$wpdb->edd_orders}.date_created), MONTH({$wpdb->edd_orders}.date_created), DAY({$wpdb->edd_orders}.date_created) ASC
	            LIMIT {$args['offset']}, {$args['number']}
			";
		}

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $result ) {

			// Localize the returned time.
			$d = EDD()->utils->date( $result->date_created, null, true )->format( 'date' );

			$sales = isset( $result->sales )
				? absint( $result->sales )
				: 0;

			$earnings = isset( $result->earnings )
				? edd_format_amount( $result->earnings )
				: floatval( 0 );

			$data[] = array(
				'date'     => $d,
				'sales'    => $sales,
				'earnings' => $earnings,
			);
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = array(
			'fields' => 'ids',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		$total = edd_count_orders( $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the sales and earnings export.
	 *
	 * @since 3.0
	 *
	 * @param array $request Form data passed to the batch processor.
	 */
	public function set_properties( $request ) {
		$this->start = isset( $request['order-export-start'] )
			? sanitize_text_field( $request['order-export-start'] )
			: '';

		$this->end = isset( $request['order-export-end'] )
			? sanitize_text_field( $request['order-export-end'] )
			: '';

		$this->download_id = isset( $request['download_id'] )
			? absint( $request['download_id'] )
			: 0;

		$this->customer_id = isset( $request['customer_id'] )
			? absint( $request['customer_id'] )
			: 0;
	}
}
