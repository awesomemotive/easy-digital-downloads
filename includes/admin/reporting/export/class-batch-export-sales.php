<?php
/**
 * Batch Sales Logs Export Class
 *
 * This class handles Sales logs export
 *
 * @package     EDD
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_Sales_Export Class
 *
 * @since 2.7
 */
class EDD_Batch_Sales_Export extends EDD_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.7
	 */
	public $export_type = 'sales';

	/**
	 * The array of order IDs.
	 *
	 * @since 3.1.1.4
	 * @var array
	 */
	private $orders;

	/**
	 * The download ID for the export.
	 *
	 * @var int
	 */
	private $download_id;

	/**
	 * Set the CSV columns
	 *
	 * @since 2.7
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		return array(
			'ID'          => __( 'Product ID', 'easy-digital-downloads' ),
			'user_id'     => __( 'User', 'easy-digital-downloads' ),
			'customer_id' => __( 'Customer ID', 'easy-digital-downloads' ),
			'email'       => __( 'Email', 'easy-digital-downloads' ),
			'name'        => __( 'Name', 'easy-digital-downloads' ),
			'download'    => edd_get_label_singular(),
			'quantity'    => __( 'Quantity', 'easy-digital-downloads' ),
			'amount'      => __( 'Item Amount', 'easy-digital-downloads' ),
			'currency'    => __( 'Currency', 'easy-digital-downloads' ),
			'order_id'    => __( 'Order ID', 'easy-digital-downloads' ),
			'price_id'    => __( 'Price ID', 'easy-digital-downloads' ),
			'date'        => __( 'Date', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.7
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return array|bool The data for the CSV file, false if no data to return.
	 */
	public function get_data() {
		$data = array();

		$args = array_merge(
			$this->get_order_item_args(),
			array(
				'number'     => 30,
				'offset'     => ( $this->step * 30 ) - 30,
				'order'      => 'ASC'
			)
		);

		$items = edd_get_order_items( $args );

		foreach ( $items as $item ) {

			if ( 'refunded' === $item->status ) {
				continue;
			}

			/** @var EDD\Orders\Order_Item $item */
			$order  = edd_get_order( $item->order_id );

			// If the item has been partially refunded, we need to calculate the amount
			$amount = array_reduce(
				$item->get_refunded_items(),
				function( $total, $refund_item ) {
					return $total + $refund_item->total;
				},
				$item->total
			);

			$data[] = array(
				'ID'          => $item->product_id,
				'user_id'     => $order->user_id,
				'customer_id' => $order->customer_id,
				'email'       => $order->email,
				'name'        => edd_get_customer_field( $order->customer_id, 'name' ),
				'download'    => $item->product_name,
				'quantity'    => $item->quantity,
				'amount'      => edd_format_amount( $amount ),
				'currency'    => $order->currency,
				'order_id'    => $order->id,
				'price_id'    => $item->price_id,
				'date'        => $order->date_created,
			);
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return ! empty( $data )
			? $data
			: false;
	}
	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 2.7
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args       = $this->get_order_item_args();
		$total      = edd_count_order_items( $args );
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
	 * Gets the default order item parameters based on the class properties.
	 *
	 * @since 3.1.1.4
	 * @return array
	 */
	private function get_order_item_args() {
		$args = array();
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		if ( ! empty( $this->download_id ) ) {
			$args['product_id'] = $this->download_id;
		}

		if ( ! empty( $this->orders ) ) {
			$args['order_id__in'] = $this->orders;
		}

		return $args;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['sales-export-start'] ) ? sanitize_text_field( $request['sales-export-start'] ) : '';
		$this->end         = isset( $request['sales-export-end'] ) ? sanitize_text_field( $request['sales-export-end'] ) : '';
		$this->download_id = isset( $request['download_id'] ) ? absint( $request['download_id'] ) : 0;
		$this->orders      = $this->get_orders();
	}

	/**
	 * Gets the array of complete order IDs for the time period.
	 *
	 * @return array
	 */
	private function get_orders() {
		$args = array(
			'fields'     => 'ids',
			'type'       => 'sale',
			'number'     => 999999999,
			'status__in' => edd_get_complete_order_statuses(),
		);
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		return edd_get_orders( $args );
	}
}
