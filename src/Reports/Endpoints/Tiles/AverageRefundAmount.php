<?php
/**
 * Average Refund Amount Tile
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
 * Average Refund Amount Tile class.
 *
 * Displays the average refund amount for the selected date range.
 *
 * @since 3.5.1
 */
class AverageRefundAmount extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'average_refund_amount';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Average Refund Amount', 'easy-digital-downloads' );
	}

	/**
	 * Gets the refund amount data.
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

		return $stats->get_order_refund_amount();
	}
}
