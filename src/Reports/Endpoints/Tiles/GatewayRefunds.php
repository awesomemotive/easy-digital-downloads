<?php
/**
 * Gateway Refunds Tile
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
 * Gateway Refunds Tile class.
 *
 * Displays the number of refunds for the selected date range by gateway.
 *
 * @since 3.5.1
 */
class GatewayRefunds extends Tile {

	/**
	 * Gets the tile endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'refunds_per_gateway';
	}

	/**
	 * Gets the tile label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Refunds', 'easy-digital-downloads' );
	}

	/**
	 * Gets the sales data.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_data(): string {
		$args  = array(
			'revenue_type' => 'net',
			'relative'     => true,
			'gateway'      => $this->get_gateway(),
			'output'       => 'formatted',
		);
		$stats = $this->get_stats( $args );

		$filter       = array(
			'type' => 'refund',
		);
		$order_status = $this->get_order_status();
		if ( ! empty( $order_status ) ) {
			$filter['status'] = array( $order_status );
		}

		return $stats->get_gateway_earnings( $filter );
	}
}
