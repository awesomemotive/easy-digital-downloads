<?php
/**
 * EU VAT Sales Export.
 *
 * @package     EDD\Admin\Exports\Exporters
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Admin\Exports\Exporters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EUVAT Class.
 *
 * @since 3.5.0
 */
class EUVATSales extends Exporter {

	/**
	 * The countries to export.
	 *
	 * @var array
	 */
	private $countries = array();

	/**
	 * Set the properties specific to the payments export
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ): void {
		if ( ! empty( $request['eu-vat-export-sales-start'] ) ) {
			$this->start = sanitize_text_field( $request['eu-vat-export-sales-start'] );
		}
		if ( ! empty( $request['eu-vat-export-sales-end'] ) ) {
			$this->end = sanitize_text_field( $request['eu-vat-export-sales-end'] );
		}
		if ( ! empty( $request['eu_vat_sales_country'] ) ) {
			$this->countries[] = sanitize_text_field( $request['eu_vat_sales_country'] );
		} else {
			$this->countries = \EDD\Utils\Countries::get_eu_countries();
			$invoice_country = edd_get_option( 'edd_vat_address_invoice', '' );
			if ( ! empty( $invoice_country ) ) {
				$key = array_search( $invoice_country, $this->countries, true );
				if ( false !== $key ) {
					unset( $this->countries[ $key ] );
				}
			}
		}
	}

	/**
	 * Get the export type.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'eu_vat_ec_sales';
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 3.5.0
	 * @return array The columns for the CSV file.
	 */
	protected function get_data_headers(): array {
		return array(
			'country'    => __( 'EU Country Code', 'easy-digital-downloads' ),
			'vat_number' => __( 'VAT Registration Number', 'easy-digital-downloads' ),
			'amount'     => __( 'Value of Supplies', 'easy-digital-downloads' ),
			'indicator'  => __( 'Indicator', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the total number of items to export.
	 *
	 * @since 3.5.0
	 * @return int
	 */
	protected function get_total(): int {
		return edd_count_orders( $this->get_order_args() );
	}

	/**
	 * Gets the data being exported.
	 *
	 * @since 3.5.0
	 * @return array $data Data for export.
	 */
	protected function get_data(): array {
		$data           = array();
		$args           = $this->get_order_args();
		$args['number'] = $this->per_step;
		$args['offset'] = ( $this->step * $this->per_step ) - $this->per_step;

		$orders = edd_get_orders( $args );

		if ( ! $orders ) {
			return $data;
		}

		foreach ( $orders as $order ) {
			$address = $order->get_address();
			if ( empty( $address->country ) || ! in_array( $address->country, $this->countries, true ) ) {
				continue;
			}
			$data[] = array(
				'country'    => $address->country,
				'vat_number' => $this->get_order_vat_number( $order->id, $address ),
				'amount'     => edd_format_amount( $this->round_order_amount( $order ) ),
				'indicator'  => 3,
			);
		}

		return $data;
	}

	/**
	 * Gets the parameters for the orders query.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private function get_order_args() {
		$args = array(
			'status__in'   => array( 'complete', 'edd_subscription' ),
			'meta_query'   => array(
				'relation' => 'AND',
				array(
					'key'     => '_edd_payment_vat_number',
					'compare' => 'EXISTS',
				),
			),
			'tax__compare' => array(
				'relation' => 'AND',
				array(
					'value'   => '0',
					'compare' => '=',

				),
			),
		);
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		return $args;
	}

	/**
	 * Get the order VAT number.
	 *
	 * @since 3.5.0
	 * @param int                      $order_id The order ID.
	 * @param EDD\Orders\Order_Address $address  The order address.
	 * @return string The VAT number.
	 */
	private function get_order_vat_number( $order_id, $address ) {
		$tax_number = edd_get_order_meta( $order_id, '_edd_payment_vat_number', true );

		return apply_filters( 'edd_vat_export_vat_ec_sales_vat_number', str_replace( $address->country, '', $tax_number ), $order_id );
	}

	/**
	 * Helper function to choose how to round order amounts.
	 *
	 * @param \EDD\Orders\Order $order The order object.
	 * @return int
	 */
	private function round_order_amount( $order ) {
		/**
		 * Filter the amount to round.
		 *
		 * @param float $amount The amount to round.
		 * @param int   $order_id The order ID.
		 * @param \EDD\Orders\Order $order The order object.
		 * @return float The rounded amount.
		 */
		$amount = apply_filters( 'edd_vat_export_vat_ec_sales_amount', $order->total, $order->id, $order );

		return call_user_func( $this->get_rounding_mode(), $amount );
	}

	/**
	 * Get the rounding mode.
	 *
	 * @return string The rounding mode.
	 */
	private function get_rounding_mode() {
		$mode = apply_filters( 'edd_vat_export_vat_ec_sales_amount_rounding', 'default' );

		$mode_function_map = array(
			'round_up'   => 'ceil',
			'round_down' => 'floor',
			'default'    => 'round',
		);

		return array_key_exists( $mode, $mode_function_map ) ? $mode_function_map[ $mode ] : 'round';
	}
}
