<?php
/**
 * Tax Collected Tile
 *
 * @package     EDD\Reports\Endpoints\Tiles
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Tiles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Tax Collected Tile class.
 *
 * Displays the tax collected for the selected date range.
 *
 * @since 3.5.1
 */
class TaxCollected extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'total_tax_collected';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Total Tax Collected', 'easy-digital-downloads' );
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_chart_label(): string {
		$label    = parent::get_chart_label();
		$download = $this->get_download_data();
		if ( $download ) {
			$label .= ' (' . edd_get_download_name( $download['download_id'], $download['price_id'] ) . ')';
		}
		return $label;
	}

	/**
	 * Gets the sales data.
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
		$download = $this->get_download_data();
		if ( $download ) {
			return $stats->get_tax(
				array_filter( array_map( 'strval', $download ) )
			);
		}

		return $stats->get_tax();
	}
}
