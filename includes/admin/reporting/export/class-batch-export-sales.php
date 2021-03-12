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
	 * Set the CSV columns
	 *
	 * @since 2.7
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'          => __( 'Log ID', 'easy-digital-downloads' ),
			'user_id'     => __( 'User', 'easy-digital-downloads' ),
			'customer_id' => __( 'Customer ID', 'easy-digital-downloads' ),
			'email'       => __( 'Email', 'easy-digital-downloads' ),
			'first_name'  => __( 'First Name', 'easy-digital-downloads' ),
			'last_name'   => __( 'Last Name', 'easy-digital-downloads' ),
			'download'    => edd_get_label_singular(),
			'quantity'    => __( 'Quantity', 'easy-digital-downloads' ),
			'amount'      => __( 'Item Amount', 'easy-digital-downloads' ),
			'payment_id'  => __( 'Payment ID', 'easy-digital-downloads' ),
			'price_id'    => __( 'Price ID', 'easy-digital-downloads' ),
			'date'        => __( 'Date', 'easy-digital-downloads' ),
		);

		return $cols;
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

		$args = array(
			'number' => 30,
			'offset' => ( $this->step * 30 ) - 30,
			'order'  => 'ASC',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		if ( 0 !== $this->download_id ) {
			$args['product_id'] = $this->download_id;
		}

		$items = edd_get_order_items( $args );

		foreach ( $items as $item ) {
			/** @var EDD\Orders\Order_Item $item */
			$order     = edd_get_order( $item->order_id );
			$download  = edd_get_download( $item->product_id );
			$user_info = $order->get_user_info();

			$download_title = $item->product_name;

			// Maybe append variable price name.
			if ( $download->has_variable_prices() ) {
				$price_option = edd_get_price_option_name( $item->product_id, $item->price_id, $order->id );

				$download_title .= ! empty( $price_option )
					? ' - ' . $price_option
					: '';
			}

			$data[] = array(
				'ID'          => $item->product_id,
				'user_id'     => $order->user_id,
				'customer_id' => $order->customer_id,
				'email'       => $order->email,
				'first_name'  => isset( $user_info['first_name'] ) ? $user_info['first_name'] : '',
				'last_name'   => isset( $user_info['last_name'] ) ? $user_info['last_name'] : '',
				'download'    => $download_title,
				'quantity'    => $item->quantity,
				'amount'      => $order->total,
				'payment_id'  => $order->id,
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
		$args = array(
			'fields' => 'ids',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		if ( 0 !== $this->download_id ) {
			$args['product_id'] = $this->download_id;
		}

		$total = edd_count_order_items( $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['orders-export-start'] ) ? sanitize_text_field( $request['orders-export-start'] ) : '';
		$this->end         = isset( $request['orders-export-end'] ) ? sanitize_text_field( $request['orders-export-end'] ) . ' 23:59:59' : '';
		$this->download_id = isset( $request['download_id'] ) ? absint( $request['download_id'] ) : 0;
	}
}
