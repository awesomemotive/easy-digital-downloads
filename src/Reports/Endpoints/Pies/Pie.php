<?php
/**
 * Base class for EDD Reports Pie Charts
 *
 * This abstract class provides common functionality for building pie chart data
 * from database queries, making it easy to create new pie chart implementations.
 *
 * @package     EDD\Reports\Endpoints\Pies
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Pies;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports\Endpoints\Endpoint;

/**
 * Abstract base class for EDD Reports Pie Chart builders.
 *
 * Provides common functionality for pie chart data building patterns,
 * particularly for charts that show distribution or breakdown of data.
 *
 * @since 3.5.1
 */
abstract class Pie extends Endpoint {
	use \EDD\Reports\Endpoints\Traits\Colors;

	/**
	 * The chart type (always 'pie').
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $chart_type = 'pie';

	/**
	 * The cutout percentage for the pie chart (0 for full pie, >0 for doughnut).
	 *
	 * @since 3.5.1
	 * @var int
	 */
	protected $cutout_percentage = 0;

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'data';

	/**
	 * Chart data array.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $data = array();

	/**
	 * Chart labels array.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $labels = array();

	/**
	 * The results for the query.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $results = array();

	/**
	 * Registers this chart with the reports system.
	 *
	 * @since 3.5.1
	 */
	protected function register(): void {
		if ( empty( $this->get_data() ) ) {
			return;
		}

		$this->reports->register_endpoint(
			$this->get_id(),
			array(
				'label' => $this->get_heading(),
				'views' => array(
					'chart' => array(
						'data_callback' => array( $this, 'get_data_for_callback' ),
						'type'          => $this->chart_type,
						'options'       => $this->get_chart_options(),
					),
				),
			)
		);
	}

	/**
	 * Gets chart data formatted for the callback system.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	public function get_data_for_callback(): array {
		$data = $this->get_data();

		return array( $this->key => $data );
	}

	/**
	 * Gets the query results that will be used to build the chart.
	 * This method should return an array of database result objects.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	abstract protected function get_query_results(): array;

	/**
	 * Gets the chart data by processing query results.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_data(): array {
		try {
			$this->data = array_values( $this->process_results( $this->get_query_results() ) );

			return $this->data;
		} catch ( \Exception $e ) {
			edd_debug_log( $e->getMessage(), true );

			return array();
		}
	}

	/**
	 * Gets the heading for the chart.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_heading(): string {
		return $this->get_label() . ' &mdash; ' . $this->get_chart_label();
	}

	/**
	 * Gets the chart options for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_chart_options(): array {
		return array(
			'cutoutPercentage' => $this->cutout_percentage,
			'datasets'         => $this->get_datasets(),
			'labels'           => $this->get_labels(),
		);
	}

	/**
	 * Gets the datasets configuration for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_datasets(): array {
		return array(
			$this->key => array(
				'label'           => $this->get_label(),
				'backgroundColor' => $this->get_colors(),
				'type'            => 'earnings' === $this->key ? 'currency' : 'number',
			),
		);
	}

	/**
	 * Gets the labels for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_labels(): array {
		return array_map(
			function ( $result ) {
				return $result->piece;
			},
			$this->get_query_results()
		);
	}

	/**
	 * Gets the pieces for the pie chart.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_pieces(): array {
		return array_map(
			function ( $result ) {
				return $result->piece;
			},
			$this->get_query_results()
		);
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 * Child classes can override this method for custom processing logic,
	 * but won't have to if they return the results as an array of objects with
	 * a 'piece' and 'value' property.
	 *
	 * @since 3.5.1
	 * @param array $query_results Database query results.
	 * @return array
	 */
	protected function process_results( array $query_results ): array {
		$data = array();

		foreach ( $query_results as $result ) {
			$data[ $result->piece ] = 0;
		}

		foreach ( $query_results as $result ) {
			if ( isset( $data[ $result->piece ] ) ) {
				$data[ $result->piece ] = (int) $result->value;
			}
		}

		return $data;
	}
}
