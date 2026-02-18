<?php
/**
 * Base class for EDD Reports Bar Charts
 *
 * This abstract class provides common functionality for building bar chart data
 * from database queries, making it easy to create new bar chart implementations.
 *
 * @package     EDD\Reports\Endpoints\Charts
 * @copyright   Copyright (c) 2026, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Reports\Endpoints\Charts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Reports;
use EDD\Reports\Endpoints\Endpoint;

/**
 * Abstract base class for EDD Reports Bar Chart builders.
 *
 * Provides common functionality for bar chart data building patterns,
 * particularly for charts that show multiple datasets over time periods.
 *
 * @since 3.6.5
 */
abstract class Bar extends Endpoint {
	use \EDD\Reports\Endpoints\Traits\Colors;

	/**
	 * The chart type (always 'bar').
	 *
	 * @since 3.6.5
	 * @var string
	 */
	protected $chart_type = 'bar';

	/**
	 * Whether the bar chart should be stacked.
	 *
	 * @since 3.6.5
	 * @var bool
	 */
	protected $stacked = false;

	/**
	 * Date range for the chart display.
	 *
	 * @since 3.6.5
	 * @var array
	 */
	protected $chart_dates;

	/**
	 * The graph period (hour, day, month).
	 *
	 * @since 3.6.5
	 * @var string
	 */
	protected $period;

	/**
	 * The column to query for amounts.
	 *
	 * @since 3.6.5
	 * @var string
	 */
	protected $column;

	/**
	 * SQL clauses for the query.
	 *
	 * @since 3.6.5
	 * @var array
	 */
	protected $sql_clauses;

	/**
	 * True if using day-by-day period.
	 *
	 * @since 3.6.5
	 * @var bool
	 */
	protected $day_by_day;

	/**
	 * True if using hour-by-hour period.
	 *
	 * @since 3.6.5
	 * @var bool
	 */
	protected $hour_by_hour;

	/**
	 * The chart data array.
	 *
	 * @since 3.6.5
	 * @var array
	 */
	protected $data = array();

	/**
	 * Registers this chart with the reports system.
	 *
	 * @since 3.6.5
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
						'stacked'       => $this->stacked,
						'options'       => $this->get_chart_options(),
					),
				),
			)
		);
	}

	/**
	 * Gets chart data formatted for the callback system.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	public function get_data_for_callback(): array {
		return $this->get_data();
	}

	/**
	 * Gets the chart data by building it from query results.
	 *
	 * @since 3.6.5
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

				// Initialize data for this timestamp for all datasets.
				$this->initialize_timestamp_data( $timestamp, $date_on_chart );

				// Process each query result for this timestamp.
				$this->process_query_results( $query_results, $timestamp, $date_on_chart );

				// Move to next period.
				$this->increment_chart_date();
			}

			return $this->format_data();
		} catch ( \Exception $e ) {
			edd_debug_log( $e->getMessage(), true );
			return array();
		}
	}

	/**
	 * Gets the heading for the chart.
	 *
	 * @since 3.6.5
	 * @return string
	 */
	protected function get_heading(): string {
		return $this->get_label() . ' &mdash; ' . $this->get_chart_label();
	}

	/**
	 * Gets the query results that will be used to build the chart.
	 * This method should return an array or associative array of database result objects.
	 *
	 * For single dataset bar charts, return a simple array.
	 * For multiple dataset bar charts, return an associative array with keys for each dataset.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	abstract protected function get_query_results(): array;

	/**
	 * Gets the chart datasets configuration.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	abstract protected function get_datasets(): array;

	/**
	 * Initializes the data for a specific timestamp.
	 * Child classes should override this to set up their specific dataset structure.
	 *
	 * @since 3.6.5
	 * @param int    $timestamp     Unix timestamp.
	 * @param object $date_on_chart Current chart date.
	 */
	abstract protected function initialize_timestamp_data( $timestamp, $date_on_chart ): void;

	/**
	 * Processes query results for a specific timestamp.
	 * Child classes should override this to handle their specific data processing logic.
	 *
	 * @since 3.6.5
	 * @param array  $query_results All query results.
	 * @param int    $timestamp     Unix timestamp.
	 * @param object $date_on_chart Current chart date.
	 */
	abstract protected function process_query_results( array $query_results, $timestamp, $date_on_chart ): void;

	/**
	 * Formats the data for output.
	 * Child classes should override this to format their data structure for the chart.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	abstract protected function format_data(): array;

	/**
	 * Gets the chart options for the bar chart.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	protected function get_chart_options(): array {
		return array(
			'datasets' => $this->get_datasets(),
			'scales'   => $this->get_scales(),
		);
	}

	/**
	 * Gets the scales configuration for the bar chart.
	 * Child classes can override this to customize the scales.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	protected function get_scales(): array {
		return array(
			'yAxes' => array(
				array(
					'type'      => 'linear',
					'display'   => true,
					'stacked'   => $this->stacked,
					'ticks'     => array(
						'formattingType' => 'format',
						'beginAtZero'    => true,
						'precision'      => 0,
					),
					'gridLines' => array(
						'display' => true,
					),
				),
			),
			'xAxes' => array(
				array(
					'type'     => 'time',
					'display'  => true,
					'stacked'  => $this->stacked,
					'offset'   => true,
					'ticks'    => array(
						'source'      => 'auto',
						'maxRotation' => 0,
					),
					'position' => 'bottom',
					'time'     => $this->get_time_for_axes(),
				),
			),
		);
	}

	/**
	 * Gets the time period to use for the bar chart axes.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	protected function get_time_for_axes(): array {
		$time_unit   = 'month';
		$time_format = 'MMM YYYY';

		if ( 'day' === $this->period ) {
			$time_unit   = 'day';
			$time_format = 'MMM D';
		} elseif ( 'hour' === $this->period ) {
			$time_unit   = 'hour';
			$time_format = 'hA';
		}

		return array(
			'unit'          => $time_unit,
			'tooltipFormat' => $time_format,
		);
	}

	/**
	 * Sets up common properties used by all chart types.
	 *
	 * @since 3.6.5
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
	 * Checks if a database result date matches the current chart date.
	 *
	 * @since 3.6.5
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
	 * @since 3.6.5
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
	 * @since 3.6.5
	 */
	protected function initialize_data(): void {
		if ( ! $this->day_by_day && ! $this->hour_by_hour ) {
			$this->chart_dates['start']->modify( 'first day of this month' );
		}
	}
}
