<?php
/**
 * Refunded Amount Tile
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
 * Refunded Amount Tile class.
 *
 * Displays the total refund amount for the selected date range.
 *
 * @since 3.5.1
 */
class RefundedAmount extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'overview_refunded_amount';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Total Refund Amount', 'easy-digital-downloads' );
	}

	/**
	 * Gets the refunded amount data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'output'   => 'formatted',
				'function' => 'SUM',
				'relative' => true,
			)
		);

		return $stats->get_order_refund_amount();
	}
}
