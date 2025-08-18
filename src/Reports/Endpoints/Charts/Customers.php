<?php
/**
 * Customers Chart
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
 * Earnings Chart class.
 *
 * Builds chart data for the earnings chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class Customers extends Graph {

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'customers';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'new_customers';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'New Customers', 'easy-digital-downloads' );
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
				"SELECT COUNT(id) AS value, {$this->sql_clauses['select']}
				 FROM {$wpdb->edd_customers}
				 WHERE purchase_count > 0
				 AND {$this->get_date_sql()}
				 GROUP BY {$this->sql_clauses['groupby']}
				 ORDER BY {$this->sql_clauses['orderby']} ASC"
			)
		);
	}
}
