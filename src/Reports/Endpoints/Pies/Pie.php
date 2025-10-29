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
	 * Maximum number of pieces to display before grouping smaller ones into "Other".
	 *
	 * @since 3.5.3
	 * @var int
	 */
	protected $max_pieces = 9;

	/**
	 * Minimum percentage a piece must represent to avoid being grouped into "Other".
	 * Pieces representing less than this percentage of the total will be grouped.
	 *
	 * @since 3.5.3
	 * @var float
	 */
	protected $min_percentage = 5.0;

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
	 * The processed results for the query.
	 *
	 * @since 3.5.3
	 * @var array
	 */
	private $processed_results = array();

	/**
	 * The original pieces that were grouped into "Other".
	 *
	 * @since 3.5.3
	 * @var array
	 */
	private $other_pieces_breakdown = array();

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
			$this->data = array_values( $this->get_processed_results() );

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
			'otherLabel'       => __( 'Other', 'easy-digital-downloads' ),
			'otherBreakdown'   => $this->get_other_pieces_breakdown(),
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
	 * Labels are derived from the processed data to ensure they match
	 * the actual chart data, including any "Other" groupings.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_labels(): array {
		return array_keys( $this->get_processed_results() );
	}

	/**
	 * Gets the pieces for the pie chart.
	 * Pieces are derived from the processed data to ensure they match
	 * the actual chart data, including any "Other" groupings.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_pieces(): array {
		return array_keys( $this->get_processed_results() );
	}

	/**
	 * Processes the query results to populate the data and labels arrays.
	 * Child classes can override this method for custom processing logic,
	 * but won't have to if they return the results as an array of objects with
	 * a 'piece' and 'value' property.
	 *
	 * Pieces will be grouped into "Other" if:
	 * - There are more pieces than $max_pieces, OR
	 * - A piece represents less than $min_percentage of the total
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

		// Group small pieces based on percentage threshold.
		$data = $this->group_small_percentage_pieces( $data );

		// If we still have more pieces than the maximum, group the smallest ones into "Other".
		if ( count( $data ) > $this->get_max_pieces() ) {
			$data = $this->group_small_pieces( $data );
		}

		return $data;
	}

	/**
	 * Groups pieces that represent less than the minimum percentage into "Other".
	 *
	 * @since 3.5.3
	 * @param array $data The original data array.
	 * @return array The modified data array with small percentage pieces grouped.
	 */
	protected function group_small_percentage_pieces( array $data ): array {
		if ( empty( $data ) || $this->min_percentage <= 0 ) {
			return $data;
		}

		$total = array_sum( $data );
		if ( $total <= 0 ) {
			return $data;
		}

		$threshold    = ( $this->min_percentage / 100 ) * $total;
		$keep_pieces  = array();
		$small_pieces = array();

		foreach ( $data as $piece => $value ) {
			if ( $value >= $threshold ) {
				$keep_pieces[ $piece ] = $value;
			} else {
				$small_pieces[ $piece ] = $value;
			}
		}

		// If there are small pieces to group, add them to "Other".
		if ( ! empty( $small_pieces ) ) {
			$other_total = array_sum( $small_pieces );
			if ( $other_total > 0 ) {
				$keep_pieces[ __( 'Other', 'easy-digital-downloads' ) ] = $other_total;
				// Store the breakdown for tooltip display.
				$this->other_pieces_breakdown = $this->other_pieces_breakdown + $small_pieces;
			}
		}

		return $keep_pieces;
	}

	/**
	 * Groups the smallest pieces into an "Other" category when there are too many pieces.
	 *
	 * @since 3.5.3
	 * @param array $data The original data array.
	 * @return array The modified data array with small pieces grouped.
	 */
	protected function group_small_pieces( array $data ): array {
		$other_label = __( 'Other', 'easy-digital-downloads' );
		// Extract existing "Other" value if it exists.
		$existing_other = 0;
		if ( isset( $data[ $other_label ] ) ) {
			$existing_other = $data[ $other_label ];
			unset( $data[ $other_label ] );
		}

		// Sort by value (descending) to identify the largest pieces.
		arsort( $data );

		// Keep the top (max_pieces - 1) pieces to leave room for "Other".
		$keep_count   = $this->get_max_pieces() - 1;
		$top_pieces   = array_slice( $data, 0, $keep_count, true );
		$small_pieces = array_slice( $data, $keep_count, null, true );

		// Sum up the small pieces and add any existing "Other" value.
		$other_total = array_sum( $small_pieces ) + $existing_other;

		// Add the "Other" category if there are pieces to group.
		if ( $other_total > 0 ) {
			$top_pieces[ $other_label ] = $other_total;
			// Store the breakdown for tooltip display.
			$this->other_pieces_breakdown = $this->other_pieces_breakdown + $small_pieces;
		}

		return $top_pieces;
	}

	/**
	 * Gets the processed results.
	 *
	 * @since 3.5.3
	 * @return array
	 */
	protected function get_processed_results(): array {
		if ( empty( $this->processed_results ) ) {
			$this->processed_results = $this->process_results( $this->get_query_results() );
		}

		return $this->processed_results;
	}

	/**
	 * Gets the breakdown of pieces that were grouped into "Other".
	 *
	 * @since 3.5.3
	 * @return array
	 */
	protected function get_other_pieces_breakdown(): array {
		$breakdown = $this->other_pieces_breakdown;

		// Allow child classes to format the breakdown labels.
		$formatted_breakdown = array();
		foreach ( $breakdown as $piece => $value ) {
			$formatted_breakdown[ $this->format_breakdown_label( $piece ) ] = $value;
		}

		return $formatted_breakdown;
	}

	/**
	 * Formats a breakdown label for display in tooltips.
	 * Child classes can override this method to provide custom formatting.
	 *
	 * @since 3.5.3
	 * @param string $piece The original piece identifier.
	 * @return string The formatted label.
	 */
	protected function format_breakdown_label( string $piece ): string {
		return $piece;
	}

	/**
	 * Gets the maximum number of pieces to display before grouping smaller ones into "Other".
	 *
	 * @since 3.5.3
	 * @return int
	 */
	private function get_max_pieces(): int {
		/**
		 * Filters the maximum number of pieces to display before grouping smaller ones into "Other".
		 *
		 * @since 3.5.3
		 * @param int    $max_pieces The maximum number of pieces.
		 * @param string $id         The ID of the pie chart.
		 */
		return (int) apply_filters( 'edd_pie_chart_max_pieces', $this->max_pieces, $this->get_id() );
	}
}
