<?php
/**
 * Trait for shared functionality between Gateway Earnings and Sales reports.
 *
 * @package EDD\Reports\Endpoints\Pies\Traits
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.4
 */

namespace EDD\Reports\Endpoints\Pies\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trait Gateway
 *
 * @since 3.6.4
 */
trait Gateway {

	/**
	 * Formats a breakdown label for display in tooltips.
	 *
	 * @since 3.6.4
	 * @param string $piece The original piece identifier (gateway slug).
	 * @return string The formatted label.
	 */
	protected function format_breakdown_label( string $piece ): string {
		return edd_get_gateway_admin_label( $piece );
	}

	/**
	 * Gets the labels for the pie chart.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	protected function get_labels(): array {
		return array_map( 'edd_get_gateway_admin_label', $this->get_pieces() );
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 *
	 * Only includes gateways that have actual sales or earnings data.
	 *
	 * @since 3.6.4
	 * @param array $query_results Database query results.
	 * @return array
	 */
	protected function process_results( array $query_results ): array {
		$gateways   = array();
		$result_key = 'earnings' === $this->key ? $this->key : 'total';

		// Only include gateways that have actual data.
		foreach ( $query_results as $result ) {
			// Only include results with values greater than 0.
			if ( isset( $result->$result_key ) && $result->$result_key > 0 ) {
				$gateways[ $result->gateway ] = 'earnings' === $this->key
					? floatval( $result->$result_key )
					: (int) $result->$result_key;
			}
		}

		// Group small pieces based on percentage threshold.
		$gateways = $this->group_small_percentage_pieces( $gateways );

		// If we still have more pieces than the maximum, group the smallest ones into "Other".
		if ( count( $gateways ) > $this->get_max_pieces() ) {
			$gateways = $this->group_small_pieces( $gateways );
		}

		return $gateways;
	}
}
