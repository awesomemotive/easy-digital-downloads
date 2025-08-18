<?php
/**
 * New Customers Tile
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
 * New Customers Tile class.
 *
 * Displays the number of new customers for the selected date range.
 *
 * @since 3.5.1
 */
class CustomerGrowth extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'new_customer_growth';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'New Customers', 'easy-digital-downloads' );
	}

	/**
	 * Gets the new customers count data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'relative'       => true,
				'purchase_count' => true,
			)
		);

		return $stats->get_customer_count();
	}
}
