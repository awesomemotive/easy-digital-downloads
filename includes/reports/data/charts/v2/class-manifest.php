<?php
/**
 * Reports API - Chart Manifest class
 *
 * @package     EDD
 * @subpackage  Reports\Data\Charts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */namespace EDD\Reports\Data\Charts\v2;

use EDD\Reports;
use EDD\Reports\Data\Chart_Endpoint;
use EDD\Utils\Error_Logger_Interface as Error_Logger;

/**
 * Represents a manifestation of a ChartJS v2 object's attributes in PHP form.
 *
 * Primarily used to simplify translating server-side arguments into client-side ones.
 *
 * @since 3.0
 */
class Manifest implements Error_Logger {

	/**
	 * Represents the chart type to be manifested.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $type;

	/**
	 * Represents the unfiltered chart options for the manifest.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $options = array();

	/**
	 * Datasets associated with the current chart.
	 *
	 * @since 3.0
	 * @var   Dataset[]
	 */
	private $datasets = array();

	/**
	 * Labels associated with the current pie or doughnut chart.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $labels = array();

	/**
	 * Represents the current Chart_Endpoint instance.
	 *
	 * @since 3.0
	 * @var   Chart_Endpoint
	 */
	private $endpoint;

	/**
	 * Holds errors related to instantiating the manifest.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	protected $errors;

	/**
	 * Sets up the manifest.
	 *
	 * @since 3.0
	 *
	 * @param Chart_Endpoint $endpoint Chart endpoint.
	 */
	public function __construct( $endpoint ) {
		$this->setup_error_logger();
		$this->set_type( $endpoint->get_type() );
		$this->set_endpoint( $endpoint );

		$options = $endpoint->get_options();

		if ( $this->is_pie_manifest() && ! empty( $options['labels'] ) ) {
			$this->set_labels( $options['labels'] );

			unset( $options['labels'] );
		}

		$this->set_options( $options );
	}

	/**
	 * Retrieves the chart type.
	 *
	 * @since 3.0
	 *
	 * @return string Chart type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Sets the chart type for the manifest.
	 *
	 * @since 3.0
	 *
	 * @param string $type Chart type to be manifested.
	 */
	private function set_type( $type ) {
		$this->type = sanitize_key( $type );
	}

	/**
	 * Retrieves the chart endpoint object for this manifest.
	 *
	 * @since 3.0
	 *
	 * @return Chart_Endpoint Chart endpoint.
	 */
	public function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * Sets the chart endpoint object.
	 *
	 * @since 3.0
	 *
	 * @param EDD\Reports\Data\Chart_Endpoint $endpoint Chart_Endpoint object.
	 */
	private function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Stores the unfiltered chart options for later access.
	 *
	 * @since 3.0
	 *
	 * @param array $options Chart options and datasets.
	 */
	private function set_options( $options ) {
		if ( ! empty( $options['datasets'] ) && is_array( $options['datasets'] ) ) {

			foreach ( $options['datasets'] as $id => $data ) {
				$this->add_dataset( $id, $data );
			}

		} else {

			$message = sprintf( 'The %s endpoint has no datasets.', $this->get_endpoint()->get_id() );

			$this->errors->add( 'missing_chart_datasets', $message, $this->get_endpoint() );

		}

		unset( $options['datasets'] );

		$this->options = $options;
	}

	/**
	 * Retrieves parsed options for the chart manifest.
	 *
	 * @since 3.0
	 *
	 * @return array Chart options.
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Retrieves the manifest datasets.
	 *
	 * @since 3.0
	 *
	 * @return Dataset[] Datasets for this chart if any are defined, otherwise an empty array.
	 */
	public function get_datasets() {
		return $this->datasets;
	}

	/**
	 * Determines whether the current chart manifest contains any datasets.
	 *
	 * @since 3.0
	 *
	 * @return bool True if there are datasets, otherwise false.
	 */
	public function has_datasets() {
		$datasets = $this->get_datasets();

		return ! empty( $datasets );
	}

	/**
	 * Sets the labels property (for pie and doughnut charts).
	 *
	 * @since 3.0
	 *
	 * @param array $labels Array of pie or doughnut chart labels.
	 */
	private function set_labels( $labels ) {
		$this->labels = $labels;
	}

	/**
	 * Retrieves the manifest labels (for pie and doughnut charts).
	 *
	 * @since 3.0
	 */
	public function get_labels() {
		return $this->labels;
	}

	/**
	 * Determines whether the current chart manifest contains any labels (for pie and doughnut charts).
	 *
	 * @since 3.0
	 *
	 * @return bool True if there are labels, otherwise false.
	 */
	public function has_labels() {
		$labels = $this->get_labels();

		return ! empty( $labels );
	}

	/**
	 * Adds a dataset.
	 *
	 * @since 3.0
	 *
	 * @param string $dataset_id ID to associate the dataset with.
	 * @param array  $options    Dataset options.
	 * @return bool True if the dataset was added, otherwise false.
	 */
	public function add_dataset( $dataset_id, $options ) {
		$handler = $this->get_dataset_handler();

		if ( ! empty( $handler ) && class_exists( $handler ) ) {
			/** @var Dataset $dataset */
			$dataset = new $handler( $dataset_id, $this->get_endpoint(), $options );

			if ( ! $dataset->has_errors() ) {

				$this->datasets[ $dataset_id ] = $dataset;


				return true;

			} else {

				$this->errors->add( 'dataset_errors_passthrough', 'Errors have been passed through from dataset parsing.', $dataset->get_errors() );

			}

		}

		return false;
	}

	/**
	 * Retrieves the handler class for the current dataset type.
	 *
	 * @since 3.0
	 *
	 * @return string Dataset handler class.
	 */
	public function get_dataset_handler() {
		$handler = '';

		switch( $this->get_type() ) {

			case 'doughnut':
			case 'pie':
				$handler = 'EDD\Reports\Data\Charts\v2\Pie_Dataset';
				break;

			case 'bar':
				$handler = 'EDD\Reports\Data\Charts\v2\Bar_Dataset';
				break;

			case 'line':
				$handler = 'EDD\Reports\Data\Charts\v2\Line_Dataset';
				break;


		}

		return $handler;
	}

	/**
	 * Generate the name of an element used to reference a rendered chart.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_target_el() {
		$endpoint = $this->get_endpoint();
		$default  = "edd_reports_graph_{$endpoint->get_id()}";

		return $endpoint->get_display_arg( 'target', $default );
	}

	/**
	 * Renders the manifest in JS form.
	 *
	 * @since 3.0
	 */
	public function render() {
		// Render a <canvas> element to inject the chart in to.
		printf(
			'<div class="edd-canvas__container edd-canvas__type-%1$s" id="%2$s-container"><canvas id="%2$s"></canvas></div>',
			esc_attr( $this->get_type() ),
			esc_attr( $this->get_target_el() )
		);

		// Enqueue script and configuration to render the chart.
		wp_enqueue_script( 'edd-admin-reports' );

		wp_add_inline_script(
			'edd-admin-reports',
			sprintf( 'window.edd.renderChart(%s)', wp_json_encode( $this->build_config() ) )
		);
	}

	/**
	 * Builds the chart config.
	 *
	 * @since 3.0
	 *
	 * @return object Config object.
	 */
	public function build_config() {
		$config = new \stdClass();

		// Dates.
		$dates        = Reports\get_dates_filter( 'objects' );
		$day_by_day   = Reports\get_dates_filter_day_by_day();
		$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

		// Adjust end date forward by 1 second to push into the next day (for ChartJS display purposes).
		$dates['end']->addSeconds( 1 );

		// Get the timezone ID for parsing.
		$timezone = edd_get_timezone_id();

		// Apply UTC offset.
		$dates['start']->setTimezone( $timezone );
		$dates['end']->setTimezone( $timezone );

		$time_format = 'MMM YYYY';

		if ( $hour_by_hour ) {
			$time_format = 'hA';
		} else if ( $day_by_day ) {
			$time_format = 'MMM D';
		}

		$config->type         = $this->get_type();
		$config->data         = $this->get_chart_data();
		$config->options      = $this->get_chart_options();
		$config->target       = $this->get_target_el();
		$config->dates        = array_merge(
			$dates,
			array(
				'hour_by_hour' => $hour_by_hour,
				'day_by_day'   => $day_by_day,
				'utc_offset'   => esc_js( EDD()->utils->get_gmt_offset() / HOUR_IN_SECONDS ),
				'timezone'     => $timezone,
				'time_format'  => $time_format,
			)
		);

		return $config;
	}

	/**
	 * Retrieves the parsed chart datasets as an object.
	 *
	 * @since 3.0
	 *
	 * @return array Parsed chart data.
	 */
	public function get_chart_data() {
		$data = array();

		if ( $this->has_datasets() ) {
			$datasets = $this->get_datasets();

			$data['datasets'] = array();

			foreach ( $datasets as $id => $set ) {
				if ( $set->has_errors() ) {
					continue;
				}

				$data['datasets'][] = $set->get_options();
			}
		}

		if ( $this->is_pie_manifest() ) {
			$data['labels'] = $this->get_labels();
		}

		return $data;
	}

	/**
	 * Retrieves the parsed chart options as an object.
	 *
	 * @since 3.0
	 *
	 * @return array Parsed chart options.
	 */
	public function get_chart_options() {
		$endpoint_options = $this->get_endpoint()->get_options();

		if ( $this->is_pie_manifest() ) {
			$defaults = array(
				'animation' => array(
					'duration'   => 0,
				),
				'responsive' => true,
				'legend'     => array(
					'position' => 'left',
				),
			);
		} else {
			$day_by_day   = Reports\get_dates_filter_day_by_day();
			$hour_by_hour = Reports\get_dates_filter_hour_by_hour();

			$time_unit = 'month';
			$time_format = 'MMM YYYY';

			if ( $hour_by_hour ) {
				$time_unit   = 'hour';
				$time_format = 'hA';
			} else if ( $day_by_day ) {
				$time_unit   = 'day';
				$time_format = 'MMM D';
			}

			$defaults = array(
				'animation' => array(
					'duration'   => 0,
				),
				'responsive' => true,
				'hoverMode'  => 'index',
				'stacked'    => false,
				'title'      => array(
					'display' => $this->get_endpoint()->get_label() && $this->get_endpoint()->get( 'show_chart_title' ),
					'text'    => $this->get_endpoint()->get_label(),
				),
				'scales'    => array(
					'xAxes' => array(),
					'yAxes' => array(),
				),
			);

			$default_xAxes = array(
				array(
					'type'     => 'time',
					'display'  => true,
					'ticks'    => array(
						'source'        => 'auto',
						'maxRotation'   => 0,
					),
					'position' => 'bottom',
					'time'     => array(
						'unit'          => $time_unit,
						'tooltipFormat' => $time_format,
					),
				),
			);

			$default_yAxes = array(
				array(
					'type'     => 'linear',
					'display'  => true,
					'position' => 'left',
					'ticks'    => array(
						'formattingType' => 'format',
						'beginAtZero'    => true,
						'suggestedMin'   => 0,
					),
				),
			);

			// Check if specific axes are missing from the endpoint options and load them from defaults.
			foreach ( array( 'xAxes', 'yAxes' ) as $axes_name) {
				if ( empty( $endpoint_options['scales'][ $axes_name ] ) ) {
					$endpoint_options['scales'][ $axes_name ] = ${ "default_{$axes_name}" };
				}
			}

		}

		return array_merge( $defaults, $endpoint_options );
	}

	/**
	 * Determines whether the chart manifest is for a pie or doughnut chart.
	 *
	 * @since 3.0
	 *
	 * @return bool True if the manifest is for a pie or doughnut chart, otherwise false.
	 */
	public function is_pie_manifest() {
		return in_array( $this->get_type(), array( 'pie', 'doughnut' ), true );
	}

	/**
	 * Determines whether the dataset has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		if ( method_exists( $this->errors, 'has_errors' ) ) {
			return $this->errors->has_errors();
		} else {
			$errors = $this->errors->get_error_codes();

			return ! empty( $errors );
		}
	}

	/**
	 * Retrieves any logged errors for the dataset.
	 *
	 * @since 3.0
	 *
	 * @return \WP_Error WP_Error object for the current dataset.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets up the WP_Error instance.
	 *
	 * @since 3.0
	 */
	public function setup_error_logger() {
		if ( ! isset( $this->errors ) ) {
			$this->errors = new \WP_Error();
		}
	}

}
