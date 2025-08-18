<?php
/**
 * Most Downloaded Product Tile
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
 * Most Downloaded Product Tile class.
 *
 * Displays the most downloaded product for the selected date range.
 *
 * @since 3.5.1
 */
class FileDownloadsProduct extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'most_downloaded_product';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Most Downloaded Product', 'easy-digital-downloads' );
	}

	/**
	 * Gets the file downloads data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats();

		$download = $stats->get_most_downloaded_products();
		if ( $download ) {
			return esc_html( $download[0]->object->post_title );
		}

		return '&mdash;';
	}
}
