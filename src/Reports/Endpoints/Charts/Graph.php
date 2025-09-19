<?php
/**
 * Base class for Simple EDD Reports Charts
 *
 * This abstract class provides common functionality for building single-dataset chart data
 * from database queries, making it easy to create new chart implementations.
 *
 * @package     EDD\Reports\Endpoints\Charts
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Charts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports;
use EDD\Reports\Endpoints\Endpoint;

/**
 * Abstract base class for simple EDD Reports Chart builders.
 *
 * Provides common functionality for single-dataset chart data building patterns,
 * particularly for line graphs that aggregate database results over time periods.
 *
 * @since 3.5.1
 */
abstract class Graph extends Endpoint {
	use \EDD\Reports\Endpoints\Traits\Colors;

	/**
	 * The chart type (usually 'line').
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $chart_type = 'line';

	/**
	 * Date range for the chart display.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $chart_dates;

	/**
	 * The graph period (hour, day, month).
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $period;

	/**
	 * The column to query for amounts.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $column;

	/**
	 * SQL clauses for the query.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $sql_clauses;

	/**
	 * True if using day-by-day period.
	 *
	 * @since 3.5.1
	 * @var bool
	 */
	protected $day_by_day;

	/**
	 * True if using hour-by-hour period.
	 *
	 * @since 3.5.1
	 * @var bool
	 */
	protected $hour_by_hour;

	/**
	 * The chart data array.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $data = array();

	/**
	 * The key for the dataset.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $key = 'earnings';

	/**
	 * Registers this chart with the reports system.
	 *
	 * @since 3.5.1
	 */
	protected function register(): void {
		$this->reports->register_endpoint(
			$this->get_id(),
			array(
				'label' => $this->get_heading(),
				'views' => array(
					'chart' => array(
						'data_callback' => array( $this, 'get_data_for_callback' ),
						'type'          => $this->chart_type,
						'options'       => array(
							'datasets'       => $this->get_datasets(),
							'formattingType' => 'earnings' !== $this->key ? 'integer' : 'format',
						),
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
		return array( $this->key => $this->get_data() );
	}

	/**
	 * Gets the chart data by building it from query results.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_data(): array {
		try {
			$query_results = $this->get_query_results();
			$this->initialize_data();

			// Build the chart data using the common pattern.
			while ( strtotime( $this->chart_dates['start']->copy()->format( 'mysql' ) ) <= strtotime( $this->chart_dates['end']->copy()->format( 'mysql' ) ) ) {
				$timestamp     = $this->chart_dates['start']->copy()->format( 'U' );
				$date_on_chart = $this->chart_dates['start'];

				// Initialize data for this timestamp.
				$this->data[ $timestamp ][0] = $date_on_chart->format( 'Y-m-d H:i:s' );
				$this->data[ $timestamp ][1] = 0;

				// Process each query result for this timestamp.
				foreach ( $query_results as $result ) {
					if ( $this->dates_match( $result, $date_on_chart ) ) {
						$this->process_result( $result, $timestamp );
					}
				}

				// Move to next period.
				$this->increment_chart_date();
			}

			return array_values( $this->data );
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
	 * Gets the query results that will be used to build the chart.
	 * This method should return an array of database result objects.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	abstract protected function get_query_results(): array;

	/**
	 * Gets the chart datasets configuration.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_datasets(): array {
		return array( $this->key => $this->get_args() );
	}

	/**
	 * Sets up common properties used by all chart types.
	 *
	 * @since 3.5.1
	 */
	protected function initialize_properties(): void {
		parent::initialize_properties();
		$this->chart_dates  = Reports\parse_dates_for_range( null, 'now', false );
		$this->period       = Reports\get_graph_period();
		$this->column       = Reports\get_taxes_excluded_filter() ? '(total - tax)' : 'total';
		$this->day_by_day   = Reports\get_dates_filter_day_by_day();
		$this->hour_by_hour = Reports\get_dates_filter_hour_by_hour();
		$this->sql_clauses  = Reports\get_sql_clauses( $this->period, $this->date_column );

		// Handle currency conversion.
		if ( empty( $this->currency ) || 'convert' === $this->currency ) {
			$this->column .= ' / rate';
		}

		// Handle currency-specific SQL clauses.
		if ( ! empty( $this->currency ) && array_key_exists( strtoupper( $this->currency ), edd_get_currencies() ) ) {
			global $wpdb;
			$this->sql_clauses['where'] = $wpdb->prepare( ' AND currency = %s ', strtoupper( $this->currency ) );
		}
	}

	/**
	 * Gets the chart arguments.
	 *
	 * @since 3.5.1
	 * @return array
	 */
	protected function get_args(): array {
		return array(
			'label'                => $this->get_label(),
			'borderColor'          => 'sales' === $this->key ? $this->sales : $this->earnings,
			'backgroundColor'      => $this->background_color,
			'fill'                 => false,
			'borderWidth'          => 2,
			'pointRadius'          => 4,
			'pointHoverRadius'     => 6,
			'pointBackgroundColor' => 'rgb(255,255,255)',
			'borderCapStyle'       => 'round',
			'borderJoinStyle'      => 'round',
			'pointBackgroundColor' => 'rgb(255,255,255)',
			'type'                 => 'earnings' === $this->key ? 'currency' : false,
		);
	}

	/**
	 * Processes a single database result for a given timestamp.
	 *
	 * Default implementation expects the query result to have a 'value' property.
	 * Child classes can override this method if they need custom logic.
	 *
	 * @since 3.5.1
	 * @param object $result    Database result object.
	 * @param int    $timestamp Unix timestamp.
	 */
	protected function process_result( $result, $timestamp ): void {
		$this->data[ $timestamp ][1] += $result->value;
	}

	/**
	 * Checks if a database result date matches the current chart date.
	 *
	 * @since 3.5.1
	 * @param object $result        Database result object.
	 * @param object $date_on_chart Current chart date.
	 * @return bool
	 */
	protected function dates_match( $result, $date_on_chart ): bool {
		$date_of_db_value = EDD()->utils->date( $result->date );

		if ( 'hour' === $this->period ) {
			return $date_of_db_value->format( 'Y-m-d H' ) === $date_on_chart->format( 'Y-m-d H' );
		} elseif ( 'day' === $this->period ) {
			return $date_of_db_value->format( 'Y-m-d' ) === $date_on_chart->format( 'Y-m-d' );
		} else {
			return $date_of_db_value->format( 'Y-m' ) === $date_on_chart->format( 'Y-m' );
		}
	}

	/**
	 * Increments the chart date to the next period.
	 *
	 * @since 3.5.1
	 */
	protected function increment_chart_date(): void {
		if ( 'hour' === $this->period ) {
			$this->chart_dates['start']->addHour( 1 );
		} elseif ( 'day' === $this->period ) {
			$this->chart_dates['start']->addDays( 1 );
		} else {
			$this->chart_dates['start']->modify( 'first day of next month' );
		}
	}

	/**
	 * Initializes the chart data structure.
	 *
	 * @since 3.5.1
	 */
	protected function initialize_data(): void {
		if ( ! $this->day_by_day && ! $this->hour_by_hour ) {
			$this->chart_dates['start']->modify( 'first day of this month' );
		}
	}

	/**
	 * Initializes the datasets structure.
	 *
	 * This method should populate the $this->datasets array with the structure
	 * needed for the chart.
	 *
	 * @since 3.5.1
	 */
	protected function initialize_datasets(): void {
		if ( ! $this->day_by_day && ! $this->hour_by_hour ) {
			$this->chart_dates['start']->modify( 'first day of this month' );
		}
	}
}
