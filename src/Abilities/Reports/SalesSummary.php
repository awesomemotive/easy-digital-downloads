<?php
/**
 * Ability to get the store sales summary.
 *
 * @package     EDD\Abilities\Reports
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Abilities\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Abilities\ReadAbility;
use EDD\Reports\SalesSummaryRanges;

/**
 * Gets net earnings and sales counts for this month, last month, today, and all time.
 *
 * @since 3.7.1
 */
class SalesSummary extends ReadAbility {

	/**
	 * Reads the store data the ability describes.
	 *
	 * @since 3.7.1
	 *
	 * @param array $input Unused; the ability accepts no input.
	 * @return array|\WP_Error
	 */
	protected function read_data( array $input ) {
		$data = array();

		foreach ( SalesSummaryRanges::get_ranges() as $range ) {
			$stats = new \EDD\Stats( SalesSummaryRanges::get_stats_args( $range, 'raw' ) );

			$data[ $range ] = array(
				'earnings' => (float) $stats->get_order_earnings(),
				'sales'    => (int) $stats->get_order_count(),
			);
		}

		$data['currency'] = edd_get_currency();

		return $data;
	}

	/**
	 * Gets the ability slug.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_slug(): string {
		return 'sales-summary';
	}

	/**
	 * Gets the ability label.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Sales Summary', 'easy-digital-downloads' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_description(): string {
		return __( 'Get net earnings and sales counts for this month, last month, today, and all time.', 'easy-digital-downloads' );
	}

	/**
	 * Gets the capability required to run the ability.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_capability(): string {
		return 'view_shop_reports';
	}

	/**
	 * Gets the output schema.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	protected function get_output_schema(): array {
		$ranges     = SalesSummaryRanges::get_ranges();
		$properties = array_fill_keys( $ranges, self::get_range_schema() );

		$properties['currency'] = array( 'type' => 'string' );

		return array(
			'type'       => 'object',
			'properties' => $properties,
			'required'   => array_merge( $ranges, array( 'currency' ) ),
		);
	}

	/**
	 * Gets the JSON Schema describing one summary range.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	private static function get_range_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'earnings' => array(
					'type'        => 'number',
					'description' => __( 'Net earnings for the range.', 'easy-digital-downloads' ),
				),
				'sales'    => array(
					'type'        => 'integer',
					'description' => __( 'Number of sales for the range.', 'easy-digital-downloads' ),
				),
			),
			'required'   => array( 'earnings', 'sales' ),
		);
	}
}
