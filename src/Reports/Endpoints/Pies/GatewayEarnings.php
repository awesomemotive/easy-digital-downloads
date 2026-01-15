<?php
/**
 * Gateway Earnings Breakdown Pie Chart
 *
 * @package     EDD\Reports\Endpoints\Pies
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Pies;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Gateway Earnings Breakdown Pie Chart class.
 *
 * Builds pie chart data for gateway earnings breakdown using the Pie abstract pattern.
 *
 * @since 3.5.1
 */
class GatewayEarnings extends Pie {
	use Traits\Gateway;

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'earnings';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'gateway_earnings_breakdown';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Gateway Earnings', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		$stats = new \EDD\Stats();
		$args  = array(
			'range'         => $this->dates['range'],
			'grouped'       => true,
			'exclude_taxes' => $this->exclude_taxes,
			'currency'      => $this->currency,
		);

		return $stats->get_gateway_earnings( $args );
	}
}
