<?php
/**
 * Most Popular Discount Tile
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
 * Most Popular Discount Tile class.
 *
 * Displays the most popular discount for the selected date range.
 *
 * @since 3.5.1
 */
class DiscountPopular extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'most_popular_discount';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Most Popular Discount', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats();

		$r = $stats->get_most_popular_discounts(
			array(
				'number' => 1,
			)
		);

		if ( ! empty( $r ) ) {
			$r = reset( $r );

			return esc_html( $r->code . ' (' . $r->count . ')' );
		}

		return '&mdash;';
	}
}
