<?php
/**
 * Refund Rate Tile
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
 * Refund Rate Tile class.
 *
 * Displays the refund rate as a percentage for the selected date range.
 * Uses secondary context for styling.
 *
 * @since 3.5.1
 */
class RefundRate extends Tile {

	/**
	 * The context for the tile.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $context = 'secondary';

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'refund_rate';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Refund Rate', 'easy-digital-downloads' );
	}

	/**
	 * Gets the refund rate data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'output' => 'formatted',
				'status' => edd_get_gross_order_statuses(),
			)
		);

		return $stats->get_refund_rate();
	}
}
