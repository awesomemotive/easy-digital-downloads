<?php
/**
 * Average Value Per Gateway Tile
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
 * Average Gateway Earnings Tile class.
 *
 * Displays the average value for the selected date range by gateway.
 *
 * @since 3.5.1
 */
class GatewayAverage extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'average_value_per_gateway';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Average Order Value', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {

		$gateway = $this->get_gateway();
		$args    = array(
			'output' => 'formatted',
		);
		if ( ! empty( $gateway ) ) {
			$args['gateway'] = $gateway;
		}

		$stats = $this->get_stats( $args );

		if ( ! empty( $gateway ) ) {
			return $stats->get_gateway_earnings( $this->get_filter() );
		}

		return $stats->get_order_earnings( $this->get_filter() );
	}

	/**
	 * Gets the filter for the stats.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	private function get_filter(): array {
		$filter       = array( 'function' => 'AVG' );
		$order_status = $this->get_order_status();
		if ( ! empty( $order_status ) ) {
			$filter['status'] = array( $order_status );
		}

		return $filter;
	}
}
