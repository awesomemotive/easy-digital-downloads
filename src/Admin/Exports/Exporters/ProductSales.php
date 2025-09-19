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

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * ProductSales Class
 *
 * @since 2.7
 */
class ProductSales extends Exporter {

	/**
	 * The number of items to export per step.
	 *
	 * @var int
	 * @since 3.5.2
	 */
	protected $per_step = 100;

	/**
	 * The download ID for the export.
	 *
	 * @var int
	 */
	private $download_id;

	/**
	 * Set the properties specific to the product sales export.
	 *
	 * @param array $request The request data.
	 */
	public function set_properties( $request ): void {
		$this->start       = isset( $request['sales-export-start'] ) ? sanitize_text_field( $request['sales-export-start'] ) : '';
		$this->end         = isset( $request['sales-export-end'] ) ? sanitize_text_field( $request['sales-export-end'] ) : '';
		$this->download_id = isset( $request['download_id'] ) ? absint( $request['download_id'] ) : 0;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 2.7
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return int
	 */
	protected function get_total(): int {
		return edd_count_order_items( $this->get_order_item_args() );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 2.7
	 * @return array $cols All the columns
	 */
	protected function get_data_headers(): array {
		return array(
			'ID'          => __( 'Product ID', 'easy-digital-downloads' ),
			'user_id'     => __( 'User', 'easy-digital-downloads' ),
			'customer_id' => __( 'Customer ID', 'easy-digital-downloads' ),
			'email'       => __( 'Email', 'easy-digital-downloads' ),
			'name'        => __( 'Name', 'easy-digital-downloads' ),
			'download'    => edd_get_label_singular(),
			'quantity'    => __( 'Quantity', 'easy-digital-downloads' ),
			'amount'      => __( 'Item Amount', 'easy-digital-downloads' ),
			'tax'         => __( 'Tax', 'easy-digital-downloads' ),
			'currency'    => __( 'Currency', 'easy-digital-downloads' ),
			'order_id'    => __( 'Order ID', 'easy-digital-downloads' ),
			'price_id'    => __( 'Price ID', 'easy-digital-downloads' ),
			'date'        => __( 'Date', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the export type.
	 *
	 * @since 3.5.2
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'sales';
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.7
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return array|bool The data for the CSV file, false if no data to return.
	 */
	protected function get_data(): array {
		$data     = array();
		$per_step = $this->get_per_step();
		$args     = array_merge(
			$this->get_order_item_args(),
			array(
				'number' => $per_step,
				'offset' => ( $this->step * $per_step ) - $per_step,
				'order'  => 'ASC',
			)
		);

		$items = edd_get_order_items( $args );

		if ( empty( $items ) ) {
			return $data;
		}

		$order_ids = wp_list_pluck( $items, 'order_id' );
		$orders    = array();

		if ( ! empty( $order_ids ) ) {
			$order_ids   = array_unique( $order_ids );
			$orders_data = edd_get_orders(
				array(
					'id__in' => $order_ids,
				)
			);

			foreach ( $orders_data as $order ) {
				$orders[ $order->id ] = $order;
			}
		}

		foreach ( $items as $item ) {
			$order = isset( $orders[ $item->order_id ] ) ? $orders[ $item->order_id ] : null;

			if ( ! $order ) {
				continue;
			}

			$data[] = array(
				'ID'          => $item->product_id,
				'user_id'     => $order->user_id,
				'customer_id' => $order->customer_id,
				'email'       => $order->email,
				'name'        => edd_get_customer_field( $order->customer_id, 'name' ),
				'download'    => $item->product_name,
				'quantity'    => $item->quantity,
				'amount'      => edd_format_amount( $item->get_net_total() ),
				'tax'         => edd_format_amount( $item->tax ),
				'currency'    => $order->currency,
				'order_id'    => $order->id,
				'price_id'    => $item->price_id,
				'date'        => $order->date_created,
			);
		}

		return $data;
	}

	/**
	 * Gets the default order item parameters based on the class properties.
	 *
	 * @since 3.1.1.4
	 * @return array
	 */
	private function get_order_item_args() {
		$args = array(
			'status__not_in' => array( 'refunded', 'pending', 'abandoned', 'failed' ),
			'order_query'    => array(
				'type'       => 'sale',
				'status__in' => edd_get_complete_order_statuses(),
			),
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		if ( ! empty( $this->download_id ) ) {
			$args['product_id'] = $this->download_id;
		}

		return $args;
	}
}
