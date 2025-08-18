<?php
/**
 * Discount Savings Tile
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
 * Discount Savings Tile class.
 *
 * Displays the total savings from discounts for the selected date range.
 *
 * @since 3.5.1
 */
class DiscountSavings extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'customer_savings';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Customer Savings', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$stats = $this->get_stats(
			array(
				'output' => 'formatted',
			)
		);

		$discount_data = $this->get_discount_data();
		if ( $discount_data ) {
			return $stats->get_discount_savings(
				array(
					'discount_code' => $discount_data->code,
				)
			);
		}

		return $stats->get_discount_savings();
	}
}
