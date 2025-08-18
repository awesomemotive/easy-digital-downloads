<?php
/**
 * File Downloads Customer Tile
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
 * File Downloads Customer Tile class.
 *
 * Displays the average number of file downloads per customer for the selected date range.
 *
 * @since 3.5.1
 */
class FileDownloadsCustomer extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'average_file_downloads_per_customer';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Average per Customer', 'easy-digital-downloads' );
	}

	/**
	 * Gets the file downloads data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats();

		return $stats->get_average_file_download_count(
			array(
				'column' => 'customer_id',
			)
		);
	}
}
