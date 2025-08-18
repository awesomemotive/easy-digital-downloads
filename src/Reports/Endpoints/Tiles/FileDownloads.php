<?php
/**
 * File Downloads Tile
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
 * File Downloads Tile class.
 *
 * Displays the number of file downloads for the selected date range.
 *
 * @since 3.5.1
 */
class FileDownloads extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'number_of_file_downloads';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Number of File Downloads', 'easy-digital-downloads' );
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_chart_label(): string {
		$download_data = $this->get_download_data();

		return parent::get_chart_label() . Reports\get_download_label( $download_data );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats    = $this->get_stats();
		$download = $this->get_download_data();
		if ( $download ) {
			return $stats->get_file_download_count(
				array(
					'download_id' => $download['download_id'],
					'price_id'    => (string) $download['price_id'],
				)
			);
		}

		return $stats->get_file_download_count();
	}
}
