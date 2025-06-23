<?php
/**
 * Payments Export Class
 *
 * This class handles payment export in batches
 *
 * @package     EDD
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Orders Class
 *
 * @since 2.4
 */
class Orders extends Exporter {

	/**
	 * The number of items to process per step.
	 *
	 * @since 3.3.8
	 * @var int
	 */
	protected $per_step = 100;

	/**
	 * The status of the orders to export.
	 *
	 * @var string
	 */
	private $status = 'all';

	/**
	 * Set the CSV columns.
	 *
	 * @since 2.4
	 * @return array $columns All the columns.
	 */
	protected function get_data_headers(): array {
		$columns = array(
			'id'           => __( 'Order ID', 'easy-digital-downloads' ), // unaltered payment ID (use for querying).
			'seq_id'       => __( 'Order Number', 'easy-digital-downloads' ), // sequential payment ID.
			'email'        => __( 'Email', 'easy-digital-downloads' ),
			'customer_id'  => __( 'Customer ID', 'easy-digital-downloads' ),
			'name'         => __( 'Customer Name', 'easy-digital-downloads' ),
			'address1'     => __( 'Address', 'easy-digital-downloads' ),
			'address2'     => __( 'Address (Line 2)', 'easy-digital-downloads' ),
			'city'         => __( 'City', 'easy-digital-downloads' ),
			'state'        => __( 'State', 'easy-digital-downloads' ),
			'country'      => __( 'Country', 'easy-digital-downloads' ),
			'zip'          => __( 'Postal / ZIP Code', 'easy-digital-downloads' ),
			'products'     => __( 'Products (Verbose)', 'easy-digital-downloads' ),
			'products_raw' => __( 'Products (Raw)', 'easy-digital-downloads' ),
			'skus'         => __( 'SKUs', 'easy-digital-downloads' ),
			'currency'     => __( 'Currency', 'easy-digital-downloads' ),
			'amount'       => __( 'Amount', 'easy-digital-downloads' ),
			'tax'          => __( 'Tax', 'easy-digital-downloads' ),
			'discount'     => __( 'Discount Code', 'easy-digital-downloads' ),
			'gateway'      => __( 'Payment Method', 'easy-digital-downloads' ),
			'trans_id'     => __( 'Transaction ID', 'easy-digital-downloads' ),
			'key'          => __( 'Purchase Key', 'easy-digital-downloads' ),
			'date'         => __( 'Date', 'easy-digital-downloads' ),
			'user'         => __( 'User', 'easy-digital-downloads' ),
			'ip'           => __( 'IP Address', 'easy-digital-downloads' ),
			'mode'         => __( 'Mode (Live|Test)', 'easy-digital-downloads' ),
			'status'       => __( 'Status', 'easy-digital-downloads' ),
			'country_name' => __( 'Country Name', 'easy-digital-downloads' ),
			'state_name'   => __( 'State Name', 'easy-digital-downloads' ),
		);

		if ( ! edd_use_skus() ) {
			unset( $columns['skus'] );
		}

		if ( ! edd_get_option( 'enable_sequential' ) ) {
			unset( $columns['seq_id'] );
		}

		return $columns;
	}

	/**
	 * Get the export data.
	 *
	 * @since 2.4
	 * @since 3.0 Updated to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	protected function get_data(): array {
		$data     = array();
		$per_step = $this->get_per_step();
		$args     = array_merge(
			$this->get_base_order_args(),
			array(
				'number'  => $per_step,
				'offset'  => ( $this->step * $per_step ) - $per_step,
				'order'   => 'ASC',
				'orderby' => 'date_created',
			)
		);

		$orders = edd_get_orders( $args );
		if ( empty( $orders ) ) {
			return $data;
		}

		foreach ( $orders as $order ) {
			/** @var EDD\Orders\Order $order */

			$items        = $order->get_items();
			$address      = $order->get_address();
			$total        = $order->total;
			$user_id      = ! empty( $order->user_id ) ? $order->user_id : $order->email;
			$customer     = edd_get_customer( $order->customer_id );
			$products     = '';
			$products_raw = '';
			$skus         = '';

			$discounts = $order->get_discounts();
			$discounts = ! empty( $discounts )
				? implode( ', ', $discounts )
				: __( 'none', 'easy-digital-downloads' );

			foreach ( $items as $key => $item ) {
				/** @var EDD\Orders\Order_Item $item */

				// Setup item information.
				$id       = $item->product_id;
				$qty      = $item->quantity;
				$price    = $item->amount;
				$tax      = $item->tax;
				$price_id = $item->price_id;

				// Set up verbose product column.
				$products .= html_entity_decode( get_the_title( $id ) );

				if ( $qty > 1 ) {
					$products .= html_entity_decode( ' (' . $qty . ')' );
				}

				$products .= ' - ';

				if ( edd_use_skus() ) {
					$sku = edd_get_download_sku( $id );

					if ( ! empty( $sku ) ) {
						$skus .= $sku;
					}
				}

				if ( 0 < $item->price_id ) {
					$products .= html_entity_decode( edd_get_price_option_name( $id, $item->price_id, $order->id ) ) . ' - ';
				}

				$products .= html_entity_decode( edd_currency_filter( edd_format_amount( $price ), $order->currency ) );

				if ( $key != ( count( $items ) - 1 ) ) {
					$products .= ' / ';

					if ( edd_use_skus() ) {
						$skus .= ' / ';
					}
				}

				// Set up raw products column; nothing but product names.
				$products_raw .= html_entity_decode( get_the_title( $id ) ) . '|' . $price . '{' . $tax . '}';

				// If we have a price ID, include it.
				if ( false !== $price_id ) {
					$products_raw .= '{' . $price_id . '}';
				}

				if ( $key != ( count( $items ) - 1 ) ) {
					$products_raw .= ' / ';
				}
			}

			$user = is_numeric( $user_id )
				? get_userdata( $user_id )
				: false;

			$name = ! empty( $customer->name ) ? $customer->name : '';
			if ( preg_match( '~^[+\-=@]~m', $name ) ) {
				$name = "'{$name}";
			}

			$data[] = array(
				'id'           => $order->id,
				'seq_id'       => $order->get_number(),
				'email'        => $order->email,
				'customer_id'  => $order->customer_id,
				'name'         => $name,
				'address1'     => isset( $address->address ) ? $address->address : '',
				'address2'     => isset( $address->address2 ) ? $address->address2 : '',
				'city'         => isset( $address->city ) ? $address->city : '',
				'state'        => isset( $address->region ) ? $address->region : '',
				'country'      => isset( $address->country ) ? $address->country : '',
				'zip'          => isset( $address->postal_code ) ? $address->postal_code : '',
				'products'     => $products,
				'products_raw' => $products_raw,
				'skus'         => $skus,
				'currency'     => $order->currency,
				'amount'       => html_entity_decode( edd_format_amount( $total ) ), // The non-discounted item price.
				'tax'          => html_entity_decode( edd_format_amount( $order->tax ) ),
				'discount'     => $discounts,
				'gateway'      => edd_get_gateway_admin_label( $order->gateway ),
				'trans_id'     => $order->get_transaction_id(),
				'key'          => $order->payment_key,
				'date'         => $order->date_created,
				'user'         => $user ? $user->display_name : __( 'guest', 'easy-digital-downloads' ),
				'ip'           => $order->ip,
				'mode'         => $order->mode,
				'status'       => $order->status,
				'country_name' => isset( $address->country ) ? edd_get_country_name( $address->country ) : '',
				'state_name'   => isset( $address->country ) && isset( $address->region ) ? edd_get_state_name( $address->country, $address->region ) : '',
			);
		}

		return $data;
	}

	/**
	 * Gets the total number of items to export.
	 *
	 * @return int
	 */
	protected function get_total(): int {
		return edd_count_orders( $this->get_base_order_args() );
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.4.2
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ): void {
		if ( isset( $request['orders-export-start'] ) ) {
			$this->start = sanitize_text_field( $request['orders-export-start'] );
		}
		if ( isset( $request['orders-export-end'] ) ) {
			$this->end = sanitize_text_field( $request['orders-export-end'] );
		}
		if ( ! empty( $request['status'] ) ) {
			$this->status = sanitize_text_field( $request['status'] );
		}
	}

	/**
	 * Get the export type.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'orders';
	}

	/**
	 * Get the base order args.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_base_order_args() {
		$args = array(
			'type'   => 'sale',
			'status' => $this->status,
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		if ( in_array( $args['status'], array( 'any', 'all' ), true ) ) {
			unset( $args['status'] );
			$args['status__not_in'] = array( 'trash' );
		}

		return $args;
	}
}
