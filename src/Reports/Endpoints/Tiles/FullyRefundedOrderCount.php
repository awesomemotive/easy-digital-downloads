<?php
/**
 * Refund Count Tile
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
 * Fully Refunded Order Count Tile class.
 *
 * Displays the number of fully refunded orders for the selected date range.
 *
 * @since 3.5.1
 */
class FullyRefundedOrderCount extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'fully_refunded_order_count';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Number of Fully Refunded Orders', 'easy-digital-downloads' );
	}

	/**
	 * Gets the refund count data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats();

		return $stats->get_order_refund_count(
			array(
				'fully_refunded' => true,
			)
		);
	}
}
