<?php
/**
 * Ability to get EDD sales and earnings statistics.
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
use EDD\Abilities\Traits\FormatsDates;

/**
 * Gets sales and earnings statistics for a period or date range.
 *
 * @since 3.7.1
 */
class Sales extends ReadAbility {

	use FormatsDates;

	/**
	 * Reads the store data the ability describes.
	 *
	 * @since 3.7.1
	 *
	 * @param array $input The validated input.
	 * @return array|\WP_Error
	 */
	protected function read_data( array $input ) {
		$period     = null;
		$date_start = null;
		$date_end   = null;
		$base_query = array(
			'output' => 'raw',
		);

		if ( ! empty( $input['period'] ) && in_array( $input['period'], self::get_valid_periods(), true ) ) {
			$period              = sanitize_key( $input['period'] );
			$base_query['range'] = $period;
		} elseif ( ! empty( $input['date_start'] ) || ! empty( $input['date_end'] ) ) {
			if ( ! empty( $input['date_start'] ) ) {
				$date_start = $this->validate_date_input( 'date_start', $input['date_start'] );
				if ( is_wp_error( $date_start ) ) {
					return $date_start;
				}

				$base_query['start'] = $date_start . ' 00:00:00';
			}
			if ( ! empty( $input['date_end'] ) ) {
				$date_end = $this->validate_date_input( 'date_end', $input['date_end'] );
				if ( is_wp_error( $date_end ) ) {
					return $date_end;
				}

				$base_query['end'] = $date_end . ' 23:59:59';
			}
		} else {
			$period              = 'this_month';
			$base_query['range'] = $period;
		}

		$stats = new \EDD\Stats( $base_query );

		$earnings_net = (float) $stats->get_order_earnings( array( 'revenue_type' => 'net' ) );
		$order_count  = (int) $stats->get_order_count();

		$result = array(
			'period'              => $period,
			'date_start'          => $date_start,
			'date_end'            => $date_end,
			'currency'            => edd_get_currency(),
			'earnings_gross'      => (float) $stats->get_order_earnings( array( 'revenue_type' => 'gross' ) ),
			'earnings_net'        => $earnings_net,
			'order_count'         => $order_count,
			'refund_amount'       => abs( (float) $stats->get_order_refund_amount() ),
			'refund_count'        => (int) $stats->get_order_refund_count(),
			'average_order_value' => $order_count > 0 ? round( $earnings_net / $order_count, edd_currency_decimal_filter() ) : 0.0,
		);

		if ( ! empty( $input['product_id'] ) ) {
			$product_id = absint( $input['product_id'] );

			$result['product_id']       = $product_id;
			$result['product_earnings'] = (float) $stats->get_order_item_earnings( array( 'product_id' => $product_id ) );
			$result['product_sales']    = (int) $stats->get_order_item_count( array( 'product_id' => $product_id ) );
		}

		return $result;
	}

	/**
	 * Gets the ability slug.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_slug(): string {
		return 'sales-report';
	}

	/**
	 * Gets the ability label.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Sales Report', 'easy-digital-downloads' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	protected function get_description(): string {
		return __( 'Get store sales and earnings statistics for a preset period or a custom date range, optionally scoped to a single product.', 'easy-digital-downloads' );
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
	 * Gets the input schema.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	protected function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'period'     => array(
					'type'        => 'string',
					'enum'        => self::get_valid_periods(),
					'description' => __( 'Preset reporting period. Takes precedence over date_start and date_end. Defaults to this_month when no dates are provided.', 'easy-digital-downloads' ),
				),
				'date_start' => array(
					'type'        => 'string',
					'format'      => 'date',
					'pattern'     => '^\d{4}-\d{2}-\d{2}$',
					'description' => __( 'Include orders created on or after this date (YYYY-MM-DD). Ignored when period is provided.', 'easy-digital-downloads' ),
				),
				'date_end'   => array(
					'type'        => 'string',
					'format'      => 'date',
					'pattern'     => '^\d{4}-\d{2}-\d{2}$',
					'description' => __( 'Include orders created on or before this date (YYYY-MM-DD). Ignored when period is provided.', 'easy-digital-downloads' ),
				),
				'product_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Also include earnings and sales for this product (download) ID.', 'easy-digital-downloads' ),
				),
			),
			'additionalProperties' => false,
			'default'              => array(),
		);
	}

	/**
	 * Gets the output schema.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	protected function get_output_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'period'              => array(
					'type'        => array( 'string', 'null' ),
					'description' => __( 'The preset period used, or null when a custom date range was used.', 'easy-digital-downloads' ),
				),
				'date_start'          => array( 'type' => array( 'string', 'null' ) ),
				'date_end'            => array( 'type' => array( 'string', 'null' ) ),
				'currency'            => array( 'type' => 'string' ),
				'earnings_gross'      => array( 'type' => 'number' ),
				'earnings_net'        => array( 'type' => 'number' ),
				'order_count'         => array( 'type' => 'integer' ),
				'refund_amount'       => array( 'type' => 'number' ),
				'refund_count'        => array( 'type' => 'integer' ),
				'average_order_value' => array(
					'type'        => 'number',
					'description' => __( 'Net earnings divided by order count, rounded to the store currency precision.', 'easy-digital-downloads' ),
				),
				'product_id'          => array(
					'type'        => 'integer',
					'description' => __( 'Included when a product_id was provided.', 'easy-digital-downloads' ),
				),
				'product_earnings'    => array( 'type' => 'number' ),
				'product_sales'       => array( 'type' => 'integer' ),
			),
			'required'   => array( 'currency', 'earnings_gross', 'earnings_net', 'order_count', 'refund_amount', 'refund_count', 'average_order_value' ),
		);
	}

	/**
	 * Gets the valid preset reporting periods.
	 *
	 * These mirror the range keys from \EDD\Reports\Dates\Range::options(),
	 * excluding the "other" (custom) option, which is covered by the explicit
	 * date_start and date_end inputs. Kept as a literal rather than read from
	 * there: that list is filterable, and a key added through the filter has
	 * no preset in \EDD\Reports\parse_dates_for_range(), so it would be
	 * published in the enum without dates that can be computed for it.
	 *
	 * @since 3.7.1
	 *
	 * @return array
	 */
	private static function get_valid_periods(): array {
		return array(
			'today',
			'yesterday',
			'this_week',
			'last_week',
			'last_30_days',
			'this_month',
			'last_month',
			'this_quarter',
			'last_quarter',
			'this_year',
			'last_year',
		);
	}
}
