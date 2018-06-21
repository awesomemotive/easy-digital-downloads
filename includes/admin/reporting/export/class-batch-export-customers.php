<?php
/**
 * Batch Customers Export Class
 *
 * This class handles customer export
 *
 * @package     EDD
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Batch_Customers_Export Class
 *
 * @since 2.4
 */
class EDD_Batch_Customers_Export extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'customers';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        => __( 'ID',   'easy-digital-downloads' ),
			'name'      => __( 'Name',   'easy-digital-downloads' ),
			'email'     => __( 'Email', 'easy-digital-downloads' ),
			'purchases' => __( 'Number of Purchases', 'easy-digital-downloads' ),
			'amount'    => __( 'Customer Value', 'easy-digital-downloads' )
		);

		return $cols;
	}

	/**
	 * Get the export data.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$data = array();

		if ( ! empty( $this->download ) ) {
			// Export customers of a specific product

			$args = array(
				'product_id' => absint( $this->download ),
				'number'     => 30,
				'offset'     => 30 * ( $this->step - 1 ),
			);

			if( null !== $this->price_id ) {
				$args['price_id'] = (int) $this->price_id;
			}

			$order_items = edd_get_order_items( $args );

			if ( $order_items ) {
				foreach ( $order_items as $item ) {
					/** @var EDD\Orders\Order_Item $item */

					$order = edd_get_order( $item->order_id );

					$customer = new EDD_Customer( $order->customer_id );

					$data[] = array(
						'id'        => $customer->id,
						'name'      => $customer->name,
						'email'     => $customer->email,
						'purchases' => $customer->purchase_count,
						'amount'    => edd_format_amount( $customer->purchase_value ),
					);
				}
			}
		} else {

			// Export all customers
			$customers = edd_get_customers( array(
				'number' => 30,
				'offset' => 30 * ( $this->step - 1 )
			) );

			$i = 0;

			foreach ( $customers as $customer ) {
				$data[ $i ]['id']        = $customer->id;
				$data[ $i ]['name']      = $customer->name;
				$data[ $i ]['email']     = $customer->email;
				$data[ $i ]['purchases'] = $customer->purchase_count;
				$data[ $i ]['amount']    = edd_format_amount( $customer->purchase_value );

				$i++;
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {
		$percentage = 0;

		// We can't count the number when getting them for a specific download.
		if ( empty( $this->download ) ) {
			$total = edd_count_customers();

			if ( $total > 0 ) {
				$percentage = ( ( 30 * $this->step ) / $total ) * 100;
			}
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the Customers export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] )            ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end']  )             ? sanitize_text_field( $request['end']  )  : '';
		$this->download = isset( $request['download']         ) ? absint( $request['download']         )   : null;
		$this->price_id = ! empty( $request['edd_price_option'] ) && 0 !== $request['edd_price_option'] ? absint( $request['edd_price_option'] )   : null;
	}
}
