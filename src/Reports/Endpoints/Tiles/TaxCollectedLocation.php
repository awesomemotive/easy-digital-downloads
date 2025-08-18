<?php
/**
 * Tax Collected Location Tile
 *
 * @package     EDD\Reports\Endpoints\Tiles
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Tiles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports;

/**
 * Tax Collected Location Tile class.
 *
 * Displays the tax collected for a specific location for the selected date range.
 *
 * @since 3.5.1
 */
class TaxCollectedLocation extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'total_tax_collected_for_location';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		$location = $this->get_location_data();
		$label    = '';

		if ( ! empty( $location['region'] ) && 'all' !== $location['region'] ) {
			$label = edd_get_state_name( $location['country'], $location['region'] ) . ', ';
		}

		$label .= edd_get_country_name( $location['country'] );

		return sprintf( __( 'Total Tax Collected for %s', 'easy-digital-downloads' ), $label );
	}

	/**
	 * Gets the tax data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats    = $this->get_stats(
			array(
				'function' => 'SUM',
				'output'   => 'formatted',
			)
		);
		$location = $this->get_location_data();
		$download = $this->get_download_data();
		$filter   = array_filter( array_map( 'strval', $location ) );
		if ( $download ) {
			$filter['download_id'] = $download['download_id'];
			$filter['price_id']    = (string) $download['price_id'];
		}

		return $stats->get_tax_by_location( $filter );
	}

	/**
	 * Gets the location data.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	private function get_location_data(): array {
		$country = Reports\get_filter_value( 'countries' );
		$region  = Reports\get_filter_value( 'regions' );

		return array(
			'country' => $country,
			'region'  => $region,
		);
	}
}
