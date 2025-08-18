<?php
/**
 * Most Valuable Download Tile
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
 * Most Valuable Download Tile class.
 *
 * Displays the most valuable download for the selected date range.
 *
 * @since 3.5.1
 */
class MostValuableDownload extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'most_valuable_download';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		/* translators: %s: Download singular label */
		return sprintf( __( 'Most Valuable %s', 'easy-digital-downloads' ), edd_get_label_singular() );
	}

	/**
	 * Gets the most valuable download data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'function' => 'SUM',
			)
		);

		$items = $stats->get_most_valuable_order_items();

		if ( ! empty( $items ) && isset( $items[0] ) ) {
			$download = $items[0];

			if ( $download->object instanceof \EDD_Download ) {
				return edd_get_download_name( $download->object->ID, $download->price_id );
			}
		}

		return __( 'N/A', 'easy-digital-downloads' );
	}
}
