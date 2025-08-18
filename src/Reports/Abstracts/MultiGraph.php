<?php
/**
 * Base class for Multi-Dataset EDD Reports Charts
 *
 * This abstract class extends Graph to provide multi-dataset chart functionality
 * while inheriting all the common date handling and property logic.
 *
 * @package     EDD\Reports\Abstracts
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Abstracts;

use EDD\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract base class for multi-dataset EDD Reports Chart builders.
 *
 * Extends the Graph class to support multiple datasets while reusing
 * all the common date handling and query logic.
 *
 * @since 3.5.1
 */
abstract class MultiGraph extends Graph {

	/**
	 * The datasets array for multiple data series.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $datasets = array();

	/**
	 * Constructor.
	 *
	 * @since 3.5.1
	 */
	public function __construct() {
		parent::__construct();
		$this->setup_datasets();
	}

	/**
	 * Gets chart data formatted for the callback system.
	 *
	 * Overrides the single-dataset version to return multiple keyed datasets.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	public function get_data_for_callback(): array {
		return $this->get_data();
	}

	/**
	 * Gets the chart data by building it from query results.
	 *
	 * Overrides the single-dataset version to work with multiple datasets.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_data(): array {
		$query_results = $this->get_query_results();
		$this->initialize_datasets();

		// Build the chart data using the common pattern (inherited logic).
		while ( strtotime( $this->chart_dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $this->chart_dates['end']->copy()->format( 'mysql' ) ) ) {
			$timestamp     = $this->chart_dates['start']->copy()->format( 'U' );
			$date_on_chart = $this->chart_dates['start'];

			// Initialize data for this timestamp across all datasets.
			$this->initialize_timestamp_data( $timestamp, $date_on_chart );

			// Process each query result for this timestamp.
			foreach ( $query_results as $result ) {
				if ( $this->dates_match( $result, $date_on_chart ) ) {
					$this->process_result( $result, $timestamp, $date_on_chart );
				}
			}

			// Move to next period (inherited method).
			$this->increment_chart_date();
		}

		// Convert to the format expected by the chart system.
		$values = array();
		foreach ( $this->datasets as $key => $dataset ) {
			$values[ $key ] = array_values( $dataset );
		}

		return $values;
	}

	/**
	 * Sets up the datasets structure.
	 *
	 * Child classes should implement this to define their datasets.
	 *
	 * @since 3.5.1
	 */
	abstract protected function setup_datasets(): void;

	/**
	 * Processes a single database result for a given timestamp.
	 *
	 * Child classes must implement this to handle their specific data mapping.
	 *
	 * @since 3.5.1
	 * @param object $result        Database result object.
	 * @param int    $timestamp     Unix timestamp.
	 * @param object $date_on_chart Chart date object.
	 */
	abstract protected function process_result( $result, $timestamp, $date_on_chart ): void;

	/**
	 * Initializes data for a specific timestamp across all datasets.
	 *
	 * @since 3.5.1
	 * @param int    $timestamp     Unix timestamp.
	 * @param object $date_on_chart Chart date object.
	 */
	protected function initialize_timestamp_data( $timestamp, $date_on_chart ): void {
		foreach ( $this->datasets as $key => $dataset ) {
			$this->datasets[ $key ][ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );

			// Use dataset-specific default value if specified, otherwise use 0.
			$default_value                           = isset( $dataset['default_value'] ) ? $dataset['default_value'] : 0;
			$this->datasets[ $key ][ $timestamp ][1] = $default_value;
		}
	}
}
