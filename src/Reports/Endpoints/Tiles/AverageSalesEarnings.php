<?php
/**
 * Average Sales/Earnings Tile
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
 * Average Sales/Earnings Tile class.
 *
 * Displays the average sales and earnings for the selected date range.
 *
 * @since 3.5.1
 */
class AverageSalesEarnings extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'average_download_sales_earnings';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Average Sales / Earnings', 'easy-digital-downloads' );
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_chart_label(): string {
		return parent::get_chart_label() . ' &mdash; ' . __( 'Net', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'function' => 'AVG',
				'output'   => 'formatted',
			)
		);

		return $stats->get_order_item_count() . ' / ' . $stats->get_order_item_earnings();
	}
}
