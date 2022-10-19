<?php
/**
 * Taxed Customers Export Class.
 *
 * This class handles the taxed orders export in batches.
 *
 * @package     EDD
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_Taxed_Orders_Export Class
 *
 * @since 3.0
 */
class EDD_Batch_Taxed_Customers_Export extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 3.0
	 */
	public $export_type = 'taxed_customers';

	/**
	 * Set the CSV columns
	 *
	 * @since 3.0
	 *
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'        => __( 'ID', 'easy-digital-downloads' ),
			'name'      => __( 'Name', 'easy-digital-downloads' ),
			'email'     => __( 'Email', 'easy-digital-downloads' ),
			'purchases' => __( 'Number of Purchases', 'easy-digital-downloads' ),
			'amount'    => __( 'Customer Value', 'easy-digital-downloads' ),
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
		$data = array();

		$args = array(
			'number'     => 30,
			'offset'     => ( $this->step * 30 ) - 30,
			'status__in' => edd_get_complete_order_statuses(),
			'order'      => 'ASC',
			'orderby'    => 'date_created',
			'fields'     => 'customer_id',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		add_filter( 'edd_orders_query_clauses', array( $this, 'query_clauses' ), 10, 2 );

		$customer_ids = edd_get_orders( $args );

		remove_filter( 'edd_orders_query_clauses', array( $this, 'query_clauses' ), 10 );

		$customer_ids = array_unique( $customer_ids );

		asort( $customer_ids );

		foreach ( $customer_ids as $customer_id ) {

			// Bail if a customer ID was not set.
			if ( 0 === $customer_id ) {
				continue;
			}

			$customer = edd_get_customer( $customer_id );

			// Bail if a customer record does not exist.
			if ( ! $customer ) {
				continue;
			}

			$name = ! empty( $customer->name ) ? $customer->name : '';
			if ( preg_match( '~^[+\-=@]~m', $name ) ) {
				$name = "'{$name}";
			}

			$data[] = array(
				'id'        => $customer->id,
				'name'      => $name,
				'email'     => $customer->email,
				'purchases' => $customer->purchase_count,
				'amount'    => edd_format_amount( $customer->purchase_value ),
			);
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = array(
			'fields'     => 'ids',
			'status__in' => edd_get_complete_order_statuses(),
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		add_filter( 'edd_orders_query_clauses', array( $this, 'query_clauses' ), 10, 2 );

		$total = edd_count_orders( $args );

		remove_filter( 'edd_orders_query_clauses', array( $this, 'query_clauses' ), 10 );

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
	 * Set the properties specific to the taxed orders export.
	 *
	 * @since 3.0
	 *
	 * @param array $request The form data passed into the batch processing.
	 */
	public function set_properties( $request ) {
		$this->start = isset( $request['taxed-customers-export-start'] ) ? sanitize_text_field( $request['taxed-customers-export-start'] ) : '';
		$this->end   = isset( $request['taxed-customers-export-end'] ) ? sanitize_text_field( $request['taxed-customers-export-end'] ) : '';
	}

	/**
	 * Filter the database query to only return orders which have tax applied to them.
	 *
	 * @since 3.0
	 *
	 * @param array               $clauses A compacted array of item query clauses.
	 * @param \EDD\Database\Query $base    Instance passed by reference.
	 *
	 * @return array
	 */
	public function query_clauses( $clauses, $base ) {
		$clauses['where'] = ! empty( $clauses['where'] )
			? $clauses['where'] .= ' AND tax > 0'
			: 'tax > 0';

		return $clauses;
	}
}
