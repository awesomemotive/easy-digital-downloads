<?php
/**
 * Download Sales/Earnings Tile
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
 * Download Sales/Earnings Tile class.
 *
 * Displays the average sales and earnings for the selected date range.
 *
 * @since 3.5.1
 */
class DownloadSalesEarnings extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'download_sales_earnings';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Sales / Earnings', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$download_data = $this->get_download_data();
		if ( ! $download_data ) {
			return '';
		}

		$stats = $this->get_stats(
			array(
				'product_id' => absint( $download_data['download_id'] ),
				'price_id'   => $download_data['price_id'],
				'output'     => 'formatted',
			)
		);

		$earnings = $stats->get_order_item_earnings(
			array(
				'function' => 'SUM',
			)
		);
		$sales    = $stats->get_order_item_count(
			array(
				'function' => 'COUNT',
			)
		);

		return esc_html( $sales . ' / ' . $earnings );
	}
}
