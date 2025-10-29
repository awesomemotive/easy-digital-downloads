<?php
/**
 * Download Earnings by Variations Pie Chart
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
 * Download Earnings by Variations Pie Chart class.
 *
 * Builds pie chart data for download earnings by variations using the Pie abstract pattern.
 *
 * @since 3.5.1
 */
class DownloadEarningsByVariations extends Pie {
	use Traits\DownloadVariations;

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'earnings';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'download_earnings_by_variations';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Earnings by Variation', 'easy-digital-downloads' );
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

		return $stats->get_order_item_earnings(
			array(
				'product_id' => absint( $download_data['download_id'] ),
				'range'      => $this->dates['range'],
				'grouped'    => true,
				'currency'   => $this->currency,
			)
		);
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 *
	 * @since 3.5.1
	 * @param array $query_results Database query results.
	 */
	protected function process_results( array $query_results ): array {
		// Get all available price IDs.
		$price_ids = $this->get_pieces();

		// Initialize all price IDs with 0 earnings.
		$prices = array();
		foreach ( $price_ids as $price_id => $value ) {
			$prices[ $price_id ] = 0;
		}

		// Populate with actual data from query results.
		foreach ( $query_results as $result ) {
			if ( isset( $prices[ $result->price_id ] ) ) {
				$prices[ $result->price_id ] = (int) $result->total;
			}
		}

		// Group small pieces based on percentage threshold.
		$prices = $this->group_small_percentage_pieces( $prices );

		// If we still have more pieces than the maximum, group the smallest ones into "Other".
		if ( count( $prices ) > $this->max_pieces ) {
			$prices = $this->group_small_pieces( $prices );
		}

		return $prices;
	}
}
