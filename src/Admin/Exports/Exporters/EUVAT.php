<?php
/**
 * EU VAT exporter.
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
class EUVAT extends Exporter {
	use Traits\TempData;

	/**
	 * The export key.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	private $export_key = '';

	/**
	 * The cache key.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	private $cache_key;

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
		if ( ! empty( $request['eu-vat-export-start'] ) ) {
			$this->start = sanitize_text_field( $request['eu-vat-export-start'] );
		}
		if ( ! empty( $request['eu-vat-export-end'] ) ) {
			$this->end = sanitize_text_field( $request['eu-vat-export-end'] );
		}
		if ( ! empty( $request['export-key'] ) ) {
			$this->export_key = sanitize_text_field( $request['export-key'] );
		}
		if ( ! empty( $request['eu_vat_country'] ) ) {
			$this->countries[] = sanitize_text_field( $request['eu_vat_country'] );
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
		$this->cache_key = "edd_eu_vat_export_{$this->export_key}";
	}

	/**
	 * Get the export type.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	protected function get_export_type(): string {
		return 'eu_vat';
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 3.5.0
	 * @return array The columns for the CSV file.
	 */
	protected function get_data_headers(): array {
		return array(
			'country'  => __( 'Country', 'easy-digital-downloads' ),
			'vat_rate' => __( 'Standard VAT Rate', 'easy-digital-downloads' ),
			'amount'   => __( 'Value of Items (Excluding VAT)', 'easy-digital-downloads' ),
			'vat'      => __( 'VAT', 'easy-digital-downloads' ),
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
	 * Gets the data being exported for one step.
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
			$country_name = edd_get_country_name( $address->country );
			if ( ! array_key_exists( $address->country, $data ) ) {
				$data[ $address->country ] = array(
					'country'  => $country_name,
					'amount'   => 0,
					'vat'      => 0,
					'vat_rate' => $this->get_tax_rate( $address, $order->id ),
				);
			}

			$data[ $address->country ]['amount'] += $order->total - $order->tax;
			$data[ $address->country ]['vat']    += $order->tax;
		}

		return $data;
	}

	/**
	 * Gets the parameters for the orders query.
	 *
	 * @since 3.5.0
	 * @return array The order arguments.
	 */
	private function get_order_args(): array {
		$args = array(
			'status__in'   => array( 'complete', 'edd_subscription' ),
			'tax__compare' => array(
				'relation' => 'AND',
				array(
					'value'   => '0',
					'compare' => '>',
				),
			),
		);
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = $this->get_date_query();
		}

		return $args;
	}

	/**
	 * Get the tax rate for an order.
	 *
	 * @param \EDD\Orders\Order_Address $address The address of the order.
	 * @param int                       $order_id The ID of the order.
	 * @return string The tax rate.
	 */
	private function get_tax_rate( $address, $order_id ) {
		$rate = edd_get_tax_rate_by_location( array( 'country' => $address->country ) );
		if ( $rate ) {
			$amount = $rate->amount;
		} else {
			$amount = edd_get_order_meta( $order_id, 'tax_rate', true );
		}

		if ( ! $amount ) {
			$amount = 0;
		}

		return sprintf( '%s%%', $amount );
	}
}
