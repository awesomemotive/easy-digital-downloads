<?php
/**
 * Discounts Chart
 *
 * @package     EDD\Reports\Endpoints\Charts
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Charts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Discounts Chart class.
 *
 * Builds chart data for the file downloads chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class Discounts extends Graph {

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'usage';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'discount_usage_chart';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Discount Usage', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) AS value, {$this->sql_clauses['select']}
				 FROM {$wpdb->edd_order_adjustments}
				 WHERE 1=1
				 AND type = 'discount'
				 {$this->get_discount_sql()}
				 AND {$this->get_date_sql()}
				 {$this->get_group_by()}"
			)
		);
	}

	/**
	 * Gets the SQL for the discount filter.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	private function get_discount_sql(): string {
		$discount = \EDD\Reports\get_filter_value( 'discounts' );
		if ( empty( $discount ) || 'all' === $discount ) {
			return '';
		}

		$discount_object = edd_get_discount( $discount );
		if ( ! $discount_object ) {
			return '';
		}

		return "AND description = '{$discount_object->code}'";
	}
}
