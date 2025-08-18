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
class GatewaySales extends Pie {

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
		return 'gateway_sales_breakdown';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Gateway Sales', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		$stats = new \EDD\Stats();
		$args  = array(
			'range'    => $this->dates['range'],
			'grouped'  => true,
			'currency' => $this->currency,
		);

		return $stats->get_gateway_sales( $args );
	}

	/**
	 * Gets the labels for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_labels(): array {
		return array_map( 'edd_get_gateway_admin_label', $this->get_pieces() );
	}

	/**
	 * Gets the pieces for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_pieces(): array {
		return array_keys( edd_get_payment_gateways() );
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 *
	 * @since 3.5.1
	 * @param array $query_results Database query results.
	 */
	protected function process_results( array $query_results ): array {
		// Get all available gateways.
		$gateways = array_flip( array_keys( edd_get_payment_gateways() ) );

		// Initialize all gateways with 0 sales.
		foreach ( $gateways as $gateway => $value ) {
			$gateways[ $gateway ] = 0;
		}

		// Populate with actual data from query results.
		foreach ( $query_results as $result ) {
			if ( isset( $gateways[ $result->gateway ] ) ) {
				$gateways[ $result->gateway ] = (int) $result->total;
			}
		}

		return array_values( $gateways );
	}
}
