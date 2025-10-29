<?php
/**
 * Trait for shared functionality between Download Earnings and Sales by Variations reports.
 *
 * @package EDD\Reports\Endpoints\Pies\Traits
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.3
 */

namespace EDD\Reports\Endpoints\Pies\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trait DownloadVariations
 *
 * @since 3.5.3
 */
trait DownloadVariations {

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
		 * Gets the labels for the pie chart.
		 *
		 * @since 3.5.1
		 * @return array
		 */
	protected function get_labels(): array {
		$download_data = $this->get_download_data();
		$labels        = array();
		foreach ( array_keys( $this->get_processed_results() ) as $piece ) {
			if ( ! is_numeric( $piece ) ) {
				$labels[] = $piece;
				continue;
			}
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

		// Return price IDs as keys with price IDs as values for proper array structure.
		$price_ids = array_keys( $download->get_prices() );

		return array_combine( $price_ids, $price_ids );
	}

	/**
	 * Formats a breakdown label for display in tooltips.
	 * Converts price IDs to human-readable price option names.
	 *
	 * @since 3.5.3
	 * @param string $piece The original piece identifier (price ID).
	 * @return string The formatted label.
	 */
	protected function format_breakdown_label( string $piece ): string {
		$download_data = $this->get_download_data();
		if ( ! $download_data || ! is_numeric( $piece ) ) {
			return $piece;
		}

		return edd_get_price_option_name( $download_data['download_id'], $piece );
	}
}
