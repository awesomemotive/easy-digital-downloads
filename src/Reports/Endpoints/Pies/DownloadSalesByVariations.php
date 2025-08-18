<?php
/**
 * Gateway Sales Breakdown Pie Chart
 *
 * @package     EDD\Reports\Endpoints\Pies
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Pies;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Gateway Sales Breakdown Pie Chart class.
 *
 * Builds pie chart data for gateway sales breakdown using the Pie abstract pattern.
 *
 * @since 3.5.1
 */
class DownloadSalesByVariations extends Pie {

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'sales';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'download_sales_by_variations';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Sales by Variation', 'easy-digital-downloads' );
	}

	/**
	 * Gets the heading for the chart.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		$download_data = $this->get_download_data();
		if ( ! $download_data ) {
			return $this->get_label();
		}

		return $this->get_label() . ' (' . edd_get_download_name( $download_data['download_id'] ) . ')';
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		$download_data = $this->get_download_data();
		$stats         = new \EDD\Stats();

		return $stats->get_order_item_count(
			array(
				'product_id' => absint( $download_data['download_id'] ),
				'range'      => $this->dates['range'],
				'grouped'    => true,
				'currency'   => $this->currency,
			)
		);
	}

	/**
	 * Gets the labels for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_labels(): array {
		$download_data = $this->get_download_data();
		$labels        = array();
		foreach ( $this->get_pieces() as $piece ) {
			$labels[] = edd_get_price_option_name( $download_data['download_id'], $piece );
		}

		return $labels;
	}

	/**
	 * Gets the pieces for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_pieces(): array {
		$download_data = $this->get_download_data();
		if ( ! $download_data ) {
			return array();
		}

		$download = edd_get_download( absint( $download_data['download_id'] ) );
		if ( ! $download ) {
			return array();
		}

		return array_keys( $download->get_prices() );
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 *
	 * @since 3.5.1
	 * @param array $query_results Database query results.
	 */
	protected function process_results( array $query_results ): array {
		// Get all available gateways.
		$prices = $this->get_pieces();

		// Initialize all gateways with 0 sales.
		foreach ( $prices as $price ) {
			$prices[ $price ] = 0;
		}

		// Populate with actual data from query results.
		foreach ( $query_results as $result ) {
			if ( isset( $prices[ $result->price_id ] ) ) {
				$prices[ $result->price_id ] = (int) $result->total;
			}
		}

		return array_values( $prices );
	}
}
