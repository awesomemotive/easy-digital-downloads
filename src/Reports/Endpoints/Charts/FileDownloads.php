<?php
/**
 * File Downloads Chart
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
 * File Downloads Chart class.
 *
 * Builds chart data for the file downloads chart using the simplified single-dataset pattern.
 *
 * @since 3.5.1
 */
class FileDownloads extends Graph {

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'downloads';

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'file_downloads_chart';
	}

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'File Downloads', 'easy-digital-downloads' );
	}

	/**
	 * Gets the chart heading for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		return __( 'Number of File Downloads', 'easy-digital-downloads' );
	}

	/**
	 * Gets the query results for building the chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_query_results(): array {
		global $wpdb;

		$download_data = $this->get_download_data();
		$product_id    = '';
		$price_id      = '';
		$date_sql      = $this->get_date_sql();

		if ( is_array( $download_data ) ) {
			$product_id = $wpdb->prepare( 'product_id = %d', absint( $download_data['download_id'] ) );

			if ( isset( $download_data['price_id'] ) && is_numeric( $download_data['price_id'] ) ) {
				$price_id = $wpdb->prepare( 'AND price_id = %d', absint( $download_data['price_id'] ) );
			}

			$date_sql = 'AND ' . $date_sql;
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) AS value, {$this->sql_clauses['select']}
				 FROM {$wpdb->edd_logs_file_downloads} edd_lfd
				 WHERE {$product_id} {$price_id} {$date_sql}
				 GROUP BY {$this->sql_clauses['groupby']}
				 ORDER BY {$this->sql_clauses['orderby']} ASC"
			)
		);
	}
}
