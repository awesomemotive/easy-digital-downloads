<?php
/**
 * Customer Average Orders Tile
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
 * Customer Average Tile class.
 *
 * Displays the average revenue per customer for the selected date range.
 *
 * @since 3.5.1
 */
class CustomerAverageOrders extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'average_number_of_orders_per_customer';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Average Orders per Customer', 'easy-digital-downloads' );
	}

	/**
	 * Gets the average number of orders per customer data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'function' => 'AVG',
				'relative' => true,
			)
		);

		return $stats->get_customer_order_count();
	}
}
